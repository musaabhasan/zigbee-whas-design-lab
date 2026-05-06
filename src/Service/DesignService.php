<?php

declare(strict_types=1);

namespace ZigbeeWhasLab\Service;

use ZigbeeWhasLab\Repository\LabRepository;

final class DesignService
{
    public function __construct(private readonly LabRepository $repository)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $controlCategories = [];
        foreach ($this->repository->controls() as $control) {
            $category = (string) $control['category'];
            $controlCategories[$category] = ($controlCategories[$category] ?? 0) + 1;
        }

        return [
            'paper' => $this->repository->paper(),
            'metrics' => [
                'technologies' => count($this->repository->technologies()),
                'applications' => count($this->repository->applications()),
                'challenges' => count($this->repository->challenges()),
                'controls' => count($this->repository->controls()),
                'maximum_score' => $this->maximumScore(),
            ],
            'zigbee_features' => $this->repository->zigbeeFeatures(),
            'control_categories' => $controlCategories,
            'recent_assessments' => $this->repository->recentAssessments(),
        ];
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public function assess(array $input): array
    {
        $context = $this->context($input);
        $selectedControls = $this->normalizeSelectedControls($input['controls'] ?? []);
        $controlsById = $this->repository->controlsById();
        $selected = array_values(array_intersect($selectedControls, array_keys($controlsById)));
        $earned = 0;

        foreach ($selected as $controlId) {
            $earned += (int) $controlsById[$controlId]['weight'];
        }

        $baseScore = (int) round(($earned / $this->maximumScore()) * 100);
        $challengeProfile = $this->challengeProfile($selected, $context, $baseScore);
        $riskTier = $this->riskTier($challengeProfile);
        $score = max(0, min(100, $baseScore - $this->contextPenalty($context)));
        $readiness = $this->readiness($score, $riskTier);
        $technologyScores = $this->technologyScores($context);
        $recommendations = $this->recommendations($selected, $challengeProfile);
        $architecture = $this->architecture($context, $technologyScores);

        $result = [
            'score' => $score,
            'base_score' => $baseScore,
            'available_weight' => $this->maximumScore(),
            'readiness' => $readiness,
            'risk_tier' => $riskTier,
            'selected_controls' => $selected,
            'context' => $context,
            'challenge_profile' => $challengeProfile,
            'technology_scores' => $technologyScores,
            'recommended_architecture' => $architecture,
            'recommendations' => $recommendations,
            'next_actions' => $this->nextActions($readiness, $recommendations),
        ];

        $assessmentId = $this->repository->saveAssessment($result);
        if ($assessmentId !== null) {
            $result['assessment_id'] = $assessmentId;
        }

        return $result;
    }

    private function maximumScore(): int
    {
        return array_sum(array_map(
            static fn (array $control): int => (int) $control['weight'],
            $this->repository->controls()
        ));
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    private function context(array $input): array
    {
        return [
            'project_name' => $this->cleanText($input['project_name'] ?? 'Wireless home automation design'),
            'primary_application' => $this->cleanChoice($input['primary_application'] ?? 'security', ['security', 'surveillance', 'energy', 'assistive', 'mixed'], 'security'),
            'home_size' => $this->cleanChoice($input['home_size'] ?? 'medium', ['small', 'medium', 'large', 'villa'], 'medium'),
            'node_count' => max(1, min(64000, (int) ($input['node_count'] ?? 24))),
            'topology' => $this->cleanChoice($input['topology'] ?? 'mesh', ['star', 'tree', 'mesh', 'hybrid'], 'mesh'),
            'battery_priority' => $this->cleanChoice($input['battery_priority'] ?? 'high', ['low', 'medium', 'high'], 'high'),
            'interference_density' => $this->cleanChoice($input['interference_density'] ?? 'medium', ['low', 'medium', 'high'], 'medium'),
            'remote_monitoring' => $this->bool($input['remote_monitoring'] ?? true),
            'internet_gateway' => $this->bool($input['internet_gateway'] ?? true),
            'accessibility_required' => $this->bool($input['accessibility_required'] ?? false),
            'security_critical' => $this->bool($input['security_critical'] ?? true),
            'retrofit_environment' => $this->bool($input['retrofit_environment'] ?? true),
        ];
    }

    private function cleanText(mixed $value): string
    {
        $value = trim((string) $value);
        $value = preg_replace('/\s+/', ' ', $value) ?? '';

        return substr($value, 0, 140);
    }

    /**
     * @param array<int, string> $allowed
     */
    private function cleanChoice(mixed $value, array $allowed, string $default): string
    {
        $value = strtolower(trim((string) $value));
        return in_array($value, $allowed, true) ? $value : $default;
    }

    private function bool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }

