<?php
namespace WPClinicalLaunchpad;

if (!defined('ABSPATH')) {
    exit;
}

final class LaunchGuardrails {
    public function register(): void {
        add_action('admin_notices', [$this, 'render_launch_audit_bar']);
        add_action('wp_dashboard_setup', [$this, 'add_dashboard_widget']);
    }

    public function run_audit(): array {
        $issues = [];
        $passed = [];

        // 1. Search Engine Visibility Check
        if (get_option('blog_public') == '0') {
            $issues[] = 'Search engines are blocked (noindex active under Settings > Reading).';
        } else {
            $passed[] = 'Search engines allowed to index.';
        }

        // 2. Kinsta / Staging URL leakage check
        $site_url = get_option('siteurl');
        if (str_contains($site_url, 'kinsta.cloud') || str_contains($site_url, 'staging')) {
            $issues[] = "Site URL appears to use a staging host ($site_url). Update to production domain.";
        } else {
            $passed[] = "Production domain configured ($site_url).";
        }

        // 3. SSL / HTTPS Enforcement
        if (!is_ssl() && !str_starts_with($site_url, 'https://')) {
            $issues[] = 'HTTPS is not strictly active on site URL.';
        } else {
            $passed[] = 'HTTPS enabled.';
        }

        // 4. Elementor Default Kit Check
        $active_kit = get_option('elementor_active_kit');
        if (!$active_kit) {
            $issues[] = 'Elementor global style kit is missing or unassigned.';
        } else {
            $passed[] = 'Elementor global style kit active.';
        }

        return ['issues' => $issues, 'passed' => $passed];
    }

    public function render_launch_audit_bar(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        $audit = $this->run_audit();
        if (empty($audit['issues'])) {
            return;
        }

        echo '<div class="notice notice-error is-dismissible" style="border-left-color: #d63638; padding: 12px;">';
        echo '<strong style="font-size: 14px;">🚨 WP Clinical Launchpad — Critical Pre-Launch Issues Found:</strong><ul style="margin: 8px 0 0 16px; list-style-type: disc;">';
        foreach ($audit['issues'] as $issue) {
            echo '<li>' . esc_html($issue) . '</li>';
        }
        echo '</ul></div>';
    }

    public function add_dashboard_widget(): void {
        // The audit reveals the site URL, staging host and configuration gaps;
        // keep it to the same audience as the admin notice.
        if (!current_user_can('manage_options')) {
            return;
        }

        wp_add_dashboard_widget(
            'wp_clinical_launchpad_widget',
            '🏥 Clinical Launchpad — Launch Readiness Checklist',
            [$this, 'render_dashboard_widget']
        );
    }

    public function render_dashboard_widget(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        $audit = $this->run_audit();
        echo '<div style="font-size: 13px;">';
        if (!empty($audit['issues'])) {
            echo '<p style="color: #d63638; font-weight: bold;">Items requiring attention before live traffic:</p><ul>';
            foreach ($audit['issues'] as $item) {
                echo '<li style="margin-bottom: 6px;">❌ ' . esc_html($item) . '</li>';
            }
            echo '</ul>';
        }

        echo '<p style="color: #008a20; font-weight: bold; margin-top: 12px;">Passed checks:</p><ul>';
        foreach ($audit['passed'] as $pass) {
            echo '<li style="margin-bottom: 6px;">✅ ' . esc_html($pass) . '</li>';
        }
        echo '</ul></div>';
    }
}
