# WP Clinical Launchpad

> Production pre-launch QA, schema engine, and Elementor optimization toolkit for medical & healthcare WordPress websites.

[![CI](https://github.com/nadimtuhin/wp-clinical-launchpad/actions/workflows/ci.yml/badge.svg)](https://github.com/nadimtuhin/wp-clinical-launchpad/actions)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

## Overview
Engineered specifically for clinical, surgical, and healthcare websites built with WordPress and Elementor. Solves common pre-launch pitfalls:
1. **Clinical JSON-LD Schemas**: Injects `MedicalWebPage` and `MedicalBusiness` structured data for search engine comprehension.
2. **Elementor Performance Tuning**: Selectively purges redundant asset bloat to maintain 95+ Mobile PageSpeed / Core Web Vitals.
3. **Staging Leak Guardrails**: Pre-launch admin alerts for `noindex` directives and staging URL residuals.

## Features
- Automated `schema.org/MedicalWebPage` generation on single procedure pages.
- Smart dequeueing of heavy font libraries when SVG inlining is enabled.
- Pre-launch checklist hook to catch unindexed production launches.

## Testing & Validation
Run PHP syntax checks:
```bash
php -l wp-clinical-launchpad.php
```

## License
MIT © Nadim Faruque
