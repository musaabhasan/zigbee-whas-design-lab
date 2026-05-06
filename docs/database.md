# Database

The MySQL schema supports seeded research data and persisted design assessments.

## Tables

| Table | Purpose |
| --- | --- |
| `paper_references` | Formal citation metadata. |
| `technology_catalog` | Technology comparison values. |
| `application_catalog` | WHAS application classes and design focus. |
| `challenge_catalog` | Paper-derived design challenges. |
| `control_catalog` | Readiness controls and weights. |
| `challenge_control_map` | Many-to-many challenge-control mapping. |
| `design_assessments` | Assessment context, score, readiness, selected controls, and JSON result. |
| `audit_events` | Audit trail for assessment creation and future administrative actions. |

## Migration Order

1. `database/migrations/001_create_core_tables.sql`
2. `database/seeders/001_seed_research_data.sql`

Docker Compose mounts the scripts in this order for initial database creation.

