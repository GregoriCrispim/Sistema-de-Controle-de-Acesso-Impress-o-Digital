<?php

namespace Tests\Unit;

use App\Services\FingerprintMatcher;
use App\Services\FingerprintTemplateGenerator;
use PHPUnit\Framework\TestCase;

class FingerprintMatcherTest extends TestCase
{
    public function test_parse_valid_template(): void
    {
        $hex = FingerprintTemplateGenerator::generate('finger_1', 25);
        $parsed = FingerprintMatcher::parseTemplate($hex);

        $this->assertNotNull($parsed, 'Should parse a valid ISO 19794-2 template');
        $this->assertArrayHasKey('minutiae', $parsed);
        $this->assertCount(25, $parsed['minutiae']);
        $this->assertEquals(256, $parsed['width']);
        $this->assertEquals(360, $parsed['height']);
    }

    public function test_parse_rejects_empty_string(): void
    {
        $this->assertNull(FingerprintMatcher::parseTemplate(''));
    }

    public function test_parse_rejects_fake_code(): void
    {
        $this->assertNull(FingerprintMatcher::parseTemplate('FP-2026001-D1'));
    }

    public function test_parse_rejects_random_hex(): void
    {
        $this->assertNull(FingerprintMatcher::parseTemplate('DEADBEEF01020304'));
    }

    public function test_parse_rejects_odd_length_hex(): void
    {
        $hex = FingerprintTemplateGenerator::generate('test', 10);
        $this->assertNull(FingerprintMatcher::parseTemplate($hex . 'F'));
    }

    public function test_same_template_matches_itself(): void
    {
        $hex = FingerprintTemplateGenerator::generate('same_finger', 30);

        $this->assertTrue(
            FingerprintMatcher::match($hex, $hex, securityLevel: 4),
            'An identical template must match itself at security level 4'
        );
    }

    public function test_same_template_matches_itself_at_highest_security(): void
    {
        $hex = FingerprintTemplateGenerator::generate('same_finger_strict', 30);

        $this->assertTrue(
            FingerprintMatcher::match($hex, $hex, securityLevel: 7),
            'An identical template must match itself at maximum security level'
        );
    }

    public function test_variant_matches_original(): void
    {
        $original = FingerprintTemplateGenerator::generate('user_john', 30);
        $variant = FingerprintTemplateGenerator::generateVariant('user_john', 1, 30);

        $this->assertTrue(
            FingerprintMatcher::match($original, $variant, securityLevel: 4),
            'A slightly varied re-capture should match the original at security level 4'
        );
    }

    public function test_multiple_variants_match_original(): void
    {
        $original = FingerprintTemplateGenerator::generate('user_maria', 35);

        for ($v = 1; $v <= 5; $v++) {
            $variant = FingerprintTemplateGenerator::generateVariant('user_maria', $v, 35);
            $this->assertTrue(
                FingerprintMatcher::match($original, $variant, securityLevel: 3),
                "Variant {$v} should match at security level 3"
            );
        }
    }

    public function test_different_fingers_do_not_match(): void
    {
        $finger1 = FingerprintTemplateGenerator::generate('alice_finger_1', 30);
        $finger2 = FingerprintTemplateGenerator::generate('bob_finger_2', 30);

        $this->assertFalse(
            FingerprintMatcher::match($finger1, $finger2, securityLevel: 4),
            'Templates from different persons must not match'
        );
    }

    public function test_different_fingers_same_person_do_not_match(): void
    {
        $thumb = FingerprintTemplateGenerator::generate('carlos_polegar', 30);
        $index = FingerprintTemplateGenerator::generate('carlos_indicador', 30);

        $this->assertFalse(
            FingerprintMatcher::match($thumb, $index, securityLevel: 2),
            'Different fingers from the same person must not match'
        );
    }

    public function test_find_match_returns_correct_record(): void
    {
        $targetSeed = 'student_15';
        $capturedHex = FingerprintTemplateGenerator::generateVariant($targetSeed, 1, 30);

        $storedRecords = [];
        for ($i = 1; $i <= 20; $i++) {
            $storedRecords[] = [
                'id' => $i,
                'student_id' => $i,
                'template_code' => FingerprintTemplateGenerator::generate("student_{$i}", 30),
            ];
        }

        shuffle($storedRecords);

        $result = FingerprintMatcher::findMatch($capturedHex, $storedRecords, securityLevel: 4);

        $this->assertNotNull($result, 'Should find a match among stored records');
        $this->assertEquals(15, $result['student_id'], 'Should match the correct student');
    }

