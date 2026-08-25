<?php
/**
 * Plugin Name: WP Clinical Launchpad
 * Plugin URI: https://github.com/nadimtuhin/wp-clinical-launchpad
 * Description: Pre-launch QA, clinical schema injection, Elementor asset optimization, and staging guardrails for medical websites.
 * Version: 1.1.0
 * Author: Nadim Faruque
 * License: MIT
 * Text Domain: wp-clinical-launchpad
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/includes/class-schema-manager.php';
require_once __DIR__ . '/includes/class-performance-optimizer.php';
require_once __DIR__ . '/includes/class-launch-guardrails.php';
require_once __DIR__ . '/includes/class-admin-settings.php';

final class WP_Clinical_Launchpad {
    private static ?WP_Clinical_Launchpad $instance = null;
    private \WPClinicalLaunchpad\SchemaManager $schema;
    private \WPClinicalLaunchpad\PerformanceOptimizer $perf;
    private \WPClinicalLaunchpad\LaunchGuardrails $guardrails;
    private \WPClinicalLaunchpad\AdminSettings $settings;

    public static function instance(): WP_Clinical_Launchpad {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->schema = new \WPClinicalLaunchpad\SchemaManager();
        $this->perf = new \WPClinicalLaunchpad\PerformanceOptimizer();
        $this->guardrails = new \WPClinicalLaunchpad\LaunchGuardrails();
        $this->settings = new \WPClinicalLaunchpad\AdminSettings();

        $this->init();
    }

    private function init(): void {
        $this->schema->register();
        $this->perf->register();
        $this->guardrails->register();
        $this->settings->register();
    }
}

add_action('plugins_loaded', ['WP_Clinical_Launchpad', 'instance']);
