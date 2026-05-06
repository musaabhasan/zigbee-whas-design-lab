<?php

declare(strict_types=1);

use ZigbeeWhasLab\Repository\LabRepository;
use ZigbeeWhasLab\Service\DesignService;

$bootstrap = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'bootstrap.php';

$repository = new LabRepository($bootstrap['catalog']);
$service = new DesignService($repository);
$assertions = 0;

function assertThat(bool $condition, string $message): void
{
    global $assertions;
    $assertions++;

    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$paper = $repository->paper();
assertThat($paper['doi'] === '10.5121/ijcnc.2014.6411', 'Paper DOI must match the formal DOI.');
assertThat(str_contains($paper['citation'], 'International Journal of Computer Networks'), 'Citation must include the journal.');

$technologies = $repository->technologies();
$applications = $repository->applications();
$challenges = $repository->challenges();
$controls = $repository->controls();
assertThat(count($technologies) === 6, 'Technology catalog should contain six technologies.');
assertThat(count($applications) === 4, 'Application catalog should contain four classes.');
assertThat(count($challenges) === 8, 'Challenge catalog should contain eight challenges.');
assertThat(count($controls) === 24, 'Control catalog should contain 24 controls.');

$maximum = array_sum(array_map(static fn (array $control): int => (int) $control['weight'], $controls));
assertThat($maximum === 173, 'Maximum control weight should be 173.');

$allControlIds = array_map(static fn (array $control): string => (string) $control['id'], $controls);
$strong = $service->assess([
    'project_name' => 'Strong WHAS design',
    'primary_application' => 'energy',
    'home_size' => 'medium',
    'node_count' => 48,
    'topology' => 'mesh',
    'battery_priority' => 'high',
    'interference_density' => 'low',
    'remote_monitoring' => true,
    'internet_gateway' => true,
    'security_critical' => true,
    'retrofit_environment' => false,
    'controls' => $allControlIds,
]);

assertThat($strong['score'] >= 95, 'All controls should produce a high readiness score.');
assertThat(in_array($strong['readiness'], ['Deployment Ready', 'Controlled Pilot'], true), 'All controls should produce strong readiness.');
assertThat($strong['risk_tier'] === 'Low', 'All controls should produce low residual risk.');
assertThat($strong['technology_scores'][0]['id'] === 'zigbee', 'Low-power WHAS scenario should favor ZigBee.');

$weak = $service->assess([
    'project_name' => 'Weak WHAS design',
    'primary_application' => 'security',
    'home_size' => 'villa',
    'node_count' => 140,
    'topology' => 'star',
    'battery_priority' => 'high',
    'interference_density' => 'high',
    'remote_monitoring' => true,
    'internet_gateway' => true,
    'security_critical' => true,
    'retrofit_environment' => true,
    'controls' => [],
]);

assertThat($weak['score'] === 0, 'No controls should produce zero readiness score after context penalty.');
assertThat($weak['readiness'] === 'Not Ready', 'Weak high-risk design should not be ready.');
assertThat(in_array($weak['risk_tier'], ['High', 'Critical'], true), 'Weak high-risk design should have high or critical residual risk.');
assertThat($weak['recommended_architecture']['recommended_topology'] === 'mesh', 'Large or high-node design should recommend mesh topology.');
assertThat($weak['recommended_architecture']['router_count'] >= 5, 'Large design should recommend multiple routers.');

$partial = $service->assess([
    'project_name' => 'Partial WHAS design',
    'primary_application' => 'assistive',
    'home_size' => 'large',
    'node_count' => 80,
    'topology' => 'tree',
    'battery_priority' => 'high',
    'interference_density' => 'medium',
    'remote_monitoring' => true,
    'internet_gateway' => true,
    'security_critical' => true,
    'retrofit_environment' => true,
    'controls' => ['topology-planning', 'router-density', 'channel-planning', 'gateway-hardening', 'remote-access-control', 'secure-commissioning'],
]);
assertThat($partial['score'] > 20, 'Partial controls should improve readiness above zero.');
assertThat(count($partial['recommendations']) > 0, 'Partial design should produce recommendations.');
assertThat($partial['recommended_architecture']['coordinator_count'] === 1, 'Architecture should use one coordinator.');

$summary = $service->summary();
assertThat($summary['metrics']['controls'] === 24, 'Summary should report control count.');
assertThat($summary['metrics']['technologies'] === 6, 'Summary should report technology count.');
assertThat($summary['metrics']['maximum_score'] === 173, 'Summary should report maximum score.');

$migration = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR . '001_create_core_tables.sql');
$seed = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'seeders' . DIRECTORY_SEPARATOR . '001_seed_research_data.sql');
assertThat(is_string($migration) && str_contains($migration, 'CREATE TABLE IF NOT EXISTS design_assessments'), 'Migration must create design assessments table.');
assertThat(is_string($migration) && str_contains($migration, 'CREATE TABLE IF NOT EXISTS technology_catalog'), 'Migration must create technology catalog table.');
assertThat(is_string($seed) && str_contains($seed, 'resource-constraint'), 'Seed must include resource constraint challenge.');
assertThat(is_string($seed) && str_contains($seed, 'secure-commissioning'), 'Seed must include secure commissioning control.');

echo 'Tests passed: ' . $assertions . ' assertions.' . PHP_EOL;

