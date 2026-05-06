<?php

declare(strict_types=1);

use ZigbeeWhasLab\Repository\LabRepository;
use ZigbeeWhasLab\Security\Csrf;
use ZigbeeWhasLab\Security\SecurityHeaders;
use ZigbeeWhasLab\Service\DesignService;
use ZigbeeWhasLab\Support\Database;
use ZigbeeWhasLab\Support\Json;
use ZigbeeWhasLab\Support\View;

$bootstrap = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'bootstrap.php';
$repository = new LabRepository($bootstrap['catalog'], Database::connection());
$service = new DesignService($repository);
SecurityHeaders::apply();

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if ($path === '/health') {
    Json::respond([
        'status' => 'ok',
        'service' => 'zigbee-whas-design-lab',
        'paper_doi' => $repository->paper()['doi'],
    ]);
}

if ($path === '/api/summary') {
    Json::respond($service->summary());
}

if ($path === '/api/assess') {
    if ($method !== 'POST') {
        Json::respond(['error' => 'POST required'], 405);
    }

    $payload = json_decode(file_get_contents('php://input') ?: '{}', true);
    if (!is_array($payload) || $payload === []) {
        $payload = $_POST;
    }

    Json::respond($service->assess($payload));
}

if ($path === '/') {
    echo View::page('ZigBee WHAS Design Lab', renderDashboard($repository, $service));
    exit;
}

if ($path === '/planner') {
    echo View::page('Planner | ZigBee WHAS Design Lab', renderPlanner($repository, $service, $method));
    exit;
}

if ($path === '/technologies') {
    echo View::page('Technologies | ZigBee WHAS Design Lab', renderTechnologies($repository));
    exit;
}

if ($path === '/paper') {
    echo View::page('Paper | ZigBee WHAS Design Lab', renderPaper($repository));
    exit;
}

http_response_code(404);
echo View::page('Not Found | ZigBee WHAS Design Lab', '<section class="panel hero"><h1>Page not found</h1><p>The requested page is not available.</p></section>');

function renderDashboard(LabRepository $repository, DesignService $service): string
{
    $summary = $service->summary();
    $paper = $repository->paper();
    $metrics = $summary['metrics'];
    $features = $repository->zigbeeFeatures();
    $applications = $repository->applications();
    $challenges = array_slice($repository->challenges(), 0, 4);
    $recent = $summary['recent_assessments'];

    $applicationHtml = '';
    foreach ($applications as $application) {
        $applicationHtml .= '<article class="card">'
            . '<span>' . View::e($application['id']) . '</span>'
            . '<h3>' . View::e($application['name']) . '</h3>'
            . '<p>' . View::e($application['design_focus']) . '</p>'
            . '</article>';
    }

    $challengeHtml = '';
    foreach ($challenges as $challenge) {
        $challengeHtml .= '<article class="card">'
            . '<span>' . View::e($challenge['severity']) . ' priority</span>'
            . '<h3>' . View::e($challenge['name']) . '</h3>'
            . '<p>' . View::e($challenge['mitigation']) . '</p>'
            . '</article>';
    }

    $featureHtml = '';
    foreach ($features['device_types'] as $type => $description) {
        $featureHtml .= '<li><strong>' . View::e($type) . '</strong><span>' . View::e($description) . '</span></li>';
    }

    $recentHtml = '<p class="muted">Assessments are stored when a database connection is configured.</p>';
    if ($recent !== []) {
        $recentHtml = '';
        foreach ($recent as $item) {
            $recentHtml .= '<div><strong>' . View::e($item['project_name']) . '</strong><span>' . View::e($item['score']) . '/100 - ' . View::e($item['risk_tier']) . '</span></div>';
        }
    }

    return <<<HTML
<section class="panel hero">
  <div>
    <p class="eyebrow">Research-Based Wireless Home Automation Design</p>
    <h1>Plan, compare, and validate ZigBee wireless home automation systems.</h1>
    <p class="lead">A PHP/MySQL design lab based on the IJCNC survey by Thoraya Obaid, Haleemah Rashed, Ali Abou-Elnour, Muhammad Rehan, Mussab Muhammad Saleh, and Mohammed Tarique.</p>
    <div class="hero-actions">
      <a class="button-link" href="/planner">Run Design Planner</a>
      <a class="secondary-link" href="/paper">View Paper Alignment</a>
    </div>
  </div>
  <aside class="paper-card">
    <span>Paper Reference</span>
    <strong>{$paper['title']}</strong>
    <p>{$paper['journal_short']} {$paper['volume']}({$paper['issue']}) - pp. {$paper['pages']}</p>
    <a href="{$paper['doi_url']}" target="_blank" rel="noreferrer">{$paper['doi']}</a>
  </aside>
</section>

<section class="metric-grid">
  <article><span>Technologies</span><strong>{$metrics['technologies']}</strong><p>ZigBee compared with Z-Wave, Insteon, Wavenis, Bluetooth, and WiFi.</p></article>
  <article><span>Application classes</span><strong>{$metrics['applications']}</strong><p>Security, surveillance, energy, and assistive home design patterns.</p></article>
  <article><span>Design challenges</span><strong>{$metrics['challenges']}</strong><p>Coverage, resource, security, gateway, and coexistence risks.</p></article>
  <article><span>Controls</span><strong>{$metrics['controls']}</strong><p>Implementation controls mapped to paper-derived challenges.</p></article>
</section>

<section class="split-layout">
  <div>
    <div class="section-head"><h2>WHAS Application Blueprints</h2><a href="/planner">Assess readiness</a></div>
    <div class="card-grid">{$applicationHtml}</div>
  </div>
  <aside class="panel side-panel">
    <h2>ZigBee Device Roles</h2>
    <ul class="role-list">{$featureHtml}</ul>
  </aside>
</section>

<section class="section-head"><h2>Priority Design Challenges</h2><a href="/technologies">Compare technologies</a></section>
<div class="card-grid">{$challengeHtml}</div>

<section class="panel recent-panel">
  <h2>Recent Design Assessments</h2>
  <div class="recent-list">{$recentHtml}</div>
</section>
HTML;
}

