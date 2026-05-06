# ZigBee WHAS Design Lab

A PHP 8.x and MySQL 8.0 research portal for designing and evaluating ZigBee-based wireless home automation systems.

The project is based on **"ZigBee Technology and Its Application in Wireless Home Automation Systems: A Survey"** by Thoraya Obaid, Haleemah Rashed, Ali Abou-Elnour, Muhammad Rehan, Mussab Muhammad Saleh, and Mohammed Tarique. It translates the paper's ZigBee technology overview, WHAS application survey, technology comparison, and deployment challenges into a practical design-readiness platform.

## Paper Reference

Obaid, T., Rashed, H., Abou-Elnour, A., Rehan, M., Muhammad Saleh, M., & Tarique, M. (2014). **ZigBee Technology and Its Application in Wireless Home Automation Systems: A Survey**. *International Journal of Computer Networks & Communications*, 6(4), 115-131. https://doi.org/10.5121/ijcnc.2014.6411

Official records:

- AIRCC index: https://airccj.org/csecfp/library/Search.php?title=igBee+Technology+and+its+application+in+Wireless+Home+Automation+systems%3A+a+survey&x=43&y=13
- Article PDF: https://airccse.org/journal/cnc/6414cnc11.pdf

## What This Repository Provides

- ZigBee wireless home automation design-readiness assessment.
- Technology comparison covering ZigBee, Z-Wave, Insteon, Wavenis, Bluetooth, and WiFi.
- Application blueprints for security and safety, smart surveillance, energy management, and assistive homes.
- Challenge model covering resource-constrained nodes, range, IEEE 802.15.4 dependency, ISM-band interference, gateway connectivity, orphan nodes, wireless security exposure, and retrofit cost.
- Control catalog with 24 implementation controls mapped to paper-derived challenges.
- Architecture recommendation with coordinator, router, end-device, topology, gateway, and technology guidance.
- MySQL schema and seed data for paper references, technologies, applications, challenges, controls, mappings, assessments, and audit events.
- JSON APIs for integration with dashboards, labs, and research extensions.
- Security-conscious PHP implementation with CSRF validation, security headers, input normalization, PDO prepared statements, safe session cookies, and JSON persistence.
- Linting, functional tests, HTTP smoke-test compatibility, and database migration validation.

## Quick Start

```bash
cp .env.example .env
docker compose up --build
```

Then open:

- Application: `http://localhost:8086`
- Planner: `http://localhost:8086/planner`
- Technology comparison: `http://localhost:8086/technologies`
- Paper alignment: `http://localhost:8086/paper`
- Health endpoint: `http://localhost:8086/health`
- JSON summary: `http://localhost:8086/api/summary`

## Local Checks

```bash
php bin/lint.php
php bin/test.php
```

## JSON Assessment API

```bash
curl -X POST http://localhost:8086/api/assess \
  -H "Content-Type: application/json" \
  -d '{
    "project_name": "Residential safety and energy pilot",
    "primary_application": "security",
    "home_size": "villa",
    "node_count": 72,
    "topology": "mesh",
    "battery_priority": "high",
    "interference_density": "high",
    "remote_monitoring": true,
    "internet_gateway": true,
    "security_critical": true,
    "retrofit_environment": true,
    "controls": ["topology-planning", "router-density", "channel-planning", "gateway-hardening", "secure-commissioning"]
  }'
```

## Repository Structure

```text
public/              Web entry point and responsive UI assets
src/                 PHP services, repository, security, and support classes
config/              Paper metadata and research-derived catalogs
database/            MySQL migration and seed scripts
docs/                Architecture, paper alignment, security, testing, and extension notes
bin/                 Lint and functional test scripts
```

## Responsible Use

This repository is designed for technology evaluation, secure design planning, and research translation. It is not a substitute for site engineering, electrical safety review, vendor certification, or production security governance.

## Production Notes

- Add authentication and role-based access before storing real installation data.
- Place the application behind HTTPS and a trusted reverse proxy.
- Store secrets outside source control.
- Treat home layout, sensor placement, gateway identifiers, and security-control evidence as sensitive operational data.
- Validate wireless channel behavior and router placement in the actual site environment.
- Review planner recommendations through engineering and security governance before production deployment.

