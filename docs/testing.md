# Testing

The repository includes lightweight local tests that do not require third-party PHP packages.

## Lint

```bash
php bin/lint.php
```

## Functional Tests

```bash
php bin/test.php
```

The tests verify:

- Formal paper DOI and citation.
- Catalog counts for technologies, applications, challenges, and controls.
- Maximum readiness weight.
- High-control design produces deployment-ready or controlled-pilot readiness.
- Weak high-risk design produces a high or critical residual tier.
- Technology scoring favors ZigBee for low-power WHAS scenarios.
- Architecture recommendation produces coordinator, router, gateway, and end-device guidance.
- Migration and seed files include the required tables and key records.

## HTTP Smoke Test

```bash
php -S 127.0.0.1:8086 -t public
```

Then verify:

- `http://127.0.0.1:8086/health`
- `http://127.0.0.1:8086/`
- `http://127.0.0.1:8086/planner`
- `http://127.0.0.1:8086/technologies`
- `http://127.0.0.1:8086/paper`
- `http://127.0.0.1:8086/api/summary`

