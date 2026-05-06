INSERT INTO paper_references (
  id,
  title,
  authors,
  publication_year,
  journal,
  volume_number,
  issue_number,
  pages,
  doi,
  doi_url,
  citation
) VALUES (
  'obaid-rashed-abouelnour-rehan-saleh-tarique-2014',
  'ZigBee Technology and Its Application in Wireless Home Automation Systems: A Survey',
  'Thoraya Obaid; Haleemah Rashed; Ali Abou-Elnour; Muhammad Rehan; Mussab Muhammad Saleh; Mohammed Tarique',
  2014,
  'International Journal of Computer Networks & Communications',
  '6',
  '4',
  '115-131',
  '10.5121/ijcnc.2014.6411',
  'https://doi.org/10.5121/ijcnc.2014.6411',
  'Obaid, T., Rashed, H., Abou-Elnour, A., Rehan, M., Muhammad Saleh, M., & Tarique, M. (2014). ZigBee Technology and Its Application in Wireless Home Automation Systems: A Survey. International Journal of Computer Networks & Communications, 6(4), 115-131. https://doi.org/10.5121/ijcnc.2014.6411'
)
ON DUPLICATE KEY UPDATE title = VALUES(title);

INSERT INTO technology_catalog (id, name, frequency, range_meters, network_size, power_profile, installation_profile, strengths, tradeoffs) VALUES
('zigbee', 'ZigBee', '868 MHz, 915 MHz, 2.4 GHz', 100, 64000, 'Very low', 'Low-power sensor and actuator networks with mesh expansion.', JSON_ARRAY('Low power', 'Large network scale', 'Mesh support', 'Strong fit for sensors'), JSON_ARRAY('Gateway needed for internet connectivity', 'ISM-band interference', 'Careful address and routing planning required')),
('zwave', 'Z-Wave', '868 MHz, 908 MHz, 2.4 GHz', 100, 232, 'Low', 'Residential and light commercial short-message control.', JSON_ARRAY('Mature home automation ecosystem', 'Good residential fit', 'Low power'), JSON_ARRAY('Smaller network size than ZigBee', 'Vendor ecosystem dependency')),
('insteon', 'Insteon', '904 MHz', 45, 256, 'Not specified', 'Hybrid RF and powerline home automation.', JSON_ARRAY('RF and powerline options', 'Mesh-style communication'), JSON_ARRAY('Limited hop depth', 'Technology-specific ecosystem')),
('wavenis', 'Wavenis', '433 MHz, 868 MHz, 915 MHz', 1000, 0, 'Ultra-low', 'Longer-range low-power monitoring and control.', JSON_ARRAY('Long range', 'Ultra-low power'), JSON_ARRAY('Less common WHAS ecosystem', 'Unknown practical network scale in the paper comparison')),
('bluetooth', 'Bluetooth', '2.4 GHz', 10, 8, 'Medium', 'Short-range personal area connectivity.', JSON_ARRAY('Common device support', 'Simple short-range links'), JSON_ARRAY('Small network size', 'Range limitation', 'Higher power than ZigBee for WHAS use cases')),
('wifi', 'WiFi', '2.4 GHz, 5 GHz', 100, 2007, 'High', 'IP connectivity and high-throughput local networks.', JSON_ARRAY('Native IP access', 'Broad ecosystem', 'Higher throughput'), JSON_ARRAY('High power consumption', 'Interference with 2.4 GHz sensors', 'Less ideal for battery sensor nodes'))
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO application_catalog (id, name, paper_signal, design_focus, key_devices) VALUES
('security', 'Security and Safety Monitoring', 'The survey covers ZigBee systems for door/window monitoring, smoke, gas leak, water flooding, and remote warning.', 'High availability, low-latency alarms, tamper evidence, and reliable gateway notification.', JSON_ARRAY('Door sensor', 'Smoke sensor', 'Gas sensor', 'Water leak sensor', 'Alarm actuator')),
('surveillance', 'Smart Surveillance and Remote Monitoring', 'The survey discusses ZigBee gateways, remote monitoring, location systems, and abnormal-image or warning-message workflows.', 'Gateway availability, remote access controls, sensor placement, and clear event escalation.', JSON_ARRAY('Gateway', 'Presence sensor', 'Location beacon', 'Camera trigger', 'Notification service')),
('energy', 'Energy Management', 'The survey includes power monitoring, load control, current measurement, overload response, and energy-aware deployment.', 'Load visibility, appliance control, safe circuit response, and long battery life.', JSON_ARRAY('Smart outlet', 'Current sensor', 'Relay', 'Power meter', 'Load controller')),
('assistive', 'Assistive Home and Voice Control', 'The survey covers voice-controlled ZigBee systems designed to support elderly people and people with disabilities.', 'Accessible interfaces, fallback controls, response confirmation, and resilient local operation.', JSON_ARRAY('Voice interface', 'Touch panel', 'Relay module', 'Accessible notification', 'Profile manager'))
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO challenge_catalog (id, name, severity, paper_signal, mitigation) VALUES
('resource-constraint', 'Resource-constrained nodes', 'High', 'Sensors and nodes used in WHAS have limited processing power, memory, and battery.', 'Use FFD devices for routing and processing-heavy roles, reserve RFD devices for low-power sensing, and keep payloads compact.'),
('limited-range', 'Limited transmission range', 'High', 'ZigBee range can require multihop communication and router placement to cover a deployment.', 'Plan routers, mesh routes, signal margins, and installation zones before deployment.'),
('standard-dependency', 'IEEE 802.15.4 dependency', 'Medium', 'ZigBee depends on the physical and MAC layers of IEEE 802.15.4.', 'Track standard changes, chipset support, firmware maturity, and compatibility across devices.'),
('ism-interference', 'ISM-band interference', 'High', 'ZigBee WHAS must coexist with Bluetooth, WiFi, cordless phones, microwave ovens, and other devices in shared bands.', 'Perform channel planning, interference scans, gateway placement, and coexistence testing.'),
('internet-connectivity', 'Internet connectivity requirement', 'Medium', 'Remote monitoring may require internet connectivity through a suitable gateway.', 'Use a hardened gateway, authenticated remote access, encrypted communication, and fail-safe local operation.'),
('orphan-nodes', 'Orphan node and address planning', 'Medium', 'The paper notes orphan-node risk when devices cannot obtain suitable network addresses.', 'Plan network depth, child limits, address allocation, and parent-device capacity.'),
('security-exposure', 'Wireless security exposure', 'High', 'ZigBee networks can be exposed to wireless mesh protocol attacks and penetration risk.', 'Apply key management, secure commissioning, gateway isolation, event logging, and tamper-aware deployment.'),
('retrofit-cost', 'Retrofit and installation cost', 'Medium', 'The paper identifies installation and retrofit cost as a practical barrier for existing homes.', 'Use phased deployment, reusable gateway patterns, minimal wiring changes, and clear maintenance runbooks.')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO control_catalog (id, name, category, weight) VALUES
('device-role-planning', 'Plan coordinator, router, FFD, RFD, and end-device roles', 'Network design', 9),
('battery-budgeting', 'Define battery-life budgets for each sensor class', 'Power management', 8),
('payload-minimization', 'Keep sensor payloads compact and event-driven', 'Protocol efficiency', 5),
('topology-planning', 'Select star, tree, or mesh topology by home size and risk', 'Network design', 9),
('router-density', 'Place routers to support reliable multihop coverage', 'Coverage', 8),
('site-survey', 'Perform signal and placement survey before installation', 'Coverage', 7),
('standards-tracking', 'Track IEEE 802.15.4 and ZigBee stack compatibility', 'Lifecycle', 5),
('device-certification', 'Use certified or validated devices where possible', 'Lifecycle', 6),
('firmware-lifecycle', 'Maintain firmware inventory and update cadence', 'Lifecycle', 7),
('channel-planning', 'Plan channels around WiFi and other 2.4 GHz interference', 'Coexistence', 8),
('coexistence-testing', 'Test coexistence with WiFi, Bluetooth, and household appliances', 'Coexistence', 8),
('gateway-placement', 'Place gateways for stable bridging and usable signal margins', 'Gateway', 7),
('gateway-hardening', 'Harden the home automation gateway', 'Gateway', 9),
('remote-access-control', 'Protect remote access with strong authentication and encryption', 'Security', 9),
('local-fail-safe', 'Keep safety functions operational without internet access', 'Resilience', 8),
('address-planning', 'Plan network addressing, depth, and child limits', 'Network design', 7),
('parent-capacity', 'Validate parent-device capacity before joining nodes', 'Network design', 6),
('join-monitoring', 'Monitor join failures and orphan-node indicators', 'Operations', 6),
('secure-commissioning', 'Use secure commissioning for new devices', 'Security', 9),
('key-management', 'Manage network keys and device trust deliberately', 'Security', 9),
('security-monitoring', 'Monitor for unusual traffic, device joins, and gateway events', 'Security', 8),
('phased-rollout', 'Deploy in phases to reduce retrofit risk', 'Delivery', 5),
('maintenance-runbook', 'Maintain installation and support runbooks', 'Operations', 5),
('cost-modeling', 'Model installation, maintenance, and expansion cost', 'Delivery', 5)
ON DUPLICATE KEY UPDATE name = VALUES(name), weight = VALUES(weight);