## License

MIT License. See [LICENSE](LICENSE).

<!-- portfolio:start -->
## Portfolio and Professional Profile

This repository is part of the professional portfolio of [Musaab Hasan](https://musaab.info), focused on cybersecurity, digital forensics, AI governance, EdTech, secure platforms, and research-driven digital transformation.

### Digital Forensics and Security Research Labs

- [Android Digital Forensics Lab](https://github.com/musaabhasan/android-forensics-lab) - Advanced Android forensics workbench for acquisition planning, anti-forensics evaluation, memory triage, evidence integrity, and case reconstruction.
- [Humanoid Robot Forensics Lab](https://github.com/musaabhasan/humanoid-robot-forensics-lab) - PHP/MySQL forensic casework platform for humanoid robot, companion app, and IoT evidence triage.
- [Smart Metering Security Lab](https://github.com/musaabhasan/smart-metering-security-lab) - Research portal based on smart metering security analysis for cyber-physical and smart-grid environments.
- [Drive-by Download ML Lab](https://github.com/musaabhasan/driveby-download-ml-lab) - Machine learning research portal for detecting drive-by download attacks and web-based malware delivery.
- [SQL Injection ML Detection Lab](https://github.com/musaabhasan/sqli-ml-detection-lab) - Research portal for SQL injection detection using machine learning and security telemetry.
- [IoT Board SSH Hardening Lab](https://github.com/musaabhasan/iot-board-ssh-hardening-lab) - SSH exposure assessment and hardening portal for IoT development boards and embedded Linux systems.
- [ZigBee WHAS Design Lab](https://github.com/musaabhasan/zigbee-whas-design-lab) - Research portal for designing and evaluating ZigBee wireless home automation systems.
- [Mammogram Fourier Analysis Lab](https://github.com/musaabhasan/mammogram-fourier-analysis-lab) - Medical image-processing research portal based on Fourier transform analysis for mammography.

### Security Culture and Transformation Platforms

- [Human Factors Risk Profiler](https://github.com/musaabhasan/human-factors-risk-profiler) - Human-centered security risk profiling portal for targeted interventions and behavior-aware controls.
- [Security Champion Network Portal](https://github.com/musaabhasan/security-champion-network-portal) - Platform for managing security champion networks, missions, recognition, and measurable impact.
- [Crisis Simulation Command Portal](https://github.com/musaabhasan/crisis-simulation-command-portal) - Cyber crisis simulation planning, scoring, and improvement platform for resilience exercises.
- [Behavioral Security Metrics Portal](https://github.com/musaabhasan/behavioral-security-metrics-portal) - Evidence-based security awareness metrics portal focused on behavior, culture, and intervention outcomes.
- [Security Culture Heatmap Portal](https://github.com/musaabhasan/security-culture-heatmap-portal) - Security culture maturity heatmap for norms, leadership signals, and organizational readiness.
- [Emerging Technology Security Culture Portal](https://github.com/musaabhasan/emerging-technology-security-culture-portal) - Adoption-readiness portal for emerging technology, governance, and security culture alignment.
- [AI Use Case Evaluation Portal](https://github.com/musaabhasan/ai-use-case-evaluation-portal) - Evaluation platform for AI use cases across value, feasibility, data readiness, privacy, ethics, and governance.
- [Transformation Roadmap Portal](https://github.com/musaabhasan/transformation-roadmap-portal) - Roadmap platform for moving security culture programs from compliance orientation to resilience and measurable change.

### Governance, Education, and Secure Enablement

- [Professional Development Registration System Framework](https://github.com/musaabhasan/pdrs-framework) - Secure registration and Moodle enrollment automation framework for professional development programs.
- [Multilingual Certificate Issuer](https://github.com/musaabhasan/multilingual-certificate-issuer) - Arabic/English certificate design, PDF generation, and throttled SMTP distribution platform.
- [AI Security Governance Toolkit](https://github.com/musaabhasan/ai-security-governance-toolkit) - Practical AI security governance controls, templates, evidence registers, playbooks, and policy-as-code examples.

Professional profile and research portfolio: [https://musaab.info](https://musaab.info)
<!-- portfolio:end -->
