<?php

namespace Tests\Unit\Services;

use App\Models\OrganisationContentDecision;
use App\Services\ContentTranslationSubItemResolver;
use Tests\TestCase;

class ContentTranslationSubItemResolverTest extends TestCase
{
    public function test_all_twelve_types_have_field_labels(): void
    {
        $resolver = app(ContentTranslationSubItemResolver::class);

        foreach (array_keys(config('content_translations.types', [])) as $type) {
            $labels = $resolver->fieldLabels($type, null, null);
            $this->assertNotEmpty($labels['word_label'], "Missing word_label for {$type}");
            $this->assertNotEmpty($labels['translation_label'], "Missing translation_label for {$type}");
        }
    }

    public function test_puzzle_content_field_options(): void
    {
        $resolver = app(ContentTranslationSubItemResolver::class);
        $options = $resolver->options(OrganisationContentDecision::TYPE_PUZZLE, 1);

        $this->assertSame(
            ['field:tag', 'field:description'],
            array_column($options, 'key')
        );
    }
}
