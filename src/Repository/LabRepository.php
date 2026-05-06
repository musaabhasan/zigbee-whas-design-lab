<?php

declare(strict_types=1);

namespace ZigbeeWhasLab\Repository;

use PDO;
use Throwable;
use ZigbeeWhasLab\Support\Uuid;

final class LabRepository
{
    /**
     * @param array<string, mixed> $catalog
     */
    public function __construct(
        private readonly array $catalog,
        private readonly ?PDO $pdo = null
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function paper(): array
    {
        return $this->catalog['paper'];
    }

    /**
     * @return array<string, mixed>
     */
    public function zigbeeFeatures(): array
    {
        return $this->catalog['zigbee_features'];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function technologies(): array
    {
        return $this->catalog['technologies'];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function applications(): array
    {
        return $this->catalog['applications'];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function challenges(): array
    {
        return $this->catalog['challenges'];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function controls(): array
    {
        return $this->catalog['controls'];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function controlsById(): array
    {
        $indexed = [];
        foreach ($this->controls() as $control) {
            $indexed[(string) $control['id']] = $control;
        }

        return $indexed;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function challengesById(): array
    {
        $indexed = [];
        foreach ($this->challenges() as $challenge) {
            $indexed[(string) $challenge['id']] = $challenge;
        }

        return $indexed;
    }

    /**
     * @param array<string, mixed> $assessment
     */
    public function saveAssessment(array $assessment): ?string
    {
        if ($this->pdo === null) {
            return null;
        }

        $id = Uuid::v4();

        try {
            $statement = $this->pdo->prepare(
                'INSERT INTO design_assessments (
                    id,
                    project_name,
                    primary_application,
                    home_size,
                    node_count,
                    topology,
                    score,
                    readiness,
                    risk_tier,
                    selected_controls,
                    result_payload,
                    created_at
                ) VALUES (
                    :id,
                    :project_name,
                    :primary_application,
                    :home_size,
                    :node_count,
                    :topology,
                    :score,
                    :readiness,
                    :risk_tier,
                    :selected_controls,
                    :result_payload,
                    NOW()
                )'
            );
            $context = $assessment['context'];
            $statement->execute([
                'id' => $id,
                'project_name' => (string) ($context['project_name'] ?? 'WHAS design'),
                'primary_application' => (string) ($context['primary_application'] ?? 'mixed'),
                'home_size' => (string) ($context['home_size'] ?? 'medium'),
                'node_count' => (int) ($context['node_count'] ?? 0),
                'topology' => (string) ($context['topology'] ?? 'mesh'),
                'score' => (int) $assessment['score'],
                'readiness' => (string) $assessment['readiness'],
                'risk_tier' => (string) $assessment['risk_tier'],
                'selected_controls' => json_encode($assessment['selected_controls'], JSON_THROW_ON_ERROR),
                'result_payload' => json_encode($assessment, JSON_THROW_ON_ERROR),
            ]);

            $this->audit('design_assessment.created', ['assessment_id' => $id, 'score' => $assessment['score']]);

            return $id;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function recentAssessments(int $limit = 5): array
    {
        if ($this->pdo === null) {
            return [];
        }

        try {
            $statement = $this->pdo->prepare(
                'SELECT id, project_name, primary_application, score, readiness, risk_tier, created_at
                 FROM design_assessments
                 ORDER BY created_at DESC
                 LIMIT :limit'
            );
            $statement->bindValue('limit', max(1, min(25, $limit)), PDO::PARAM_INT);
            $statement->execute();

            return $statement->fetchAll();
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function audit(string $event, array $payload): void
    {
        if ($this->pdo === null) {
            return;
        }

        try {
            $statement = $this->pdo->prepare(
                'INSERT INTO audit_events (id, event_name, payload, created_at)
                 VALUES (:id, :event_name, :payload, NOW())'
            );
            $statement->execute([
                'id' => Uuid::v4(),
                'event_name' => $event,
                'payload' => json_encode($payload, JSON_THROW_ON_ERROR),
            ]);
        } catch (Throwable) {
        }
    }
}

