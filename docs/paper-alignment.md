# Paper Alignment

This repository is based on:

Obaid, T., Rashed, H., Abou-Elnour, A., Rehan, M., Muhammad Saleh, M., & Tarique, M. (2014). **ZigBee Technology and Its Application in Wireless Home Automation Systems: A Survey**. *International Journal of Computer Networks & Communications*, 6(4), 115-131. https://doi.org/10.5121/ijcnc.2014.6411

## Concepts Implemented

| Paper concept | Repository implementation |
| --- | --- |
| ZigBee technical overview | ZigBee feature model with data rates, topologies, addressing, frequency bands, network size, and device roles. |
| Competing WHAS technologies | Technology comparison for ZigBee, Z-Wave, Insteon, Wavenis, Bluetooth, and WiFi. |
| Security applications | Security and safety application blueprint. |
| Smart surveillance | Remote monitoring and surveillance blueprint. |
| Energy management | Energy and load-management blueprint. |
| Assistive homes | Assistive home and voice-control blueprint. |
| Resource constraints | Challenge model and controls for device roles, battery budgets, and payload minimization. |
| Limited range | Topology, router-density, and site-survey controls. |
| ISM interference | Channel planning, coexistence testing, and gateway placement controls. |
| Internet connectivity | Gateway hardening, remote access control, and local fail-safe controls. |
| Orphan nodes | Address planning, parent capacity, and join monitoring controls. |
| Security exposure | Secure commissioning, key management, and security monitoring controls. |

## Deliberate Boundaries

The repository does not attempt to implement ZigBee radio firmware or replace hardware validation. It provides a design and assurance scaffold that can be extended with real device inventory, site-survey evidence, channel scans, commissioning data, and operational telemetry.

