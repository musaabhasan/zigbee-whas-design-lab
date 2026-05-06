# Security Notes

## Application Controls

- Security headers are applied to all web responses.
- Form submissions use CSRF tokens.
- Session cookies use `HttpOnly`, `SameSite=Lax`, and secure cookies when HTTPS is detected.
- User input is normalized before scoring and persistence.
- PDO prepared statements are used for database writes.
- JSON fields are validated by MySQL checks.
- Database connection failures fall back to catalog-only operation.

## WHAS Security Controls

The platform models several security controls for ZigBee deployments:

- Secure commissioning for new devices.
- Deliberate network key and trust management.
- Gateway hardening for internet-connected deployments.
- Strong authentication and encryption for remote access.
- Security monitoring for unusual joins, device events, and gateway activity.
- Local fail-safe operation for safety functions.

## Production Handling

Home layout, sensor placement, gateway details, remote access design, and device identifiers can expose security posture. Treat assessment records as sensitive and apply role-based access, retention, encryption, monitoring, and backup controls before production use.

