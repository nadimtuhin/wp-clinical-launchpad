<?php
namespace WPClinicalLaunchpad;

if (!defined('ABSPATH')) {
    exit;
}

final class SchemaManager {
    public function register(): void {
        add_action('wp_head', [$this, 'render_schema'], 1);
    }

    public function render_schema(): void {
        if (!is_singular()) {
            return;
        }

        global $post;
        $schema = $this->build_schema($post);
        if (empty($schema)) {
            return;
        }

        echo "\n<!-- WP Clinical Launchpad: Medical Structured Data -->\n";
        echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "</script>\n";
    }

    public function build_schema(?\WP_Post $post): array {
        if (!$post) {
            return [];
        }

        $specialty = get_post_meta($post->ID, '_clinical_specialty', true) ?: 'Ophthalmology';
        $procedure_type = get_post_meta($post->ID, '_clinical_procedure_type', true) ?: 'Surgical';

        return [
            '@context' => 'https://schema.org',
            '@type' => 'MedicalWebPage',
            'name' => get_the_title($post),
            'url' => get_permalink($post),
            'lastReviewed' => get_the_modified_date('c', $post),
            'about' => [
                '@type' => 'MedicalProcedure',
                'name' => get_the_title($post),
                'procedureType' => $procedure_type,
                'relevantSpecialty' => [
                    '@type' => 'MedicalSpecialty',
                    'name' => $specialty
                ]
            ],
            'audience' => [
                '@type' => 'MedicalAudience',
                'audienceType' => 'Patient'
            ]
        ];
    }
}
