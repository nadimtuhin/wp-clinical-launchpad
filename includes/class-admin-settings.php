<?php
namespace WPClinicalLaunchpad;

if (!defined('ABSPATH')) {
    exit;
}

final class AdminSettings {
    public function register(): void {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function add_settings_page(): void {
        add_submenu_page(
            'tools.php',
            'WP Clinical Launchpad Settings',
            'Clinical Launchpad',
            'manage_options',
            'wp-clinical-launchpad',
            [$this, 'render_settings_page']
        );
    }

    private const SPECIALTIES = ['Ophthalmology', 'Dermatology', 'Dentistry', 'GeneralSurgery'];

    public function register_settings(): void {
        register_setting('wp_clinical_settings_group', 'wp_clinical_specialty', [
            'type' => 'string',
            'sanitize_callback' => [$this, 'sanitize_specialty'],
            'default' => 'Ophthalmology',
        ]);

        foreach (['wp_clinical_defer_elementor_css', 'wp_clinical_purge_fa_icons', 'wp_clinical_enforce_https'] as $option) {
            register_setting('wp_clinical_settings_group', $option, [
                'type' => 'string',
                'sanitize_callback' => [$this, 'sanitize_checkbox'],
                'default' => '1',
            ]);
        }
    }

    /**
     * Only accept one of the specialties offered in the settings form.
     */
    public function sanitize_specialty($value): string {
        return is_string($value) && in_array($value, self::SPECIALTIES, true) ? $value : 'Ophthalmology';
    }

    /**
     * Store checkboxes as '1' or '0' (an unchecked box submits nothing).
     */
    public function sanitize_checkbox($value): string {
        return $value === '1' || $value === 1 || $value === true ? '1' : '0';
    }

    public function render_settings_page(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        $specialty = get_option('wp_clinical_specialty', 'Ophthalmology');
        $defer_css = get_option('wp_clinical_defer_elementor_css', '1');
        $purge_fa = get_option('wp_clinical_purge_fa_icons', '1');
        $enforce_https = get_option('wp_clinical_enforce_https', '1');
        ?>
        <div class="wrap">
            <h1>🏥 WP Clinical Launchpad — Configuration & QA Engine</h1>
            <form method="post" action="options.php">
                <?php settings_fields('wp_clinical_settings_group'); ?>
                
                <h2 class="title" style="margin-top: 24px;">1. Clinical Schema & Taxonomy</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="wp_clinical_specialty">Medical Practice Specialty</label></th>
                        <td>
                            <select name="wp_clinical_specialty" id="wp_clinical_specialty" class="regular-text">
                                <option value="Ophthalmology" <?php selected($specialty, 'Ophthalmology'); ?>>Ophthalmology / Eye Surgery</option>
                                <option value="Dermatology" <?php selected($specialty, 'Dermatology'); ?>>Dermatology & Aesthetics</option>
                                <option value="Dentistry" <?php selected($specialty, 'Dentistry'); ?>>Dentistry / Orthodontics</option>
                                <option value="GeneralSurgery" <?php selected($specialty, 'GeneralSurgery'); ?>>General Surgery</option>
                            </select>
                            <p class="description">Injected as <code>MedicalSpecialty</code> inside <code>schema.org/MedicalWebPage</code> JSON-LD payloads.</p>
                        </td>
                    </tr>
                </table>

                <h2 class="title" style="margin-top: 24px;">2. Elementor & Core Web Vitals Optimization</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Non-Critical CSS Deferral</th>
                        <td>
                            <label>
                                <input type="checkbox" name="wp_clinical_defer_elementor_css" value="1" <?php checked($defer_css, '1'); ?>>
                                Asynchronously preload Elementor post & template stylesheets (avoids render-blocking).
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">FontAwesome Icon Purge</th>
                        <td>
                            <label>
                                <input type="checkbox" name="wp_clinical_purge_fa_icons" value="1" <?php checked($purge_fa, '1'); ?>>
                                Dequeue default FontAwesome 4 & 5 CSS shims when inline SVGs are utilized.
                            </label>
                        </td>
                    </tr>
                </table>

                <h2 class="title" style="margin-top: 24px;">3. Launch Guardrails & Staging Safe-Check</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Strict HTTPS Enforcement</th>
                        <td>
                            <label>
                                <input type="checkbox" name="wp_clinical_enforce_https" value="1" <?php checked($enforce_https, '1'); ?>>
                                Force all internal asset URLs and REST API endpoints to HTTPS.
                            </label>
                        </td>
                    </tr>
                </table>

                <?php submit_button('Save Configuration & Re-run Audit'); ?>
            </form>
        </div>
        <?php
    }
}
