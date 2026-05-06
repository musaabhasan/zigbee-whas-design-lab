# Extension Guide

## Add Real Site Evidence

Recommended tables:

- `site_surveys`
- `channel_scans`
- `router_placements`
- `commissioning_events`
- `device_inventory`

These can be connected to `design_assessments` through the assessment identifier.

## Add Device Inventory

Suggested fields:

- Device identifier.
- Device type.
- Application class.
- Parent router.
- Firmware version.
- Battery target.
- Last join time.
- Placement zone.

## Add Operational Telemetry

Recommended metrics:

- Join failures.
- Packet delivery ratio.
- Battery state.
- Router utilization.
- Gateway availability.
- Interference observations.

## Add Authentication

Before using the repository operationally, add:

- User table with `password_hash`.
- Role model for viewer, assessor, reviewer, and administrator.
- Login, logout, failed-login logging, and session expiry.
- Access checks around assessment history and evidence pages.

