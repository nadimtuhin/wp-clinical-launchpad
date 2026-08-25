<?php
/**
 * Plugin Name: WP Clinical Launchpad
 * Plugin URI: https://github.com/nadimtuhin/wp-clinical-launchpad
 * Description: Pre-launch QA, clinical schema injection, Elementor asset optimization, and staging guardrails for medical websites.
 * Version: 1.0.0
 * Author: Nadim Faruque
 * License: MIT
 * Text Domain: wp-clinical-launchpad
 */

if (!defined('ABSPATH')) {
    exit;
}

final class WP_Clinical_Launchpad {
    private static ?WP_Clinical_Launchpad $instance = null;

    public static function instance(): WP_Clinical_Launchpad {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_head', [$this, 'inject_medical_schema'], 1);
        add_action('wp_enqueue_scripts', [$this, 'optimize_elementor_assets'], 99);
        add_action('admin_notices', [$this, 'check_staging_leak_guardrails']);
    }

    public function inject_medical_schema(): void {
        if (!is_singular()) {
            return;
        }

        global $post;
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'MedicalWebPage',
            'name' => get_the_title(),
            'url' => get_permalink(),
            'lastReviewed' => get_the_modified_date('c'),
            'specialty' => 'Ophthalmology',
            'audience' => 'Patient'
        ];

        echo "\n<!-- WP Clinical Launchpad: Medical Schema -->\n";
        echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "</script>\n";
    }

    public function optimize_elementor_assets(): void {
        if (is_admin()) {
            return;
        }

        // Dequeue unneeded font-awesome if icons are inlined via SVG
        if (apply_filters('wp_clinical_disable_fa', true)) {
            wp_dequeue_style('font-awesome');
            wp_deregister_style('font-awesome');
        }
    }

    public function check_staging_leak_guardrails(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (get_option('blog_public') == '0') {
            echo '<div class="notice notice-warning is-dismissible"><p><strong>Launchpad Alert:</strong> Search engines are currently discouraged from indexing this site (noindex active).</p></div>';
        }
    }
}

add_action('plugins_loaded', ['WP_Clinical_Launchpad', 'instance']);