INSERT INTO challenge_control_map (challenge_id, control_id) VALUES
('resource-constraint', 'device-role-planning'),
('resource-constraint', 'battery-budgeting'),
('resource-constraint', 'payload-minimization'),
('limited-range', 'topology-planning'),
('limited-range', 'router-density'),
('limited-range', 'site-survey'),
('standard-dependency', 'standards-tracking'),
('standard-dependency', 'device-certification'),
('standard-dependency', 'firmware-lifecycle'),
('ism-interference', 'channel-planning'),
('ism-interference', 'coexistence-testing'),
('ism-interference', 'gateway-placement'),
('internet-connectivity', 'gateway-hardening'),
('internet-connectivity', 'remote-access-control'),
('internet-connectivity', 'local-fail-safe'),
('orphan-nodes', 'address-planning'),
('orphan-nodes', 'parent-capacity'),
('orphan-nodes', 'join-monitoring'),
('security-exposure', 'secure-commissioning'),
('security-exposure', 'key-management'),
('security-exposure', 'security-monitoring'),
('retrofit-cost', 'phased-rollout'),
('retrofit-cost', 'maintenance-runbook'),
('retrofit-cost', 'cost-modeling')
ON DUPLICATE KEY UPDATE challenge_id = VALUES(challenge_id);