    /**
     * @param mixed $controls
     * @return array<int, string>
     */
    private function normalizeSelectedControls(mixed $controls): array
    {
        if (is_string($controls)) {
            $controls = array_filter(array_map('trim', explode(',', $controls)));
        }

        if (!is_array($controls)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(
            static fn (mixed $control): string => preg_replace('/[^a-z0-9-]/', '', strtolower((string) $control)) ?? '',
            $controls
        ))));
    }

    /**
     * @param array<int, string> $selected
     * @param array<string, mixed> $context
     * @return array<int, array<string, mixed>>
     */
    private function challengeProfile(array $selected, array $context, int $baseScore): array
    {
        $severityBase = ['High' => 82, 'Medium' => 58, 'Low' => 35];
        $items = [];

        foreach ($this->repository->challenges() as $challenge) {
            $required = array_map('strval', $challenge['controls']);
            $covered = count(array_intersect($required, $selected));
            $residual = ($severityBase[(string) $challenge['severity']] ?? 58) - ($covered * 19) - min(15, (int) round($baseScore * 0.15));

            $id = (string) $challenge['id'];
            if ($id === 'limited-range' && in_array($context['home_size'], ['large', 'villa'], true)) {
                $residual += 18;
            }
            if ($id === 'ism-interference' && $context['interference_density'] === 'high') {
                $residual += 20;
            }
            if ($id === 'internet-connectivity' && $context['remote_monitoring'] === true) {
                $residual += 12;
            }
            if ($id === 'security-exposure' && $context['security_critical'] === true) {
                $residual += 14;
            }
            if ($id === 'retrofit-cost' && $context['retrofit_environment'] === true) {
                $residual += 10;
            }
            if ($id === 'resource-constraint' && $context['battery_priority'] === 'high') {
                $residual += 10;
            }
            if ($id === 'orphan-nodes' && $context['node_count'] > 80) {
                $residual += 14;
            }

            $residual = max(5, min(100, $residual));
            $items[] = [
                'id' => $id,
                'name' => (string) $challenge['name'],
                'severity' => (string) $challenge['severity'],
                'residual_score' => $residual,
                'residual_tier' => $this->tierFromScore($residual),
                'covered_controls' => $covered,
                'required_controls' => count($required),
                'paper_signal' => (string) $challenge['paper_signal'],
                'mitigation' => (string) $challenge['mitigation'],
                'controls' => $required,
            ];
        }

        usort($items, static fn (array $left, array $right): int => $right['residual_score'] <=> $left['residual_score']);

        return $items;
    }

    /**
     * @param array<int, array<string, mixed>> $profile
     */
    private function riskTier(array $profile): string
    {
        return $this->tierFromScore((int) max(array_column($profile, 'residual_score')));
    }

    private function tierFromScore(int $score): string
    {
        return match (true) {
            $score >= 85 => 'Critical',
            $score >= 70 => 'High',
            $score >= 45 => 'Medium',
            default => 'Low',
        };
    }

    /**
     * @param array<string, mixed> $context
     */
    private function contextPenalty(array $context): int
    {
        $penalty = 0;
        $penalty += $context['interference_density'] === 'high' ? 4 : 0;
        $penalty += in_array($context['home_size'], ['large', 'villa'], true) ? 4 : 0;
        $penalty += $context['node_count'] > 80 ? 4 : 0;
        $penalty += $context['security_critical'] ? 3 : 0;
        $penalty += $context['retrofit_environment'] ? 2 : 0;

        return $penalty;
    }

    private function readiness(int $score, string $riskTier): string
    {
        if ($score >= 88 && $riskTier === 'Low') {
            return 'Deployment Ready';
        }
        if ($score >= 70 && in_array($riskTier, ['Low', 'Medium'], true)) {
            return 'Controlled Pilot';
        }
        if ($score >= 45) {
            return 'Design Review Required';
        }

        return 'Not Ready';
    }

    /**
     * @param array<string, mixed> $context
     * @return array<int, array<string, mixed>>
     */
    private function technologyScores(array $context): array
    {
        $scores = [];
        foreach ($this->repository->technologies() as $technology) {
            $score = 50 + (int) $technology['score_bias'];
            if ($context['battery_priority'] === 'high' && str_contains(strtolower((string) $technology['power_profile']), 'low')) {
                $score += 12;
            }
            if ($context['home_size'] === 'villa' && (int) $technology['range_meters'] >= 100) {
                $score += 8;
            }
            if ($context['node_count'] > 200 && (int) $technology['network_size'] >= $context['node_count']) {
                $score += 10;
            }
            if ($context['remote_monitoring'] && $technology['id'] === 'wifi') {
                $score += 8;
            }
            if ($context['interference_density'] === 'high' && in_array($technology['id'], ['wifi', 'bluetooth', 'zigbee'], true)) {
                $score -= $technology['id'] === 'zigbee' ? 6 : 12;
            }
            if ($context['primary_application'] === 'energy' && $technology['id'] === 'zigbee') {
                $score += 8;
            }
            if ($context['primary_application'] === 'assistive' && in_array($technology['id'], ['zigbee', 'wifi'], true)) {
                $score += 5;
            }

            $scores[] = [
                'id' => (string) $technology['id'],
                'name' => (string) $technology['name'],
                'score' => max(0, min(100, $score)),
                'power_profile' => (string) $technology['power_profile'],
                'range_meters' => (int) $technology['range_meters'],
                'network_size' => (int) $technology['network_size'],
                'strengths' => $technology['strengths'],
                'tradeoffs' => $technology['tradeoffs'],
            ];
        }

        usort($scores, static fn (array $left, array $right): int => $right['score'] <=> $left['score']);

        return $scores;
    }

    /**
     * @param array<string, mixed> $context
     * @param array<int, array<string, mixed>> $technologyScores
     * @return array<string, mixed>
     */
    private function architecture(array $context, array $technologyScores): array
    {
        $routerCount = match ($context['home_size']) {
            'small' => max(1, (int) ceil($context['node_count'] / 20)),
            'medium' => max(2, (int) ceil($context['node_count'] / 18)),
            'large' => max(4, (int) ceil($context['node_count'] / 15)),
            default => max(5, (int) ceil($context['node_count'] / 12)),
        };

        $topology = $context['topology'];
        if ($context['home_size'] === 'villa' || $context['node_count'] > 60) {
            $topology = 'mesh';
        }

        return [
            'preferred_technology' => $technologyScores[0]['name'],
            'recommended_topology' => $topology,
            'coordinator_count' => 1,
            'router_count' => $routerCount,
            'end_device_count' => max(1, $context['node_count'] - $routerCount - 1),
            'gateway_required' => $context['remote_monitoring'] || $context['internet_gateway'],
            'application_focus' => $context['primary_application'],
            'design_note' => 'Use low-power end devices for sensing, FFD routers for coverage, and a hardened gateway when remote monitoring is required.',
        ];
    }

    /**
     * @param array<int, string> $selected
     * @param array<int, array<string, mixed>> $challengeProfile
     * @return array<int, array<string, mixed>>
     */
    private function recommendations(array $selected, array $challengeProfile): array
    {
        $controlsById = $this->repository->controlsById();
        $ranked = [];

        foreach (array_slice($challengeProfile, 0, 5) as $challenge) {
            foreach ($challenge['controls'] as $controlId) {
                if (!in_array($controlId, $selected, true) && isset($controlsById[$controlId])) {
                    $ranked[$controlId] = ($ranked[$controlId] ?? 0) + (int) $challenge['residual_score'];
                }
            }
        }

        foreach ($controlsById as $controlId => $control) {
            if (!in_array($controlId, $selected, true)) {
                $ranked[$controlId] = ($ranked[$controlId] ?? 0) + (int) $control['weight'];
            }
        }

        arsort($ranked);
        $recommendations = [];
        foreach (array_slice(array_keys($ranked), 0, 8) as $controlId) {
            $control = $controlsById[$controlId];
            $recommendations[] = [
                'id' => $controlId,
                'name' => (string) $control['name'],
                'category' => (string) $control['category'],
                'weight' => (int) $control['weight'],
            ];
        }

        return $recommendations;
    }

    /**
     * @param array<int, array<string, mixed>> $recommendations
     * @return array<int, string>
     */
    private function nextActions(string $readiness, array $recommendations): array
    {
        if ($readiness === 'Deployment Ready') {
            return [
                'Run a final channel and gateway placement validation before handover.',
                'Capture commissioning, key-management, and router placement evidence.',
                'Schedule periodic firmware, battery, and join-failure reviews.',
            ];
        }

        $actions = [
            'Resolve the highest residual design challenges before installation.',
            'Validate topology, gateway, channel, and security controls in a pilot area.',
        ];

        foreach (array_slice($recommendations, 0, 3) as $recommendation) {
            $actions[] = 'Implement: ' . $recommendation['name'] . '.';
        }

        return $actions;
    }
}