function renderPlanner(LabRepository $repository, DesignService $service, string $method): string
{
    $result = null;
    $notice = '';
    if ($method === 'POST') {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            $notice = '<div class="notice">The planner could not be submitted because the session token expired.</div>';
        } else {
            $result = $service->assess($_POST);
        }
    }

    $controlsByCategory = [];
    foreach ($repository->controls() as $control) {
        $controlsByCategory[(string) $control['category']][] = $control;
    }

    $controlsHtml = '';
    foreach ($controlsByCategory as $category => $controls) {
        $controlsHtml .= '<fieldset class="control-set"><legend>' . View::e($category) . '</legend><div class="control-grid">';
        foreach ($controls as $control) {
            $id = View::e($control['id']);
            $checked = $result && in_array($control['id'], $result['selected_controls'], true) ? ' checked' : '';
            $controlsHtml .= '<label class="control-item"><input type="checkbox" name="controls[]" value="' . $id . '"' . $checked . '><span><strong>' . View::e($control['name']) . '</strong><small>Weight ' . View::e($control['weight']) . '</small></span></label>';
        }
        $controlsHtml .= '</div></fieldset>';
    }

    $resultHtml = '';
    if ($result !== null) {
        $topTech = $result['technology_scores'][0];
        $architecture = $result['recommended_architecture'];
        $gatewayLabel = $architecture['gateway_required'] ? 'Required' : 'Optional';
        $recommendationHtml = '';
        foreach ($result['recommendations'] as $recommendation) {
            $recommendationHtml .= '<li><strong>' . View::e($recommendation['name']) . '</strong><span>' . View::e($recommendation['category']) . '</span></li>';
        }

        $challengeHtml = '';
        foreach (array_slice($result['challenge_profile'], 0, 6) as $challenge) {
            $challengeHtml .= '<tr>'
                . '<td>' . View::e($challenge['name']) . '</td>'
                . '<td>' . View::e($challenge['severity']) . '</td>'
                . '<td>' . View::e($challenge['residual_score']) . '</td>'
                . '<td>' . View::e($challenge['residual_tier']) . '</td>'
                . '</tr>';
        }

        $resultHtml = <<<HTML
<section class="panel result-panel">
  <div class="result-score">
    <span>Readiness score</span>
    <strong>{$result['score']}</strong>
    <p>{$result['readiness']} - {$result['risk_tier']} residual risk</p>
  </div>
  <div class="architecture-card">
    <h2>Recommended Architecture</h2>
    <p><strong>{$architecture['preferred_technology']}</strong> using {$architecture['recommended_topology']} topology.</p>
    <div class="architecture-metrics">
      <span>Coordinator {$architecture['coordinator_count']}</span>
      <span>Routers {$architecture['router_count']}</span>
      <span>End devices {$architecture['end_device_count']}</span>
      <span>Gateway {$gatewayLabel}</span>
    </div>
    <p>{$architecture['design_note']}</p>
    <p class="muted">Top technology score: {$topTech['score']}/100.</p>
  </div>
  <div>
    <h2>Priority Actions</h2>
    <ol class="recommendation-list">{$recommendationHtml}</ol>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Challenge</th><th>Severity</th><th>Residual</th><th>Tier</th></tr></thead>
      <tbody>{$challengeHtml}</tbody>
    </table>
  </div>
</section>
HTML;
    }

    $csrf = Csrf::token();

    return <<<HTML
