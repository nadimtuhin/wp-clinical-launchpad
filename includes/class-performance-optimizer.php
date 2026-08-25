<?php
namespace WPClinicalLaunchpad;

if (!defined('ABSPATH')) {
    exit;
}

final class PerformanceOptimizer {
    public function register(): void {
        add_action('wp_enqueue_scripts', [$this, 'optimize_elementor_payload'], 99);
        add_action('wp_head', [$this, 'inject_preconnect_hints'], 2);
        add_filter('style_loader_tag', [$this, 'defer_non_critical_css'], 10, 4);
    }

    public function optimize_elementor_payload(): void {
        if (is_admin()) {
            return;
        }

        // Dequeue redundant icon fonts if icons are rendered as inline SVGs
        if (get_option('elementor_load_fa4_shim') !== 'yes') {
            wp_dequeue_style('elementor-icons-fa-solid');
            wp_dequeue_style('elementor-icons-fa-brands');
            wp_dequeue_style('elementor-icons-fa-regular');
        }

        // Disable WordPress default block styles if Elementor handles all layouts
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('wp-block-library-theme');
        wp_dequeue_style('global-styles');
    }

    public function inject_preconnect_hints(): void {
        echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
    }

    public function defer_non_critical_css(string $html, string $handle, string $href, string $media): string {
        $defer_handles = ['elementor-post-', 'elementor-global'];
        foreach ($defer_handles as $prefix) {
            if (str_contains($handle, $prefix) && !is_admin()) {
                return '<link rel="preload" as="style" href="' . esc_url($href) . '" onload="this.onload=null;this.rel=\'stylesheet\'">' . "\n";
            }
        }
        return $html;
    }
}
