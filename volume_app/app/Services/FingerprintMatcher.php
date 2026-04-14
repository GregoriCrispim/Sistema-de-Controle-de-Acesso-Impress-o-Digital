<?php

namespace App\Services;

/**
 * Fingerprint matcher implementing ISO 19794-2 template parsing and
 * minutiae-based comparison. Replaces the need for external SDK matching.
 *
 * ISO 19794-2 template structure:
 *   [Record Header 24B] [Finger View Header 4B] [Minutiae 6B each] [Extended data...]
 *
 * Each minutia stores: type (2 bits), X position (14 bits), Y position (14 bits),
 * angle (1 byte, value * 1.40625 = degrees), quality (1 byte).
 *
 * Matching works by finding the rigid transform (rotation + translation) that
 * best aligns two sets of minutiae, then counting corresponding pairs within
 * spatial and angular tolerances.
 */
class FingerprintMatcher
{
    private const SPATIAL_TOLERANCE_PX = 18;
    private const ANGLE_TOLERANCE_DEG = 16;

    private const MAX_ANCHORS = 12;

    private const SECURITY_THRESHOLDS = [
        1 => 0.14,
        2 => 0.20,
        3 => 0.26,
        4 => 0.33,
        5 => 0.42,
        6 => 0.52,
        7 => 0.64,
    ];

    /**
     * Parse an ISO 19794-2 fingerprint template from a hex string.
     *
     * @return array{width: int, height: int, quality: int, minutiae: list<array{x: int, y: int, angle: float, type: int, quality: int}>}|null
     */
    public static function parseTemplate(string $hex): ?array
    {
        $hex = trim($hex);
        if (strlen($hex) < 60 || strlen($hex) % 2 !== 0 || !ctype_xdigit($hex)) {
            return null;
        }

        $bytes = hex2bin($hex);
        if ($bytes === false || strlen($bytes) < 30) {
            return null;
        }

        $formatId = substr($bytes, 0, 3);
        if ($formatId !== 'FMR') {
            return null;
        }

        $width  = unpack('n', $bytes, 14)[1];
        $height = unpack('n', $bytes, 16)[1];

        $numViews = ord($bytes[22]);
        if ($numViews < 1) {
            return null;
        }

        $offset      = 24;
        $quality     = ord($bytes[$offset + 2]);
        $numMinutiae = ord($bytes[$offset + 3]);
        $offset += 4;

        $minutiae = [];
        $bytesLen = strlen($bytes);

        for ($i = 0; $i < $numMinutiae && ($offset + 5) < $bytesLen; $i++) {
            $word1 = unpack('n', $bytes, $offset)[1];
            $word2 = unpack('n', $bytes, $offset + 2)[1];

            $type     = ($word1 >> 14) & 0x03;
            $x        = $word1 & 0x3FFF;
            $y        = $word2 & 0x3FFF;
            $angleDeg = ord($bytes[$offset + 4]) * 1.40625;
            $mQuality = ord($bytes[$offset + 5]);

            $minutiae[] = [
                'x'       => $x,
                'y'       => $y,
                'angle'   => $angleDeg,
                'type'    => $type,
                'quality' => $mQuality,
            ];

            $offset += 6;
        }

        if (count($minutiae) < 3) {
            return null;
        }

        return [
            'width'    => $width,
            'height'   => $height,
            'quality'  => $quality,
            'minutiae' => $minutiae,
        ];
    }

    /**
     * Compare two hex-encoded ISO 19794-2 templates.
     */
    public static function match(string $hex1, string $hex2, int $securityLevel = 4): bool
    {
        $t1 = self::parseTemplate($hex1);
        $t2 = self::parseTemplate($hex2);

        if (!$t1 || !$t2) {
            return false;
        }

        $score     = self::computeMatchScore($t1['minutiae'], $t2['minutiae']);
        $threshold = self::SECURITY_THRESHOLDS[max(1, min(7, $securityLevel))] ?? 0.33;

        return $score >= $threshold;
    }

