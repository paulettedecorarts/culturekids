<?php

namespace Tests\Unit;

use App\Models\Activity;
use App\Models\OrganisationContentDecision;
use App\Support\OfflineBundle\ActivityOfflineBundleIdentity;
use PHPUnit\Framework\TestCase;

class ActivityOfflineBundleIdentityTest extends TestCase
{
    public function test_vocab_pack_uses_legacy_language_activity_id(): void
    {
        $activity = new Activity([
            'id' => 900,
            'type' => 'vocab_pack',
            'metadata' => ['legacy_language_activity_id' => 42],
        ]);

        $identity = ActivityOfflineBundleIdentity::resolve($activity);

        $this->assertSame([
            'content_type' => OrganisationContentDecision::TYPE_LANGUAGE,
            'content_id' => 42,
        ], $identity);
    }

    public function test_flashcard_uses_activity_id(): void
    {
        $activity = new Activity([
            'type' => 'flashcard',
            'metadata' => [],
        ]);
        $activity->id = 12;

        $identity = ActivityOfflineBundleIdentity::resolve($activity);

        $this->assertSame([
            'content_type' => OrganisationContentDecision::TYPE_FLASHCARD,
            'content_id' => 12,
        ], $identity);
    }

    public function test_drawing_kit_coloring_maps_to_colour_content_type(): void
    {
        $activity = new Activity([
            'id' => 50,
            'type' => 'drawing_kit',
            'metadata' => [
                'legacy_drawing_id' => 7,
                'drawing_type' => 'coloring',
            ],
        ]);

        $identity = ActivityOfflineBundleIdentity::resolve($activity);

        $this->assertSame([
            'content_type' => OrganisationContentDecision::TYPE_COLOURING,
            'content_id' => 7,
        ], $identity);
    }
}
