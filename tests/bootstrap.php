<?php
/**
 * PHPUnit Bootstrap
 */

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

require_once __DIR__ . '/../includes/class-schema-manager.php';
require_once __DIR__ . '/../includes/class-performance-optimizer.php';
require_once __DIR__ . '/../includes/class-launch-guardrails.php';
require_once __DIR__ . '/../includes/class-admin-settings.php';

// Stub standard WordPress functions for unit tests
if (!function_exists('get_post_meta')) {
    function get_post_meta($post_id, $key = '', $single = false) {
        return $GLOBALS['wp_mock_meta'][$key] ?? '';
    }
}

if (!function_exists('get_the_title')) {
    function get_the_title($post = 0) {
        return 'Cataract Surgery';
    }
}

if (!function_exists('get_permalink')) {
    function get_permalink($post = 0) {
        return 'https://example-eye-clinic.com/cataract-surgery';
    }
}

if (!function_exists('get_the_modified_date')) {
    function get_the_modified_date($format = 'U', $post = null) {
        return '2026-08-25T10:00:00+00:00';
    }
}

if (!function_exists('get_option')) {
    function get_option($option, $default = false) {
        return $GLOBALS['wp_mock_options'][$option] ?? $default;
    }
}

if (!function_exists('is_ssl')) {
    function is_ssl() {
        return $GLOBALS['wp_mock_is_ssl'] ?? true;
    }
}

if (!function_exists('esc_url')) {
    function esc_url($url) {
        return $url;
    }
}

if (!function_exists('esc_html')) {
    function esc_html($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('is_admin')) {
    function is_admin() {
        return $GLOBALS['wp_mock_is_admin'] ?? false;
    }
}

if (!class_exists('WP_Post')) {
    // Minimal stand-in for WordPress core's final WP_Post class.
    class WP_Post {
        public $ID = 0;

        public function __construct($post = null) {
            foreach ((array) $post as $key => $value) {
                $this->$key = $value;
            }
        }
    }
}

if (!function_exists('is_singular')) {
    function is_singular($post_types = '') {
        return $GLOBALS['wp_mock_is_singular'] ?? true;
    }
}

if (!function_exists('register_setting')) {
    function register_setting($option_group, $option_name, $args = []) {
        $GLOBALS['wp_mock_registered_settings'][$option_name] = $args;
    }
}