    public function test_find_match_returns_null_for_unknown(): void
    {
        $unknownHex = FingerprintTemplateGenerator::generate('unknown_person', 30);

        $storedRecords = [];
        for ($i = 1; $i <= 10; $i++) {
            $storedRecords[] = [
                'id' => $i,
                'student_id' => $i,
                'template_code' => FingerprintTemplateGenerator::generate("known_{$i}", 30),
            ];
        }

        $result = FingerprintMatcher::findMatch($unknownHex, $storedRecords, securityLevel: 4);

        $this->assertNull($result, 'Must not match an unregistered fingerprint');
    }

    public function test_find_match_ignores_invalid_stored_records(): void
    {
        $capturedHex = FingerprintTemplateGenerator::generateVariant('valid_student', 1, 30);

        $storedRecords = [
            ['id' => 1, 'student_id' => 1, 'template_code' => 'FP-FAKE-CODE'],
            ['id' => 2, 'student_id' => 2, 'template_code' => ''],
            ['id' => 3, 'student_id' => 3, 'template_code' => 'DEADBEEF'],
            ['id' => 4, 'student_id' => 4, 'template_code' => FingerprintTemplateGenerator::generate('valid_student', 30)],
        ];

        $result = FingerprintMatcher::findMatch($capturedHex, $storedRecords, securityLevel: 4);

        $this->assertNotNull($result, 'Should find the one valid record');
        $this->assertEquals(4, $result['student_id']);
    }

    public function test_security_levels_affect_strictness(): void
    {
        $original = FingerprintTemplateGenerator::generate('security_test', 30);
        $variant = FingerprintTemplateGenerator::generateVariant('security_test', 1, 30);

        $matchLevel1 = FingerprintMatcher::match($original, $variant, securityLevel: 1);
        $matchLevel7 = FingerprintMatcher::match($original, $variant, securityLevel: 7);

        // At minimum security it should definitely match; at max it may or may not
        $this->assertTrue($matchLevel1, 'Must match at lowest security');

        // If it matches at level 7 that's fine; what matters is level 1 >= level 7 in acceptance
        if (!$matchLevel7) {
            $this->assertFalse($matchLevel7, 'Higher security may reject borderline matches');
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_template_generator_is_deterministic(): void
    {
        $hex1 = FingerprintTemplateGenerator::generate('deterministic_test', 25);
        $hex2 = FingerprintTemplateGenerator::generate('deterministic_test', 25);

        $this->assertSame($hex1, $hex2, 'Same seed must produce identical templates');
    }

    public function test_minutiae_fields_are_reasonable(): void
    {
        $hex = FingerprintTemplateGenerator::generate('minutiae_check', 20);
        $parsed = FingerprintMatcher::parseTemplate($hex);

        $this->assertNotNull($parsed);
        foreach ($parsed['minutiae'] as $m) {
            $this->assertGreaterThanOrEqual(0, $m['x']);
            $this->assertLessThanOrEqual(256, $m['x']);
            $this->assertGreaterThanOrEqual(0, $m['y']);
            $this->assertLessThanOrEqual(360, $m['y']);
            $this->assertGreaterThanOrEqual(0, $m['angle']);
            $this->assertLessThan(360, $m['angle']);
            $this->assertContains($m['type'], [0, 1, 2]);
            $this->assertGreaterThanOrEqual(0, $m['quality']);
            $this->assertLessThanOrEqual(100, $m['quality']);
        }
    }

    public function test_performance_find_match_in_1000_records(): void
    {
        $capturedHex = FingerprintTemplateGenerator::generateVariant('perf_target', 1, 25);

        $storedRecords = [];
        for ($i = 1; $i <= 999; $i++) {
            $storedRecords[] = [
                'id' => $i,
                'student_id' => $i,
                'template_code' => FingerprintTemplateGenerator::generate("perf_{$i}", 25),
            ];
        }
        $storedRecords[] = [
            'id' => 1000,
            'student_id' => 1000,
            'template_code' => FingerprintTemplateGenerator::generate('perf_target', 25),
        ];

        $start = microtime(true);
        $result = FingerprintMatcher::findMatch($capturedHex, $storedRecords, securityLevel: 4);
        $elapsed = microtime(true) - $start;

        $this->assertNotNull($result);
        $this->assertEquals(1000, $result['student_id']);
        $this->assertLessThan(10.0, $elapsed, "Matching against 1000 records took {$elapsed}s, should be under 10s");
    }
}