<section class="panel form-panel">
  <p class="eyebrow">Design Planner</p>
  <h1>Assess a ZigBee wireless home automation design.</h1>
  {$notice}
  <form method="post" action="/planner">
    <input type="hidden" name="_csrf" value="{$csrf}">
    <div class="form-grid">
      <label>Project name<input name="project_name" placeholder="Residential safety and energy pilot"></label>
      <label>Primary application
        <select name="primary_application">
          <option value="security">Security and safety</option>
          <option value="surveillance">Smart surveillance</option>
          <option value="energy">Energy management</option>
          <option value="assistive">Assistive home</option>
          <option value="mixed">Mixed use</option>
        </select>
      </label>
      <label>Home size
        <select name="home_size">
          <option value="small">Small</option>
          <option value="medium" selected>Medium</option>
          <option value="large">Large</option>
          <option value="villa">Villa or large residence</option>
        </select>
      </label>
      <label>Node count<input name="node_count" type="number" min="1" max="64000" value="32"></label>
      <label>Preferred topology
        <select name="topology">
          <option value="star">Star</option>
          <option value="tree">Tree</option>
          <option value="mesh" selected>Mesh</option>
          <option value="hybrid">Hybrid</option>
        </select>
      </label>
      <label>Interference density
        <select name="interference_density">
          <option value="low">Low</option>
          <option value="medium" selected>Medium</option>
          <option value="high">High</option>
        </select>
      </label>
      <label>Battery priority
        <select name="battery_priority">
          <option value="low">Low</option>
          <option value="medium">Medium</option>
          <option value="high" selected>High</option>
        </select>
      </label>
    </div>
    <div class="toggle-row">
      <label><input type="checkbox" name="remote_monitoring" value="1" checked> Remote monitoring required</label>
      <label><input type="checkbox" name="internet_gateway" value="1" checked> Internet gateway required</label>
      <label><input type="checkbox" name="security_critical" value="1" checked> Security or safety critical</label>
      <label><input type="checkbox" name="accessibility_required" value="1"> Accessibility features required</label>
      <label><input type="checkbox" name="retrofit_environment" value="1" checked> Existing home retrofit</label>
    </div>
    {$controlsHtml}
    <button type="submit">Calculate Design Readiness</button>
  </form>
</section>
{$resultHtml}
HTML;
}

function renderTechnologies(LabRepository $repository): string
{
    $rows = '';
    foreach ($repository->technologies() as $technology) {
        $strengths = implode(', ', $technology['strengths']);
        $tradeoffs = implode(', ', $technology['tradeoffs']);
        $rows .= '<tr>'
            . '<td><strong>' . View::e($technology['name']) . '</strong></td>'
            . '<td>' . View::e($technology['frequency']) . '</td>'
            . '<td>' . View::e($technology['range_meters']) . ' m</td>'
            . '<td>' . View::e($technology['network_size'] ?: 'Not specified') . '</td>'
            . '<td>' . View::e($technology['power_profile']) . '</td>'
            . '<td>' . View::e($strengths) . '</td>'
            . '<td>' . View::e($tradeoffs) . '</td>'
            . '</tr>';
    }

    return <<<HTML
<section class="panel paper-detail">
  <p class="eyebrow">Technology Comparison</p>
  <h1>WHAS technology comparison from the survey.</h1>
  <p class="lead">The paper compares ZigBee against Z-Wave, Insteon, Wavenis, Bluetooth, and WiFi across frequency, range, network scale, and power profile.</p>
</section>
<section class="panel table-panel">
  <div class="table-wrap">
    <table>
      <thead><tr><th>Technology</th><th>Frequency</th><th>Range</th><th>Network size</th><th>Power</th><th>Strengths</th><th>Tradeoffs</th></tr></thead>
      <tbody>{$rows}</tbody>
    </table>
  </div>
</section>
HTML;
}

function renderPaper(LabRepository $repository): string
{
    $paper = $repository->paper();
    $keywordHtml = '';
    foreach ($paper['keywords'] as $keyword) {
        $keywordHtml .= '<span class="pill">' . View::e($keyword) . '</span>';
    }

    $challengeHtml = '';
    foreach ($repository->challenges() as $challenge) {
        $challengeHtml .= '<article class="card">'
            . '<span>' . View::e($challenge['severity']) . ' priority</span>'
            . '<h3>' . View::e($challenge['name']) . '</h3>'
            . '<p>' . View::e($challenge['paper_signal']) . '</p>'
            . '</article>';
    }

    return <<<HTML
<section class="panel paper-detail">
  <p class="eyebrow">Research Alignment</p>
  <h1>{$paper['title']}</h1>
  <p class="lead">{$paper['summary']}</p>
  <div class="paper-citation">
    <span>Formal citation</span>
    <p>{$paper['citation']}</p>
  </div>
  <div class="hero-actions">
    <a class="button-link" href="{$paper['doi_url']}" target="_blank" rel="noreferrer">DOI</a>
    <a class="secondary-link" href="{$paper['pdf_url']}" target="_blank" rel="noreferrer">PDF</a>
    <a class="secondary-link" href="{$paper['index_url']}" target="_blank" rel="noreferrer">Index Record</a>
  </div>
</section>
<section class="section-head"><h2>Keywords</h2></section>
<div class="keyword-row">{$keywordHtml}</div>
<section class="section-head"><h2>Mapped Design Challenges</h2></section>
<div class="card-grid">{$challengeHtml}</div>
HTML;
}
