<?php
namespace WPClinicalLaunchpad\Tests;

use PHPUnit\Framework\TestCase;
use WPClinicalLaunchpad\SchemaManager;
use WPClinicalLaunchpad\LaunchGuardrails;
use WPClinicalLaunchpad\PerformanceOptimizer;

class LaunchpadUnitTest extends TestCase {
    protected function setUp(): void {
        $GLOBALS['wp_mock_meta'] = [];
        $GLOBALS['wp_mock_options'] = [];
        $GLOBALS['wp_mock_is_ssl'] = true;
    }

    public function test_schema_builder_formats_medical_procedure_correctly(): void {
        $schema_mgr = new SchemaManager();
        $GLOBALS['wp_mock_meta']['_clinical_specialty'] = 'Ophthalmology';
        $GLOBALS['wp_mock_meta']['_clinical_procedure_type'] = 'Laser Eye Procedure';

        $fake_post = new \WP_Post((object) ['ID' => 42]);
        $schema = $schema_mgr->build_schema($fake_post);

        $this->assertEquals('https://schema.org', $schema['@context']);
        $this->assertEquals('MedicalWebPage', $schema['@type']);
        $this->assertEquals('Cataract Surgery', $schema['name']);
        $this->assertEquals('Laser Eye Procedure', $schema['about']['procedureType']);
        $this->assertEquals('Ophthalmology', $schema['about']['relevantSpecialty']['name']);
        $this->assertEquals('Patient', $schema['audience']['audienceType']);
    }

    public function test_schema_output_cannot_break_out_of_script_tag(): void {
        $schema_mgr = new SchemaManager();
        $GLOBALS['wp_mock_meta']['_clinical_specialty'] = '</script><script>alert(1)</script>';
        $GLOBALS['post'] = new \WP_Post((object) ['ID' => 42]);

        ob_start();
        $schema_mgr->render_schema();
        $output = ob_get_clean();
        unset($GLOBALS['post']);

        $this->assertSame(1, substr_count(strtolower($output), '</script'));
        $this->assertStringNotContainsString('<script>alert(1)', $output);

        preg_match('#<script type="application/ld\+json">(.*)</script>#s', $output, $m);
        $decoded = json_decode($m[1], true);
        $this->assertSame('</script><script>alert(1)</script>', $decoded['about']['relevantSpecialty']['name']);
    }

    public function test_guardrails_catches_noindex_and_staging_urls(): void {
        $guardrails = new LaunchGuardrails();
        $GLOBALS['wp_mock_options']['blog_public'] = '0';
        $GLOBALS['wp_mock_options']['siteurl'] = 'https://myclinic.kinsta.cloud';
        $GLOBALS['wp_mock_options']['elementor_active_kit'] = null;

        $audit = $guardrails->run_audit();

        $this->assertCount(3, $audit['issues']);
        $this->assertStringContainsString('noindex active', $audit['issues'][0]);
        $this->assertStringContainsString('staging host', $audit['issues'][1]);
        $this->assertStringContainsString('Elementor global style kit', $audit['issues'][2]);
    }

    public function test_guardrails_passes_clean_production_environment(): void {
        $guardrails = new LaunchGuardrails();
        $GLOBALS['wp_mock_options']['blog_public'] = '1';
        $GLOBALS['wp_mock_options']['siteurl'] = 'https://example-eye-clinic.com';
        $GLOBALS['wp_mock_options']['elementor_active_kit'] = 12;

        $audit = $guardrails->run_audit();

        $this->assertEmpty($audit['issues']);
        $this->assertCount(4, $audit['passed']);
    }

    public function test_performance_optimizer_defers_elementor_styles(): void {
        $optimizer = new PerformanceOptimizer();
        $input_html = '<link rel="stylesheet" id="elementor-post-42-css" href="https://example.com/style.css" type="text/css" media="all" />';
        
        $output_html = $optimizer->defer_non_critical_css($input_html, 'elementor-post-42', 'https://example.com/style.css', 'all');

        $this->assertStringContainsString('rel=\'stylesheet\'', $output_html);
        $this->assertStringContainsString('as="style"', $output_html);
        $this->assertStringContainsString('preload', $output_html);
    }
}