    /**
     * Search a list of stored fingerprints for the first match.
     *
     * @param  string  $capturedHex     Captured template in hex
     * @param  array   $storedRecords   Array of ['id' => ..., 'student_id' => ..., 'template_code' => ...]
     * @param  int     $securityLevel   1 (lowest) to 7 (highest)
     * @return array|null               The matched record, or null
     */
    public static function findMatch(string $capturedHex, array $storedRecords, int $securityLevel = 4): ?array
    {
        $captured = self::parseTemplate($capturedHex);
        if (!$captured) {
            return null;
        }

        $threshold = self::SECURITY_THRESHOLDS[max(1, min(7, $securityLevel))] ?? 0.33;

        foreach ($storedRecords as $record) {
            $stored = self::parseTemplate($record['template_code'] ?? '');
            if (!$stored) {
                continue;
            }

            $score = self::computeMatchScore($captured['minutiae'], $stored['minutiae']);
            if ($score >= $threshold) {
                return $record;
            }
        }

        return null;
    }

    /**
     * Core matching: find the best rigid alignment between two minutiae sets
     * and return the ratio of matched pairs to the smaller set's size.
     */
    private static function computeMatchScore(array $m1, array $m2): float
    {
        $n1 = count($m1);
        $n2 = count($m2);

        if ($n1 === 0 || $n2 === 0) {
            return 0.0;
        }

        $anchors1 = self::selectAnchors($m1);
        $anchors2 = self::selectAnchors($m2);

        $spatialTolSq = self::SPATIAL_TOLERANCE_PX * self::SPATIAL_TOLERANCE_PX;
        $angleTol     = self::ANGLE_TOLERANCE_DEG;
        $bestMatched  = 0;

        foreach ($anchors1 as $a1) {
            foreach ($anchors2 as $a2) {
                $rotDeg = $a1['angle'] - $a2['angle'];
                $rotRad = $rotDeg * 0.017453292519943; // deg2rad

                $cosR = cos($rotRad);
                $sinR = sin($rotRad);

                $rx = $a2['x'] * $cosR - $a2['y'] * $sinR;
                $ry = $a2['x'] * $sinR + $a2['y'] * $cosR;
                $tx = $a1['x'] - $rx;
                $ty = $a1['y'] - $ry;

                $matched = 0;
                $used    = [];

                for ($j = 0; $j < $n2; $j++) {
                    $m       = $m2[$j];
                    $trX     = $m['x'] * $cosR - $m['y'] * $sinR + $tx;
                    $trY     = $m['x'] * $sinR + $m['y'] * $cosR + $ty;
                    $trAngle = fmod($m['angle'] + $rotDeg + 720.0, 360.0);

                    $bestDistSq = PHP_FLOAT_MAX;
                    $bestIdx    = -1;

                    for ($k = 0; $k < $n1; $k++) {
                        if (isset($used[$k])) {
                            continue;
                        }

                        $dx = $trX - $m1[$k]['x'];
                        $dy = $trY - $m1[$k]['y'];
                        $dSq = $dx * $dx + $dy * $dy;

                        if ($dSq < $spatialTolSq && $dSq < $bestDistSq) {
                            $aDiff = abs($trAngle - $m1[$k]['angle']);
                            if ($aDiff > 180.0) {
                                $aDiff = 360.0 - $aDiff;
                            }

                            if ($aDiff < $angleTol) {
                                $bestDistSq = $dSq;
                                $bestIdx    = $k;
                            }
                        }
                    }

                    if ($bestIdx >= 0) {
                        $matched++;
                        $used[$bestIdx] = true;
                    }
                }

                if ($matched > $bestMatched) {
                    $bestMatched = $matched;
                }
            }
        }

        return $bestMatched / min($n1, $n2);
    }

    /**
     * Select the most reliable anchor minutiae, preferring high quality values.
     */
    private static function selectAnchors(array $minutiae): array
    {
        if (count($minutiae) <= self::MAX_ANCHORS) {
            return $minutiae;
        }

        usort($minutiae, fn ($a, $b) => $b['quality'] <=> $a['quality']);

        return array_slice($minutiae, 0, self::MAX_ANCHORS);
    }
}
