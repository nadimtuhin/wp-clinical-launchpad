# WP Clinical Launchpad

> Modular pre-launch QA, clinical schema engine (`schema.org/MedicalWebPage`), and Elementor Core Web Vitals optimization toolkit for medical & healthcare WordPress websites.

[![CI](https://github.com/nadimtuhin/wp-clinical-launchpad/actions/workflows/ci.yml/badge.svg)](https://github.com/nadimtuhin/wp-clinical-launchpad/actions)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP: 8.0+](https://img.shields.io/badge/PHP-8.0%2B-777BB4.svg?logo=php)](https://www.php.net)
[![Elementor Compatible](https://img.shields.io/badge/Elementor-Ready-92003B.svg)](https://elementor.com)

---

## 🏥 Architecture & Problem Statement

Medical and clinical practices (ophthalmology, refractive surgery, dental, aesthetics) face unique challenges when launching custom Elementor websites:
1. **Search Engine Misclassification**: Missing specialized clinical structured data (`MedicalWebPage`, `MedicalProcedure`, `MedicalAudience`) reduces organic visibility for high-intent patient queries.
2. **Elementor Asset Bloat**: Default icon font packages (FontAwesome shim styles) and non-critical CSS files block main thread execution, hurting Core Web Vitals (LCP & CLS).
3. **Staging Leaks**: Migrating from Kinsta/WP Engine staging environments frequently leaves `noindex` directives active or hardcoded staging domains in place.

`wp-clinical-launchpad` provides an automated, OOP WordPress architecture to resolve these issues before cutover.

---

## 🛠️ Modular Subsystems

```
wp-clinical-launchpad/
├── wp-clinical-launchpad.php          # Main bootstrap & lifecycle coordinator
├── includes/
│   ├── class-schema-manager.php       # Dynamic schema.org/MedicalWebPage JSON-LD builder
│   ├── class-performance-optimizer.php# Elementor stylesheet deferral & CSS cleanup
│   └── class-launch-guardrails.php    # Admin pre-launch audit & Kinsta staging checks
└── .github/workflows/ci.yml           # Automated PHP 8.2 syntax & lint matrix
```

### 1. Clinical Schema Engine (`SchemaManager`)
- Dynamically outputs structured JSON-LD data for single clinical/procedure pages.
- Populates `specialty` (e.g. Ophthalmology), `MedicalProcedure`, and `MedicalAudience` attributes for rich search snippets.

### 2. Core Web Vitals & Elementor Tuning (`PerformanceOptimizer`)
- Selectively dequeues unneeded FontAwesome subsets when SVG icon renderers are present.
- Dequeues default Gutenberg block stylesheets (`wp-block-library`, `global-styles`) when Elementor manages the design system.
- Converts non-critical Elementor post CSS links into asynchronous preload tags.
- Injects font preconnect tags.

### 3. Pre-Launch Guardrails (`LaunchGuardrails`)
- Automated audit runs inside the WordPress Admin Dashboard.
- Flags:
  - Active `blog_public = 0` (discourage search engines).
  - Staging host URLs (e.g. `kinsta.cloud`, `staging`).
  - Missing SSL / non-HTTPS site URLs.
  - Unset or missing Elementor Global Kit tokens.

---

## 🧪 Testing & Validation

Run local syntax and lint verification:
```bash
for file in $(find . -name "*.php"); do
  php -l "$file"
done
```

---

## 📄 License
MIT © [Nadim Faruque](https://github.com/nadimtuhin)
