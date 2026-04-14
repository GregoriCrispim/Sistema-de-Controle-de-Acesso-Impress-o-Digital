<?php

namespace App\Services;

/**
 * Generates synthetic but structurally valid ISO 19794-2 fingerprint templates.
 *
 * These templates have correct headers, realistic minutiae counts, and proper
 * binary formatting so they can be parsed and matched by FingerprintMatcher.
 *
 * Used for seeding test data and automated tests. Real production templates
 * come from the Suprema BioMini scanner hardware.
 */
class FingerprintTemplateGenerator
{
    /**
     * Generate a valid ISO 19794-2 template hex string from a deterministic seed.
     * Same seed always produces the same template.
     */
    public static function generate(string $seed, int $numMinutiae = 30): string
    {
        $numMinutiae = max(8, min(60, $numMinutiae));
        $rng = self::seededRng($seed);

        $width = 256;
        $height = 360;
        $resX = 197;
        $resY = 197;

        $minutiaeBytes = '';
        for ($i = 0; $i < $numMinutiae; $i++) {
            $type = $rng() % 3;
            $x = 10 + ($rng() % ($width - 20));
            $y = 10 + ($rng() % ($height - 20));
            $angleRaw = $rng() % 256;
            $quality = 40 + ($rng() % 61);

            $word1 = (($type & 0x03) << 14) | ($x & 0x3FFF);
            $word2 = ($y & 0x3FFF);

            $minutiaeBytes .= pack('nnCC', $word1, $word2, $angleRaw, $quality);
        }

        $viewHeader = pack('CCCC', 0x00, 0x00, 80, $numMinutiae);
        $extendedDataLen = pack('nn', 0, 0);
        $body = $viewHeader . $minutiaeBytes . $extendedDataLen;

        // ISO 19794-2 record header (24 bytes):
        //  0-3:   "FMR\0"      format identifier
        //  4-7:   " 20\0"      version
        //  8-11:  total length (big-endian 32-bit)
        // 12-13:  capture device ID
        // 14-15:  image width
        // 16-17:  image height
        // 18-19:  X resolution (pixels/cm)
        // 20-21:  Y resolution (pixels/cm)
        // 22:     number of finger views
        // 23:     reserved
        $totalLength = 24 + strlen($body);

        $header = "FMR\x00"
            . " 20\x00"
            . pack('N', $totalLength)
            . pack('n', 0)
            . pack('nn', $width, $height)
            . pack('nn', $resX, $resY)
            . pack('CC', 1, 0);

        return strtoupper(bin2hex($header . $body));
    }

    /**
     * Generate a slightly varied version of the same finger (simulating re-capture).
     * Adds small random offsets to minutiae positions and angles.
     */
    public static function generateVariant(string $seed, int $variantIndex = 1, int $numMinutiae = 30): string
    {
        $numMinutiae = max(8, min(60, $numMinutiae));
        $rng = self::seededRng($seed);
        $vRng = self::seededRng($seed . '_variant_' . $variantIndex);

        $width = 256;
        $height = 360;
        $resX = 197;
        $resY = 197;

        $minutiaeBytes = '';
        $generatedCount = 0;

        for ($i = 0; $i < $numMinutiae; $i++) {
            $type = $rng() % 3;
            $x = 10 + ($rng() % ($width - 20));
            $y = 10 + ($rng() % ($height - 20));
            $angleRaw = $rng() % 256;
            $quality = 40 + ($rng() % 61);

            // 15% chance of missing this minutia (simulates imperfect capture)
            if (($vRng() % 100) < 15) {
                continue;
            }

            // Add small spatial offset (±5 pixels)
            $x = max(0, min(0x3FFF, $x + ($vRng() % 11) - 5));
            $y = max(0, min(0x3FFF, $y + ($vRng() % 11) - 5));

            // Add small angular offset (±3 raw units ≈ ±4.2 degrees)
            $angleRaw = ($angleRaw + ($vRng() % 7) - 3 + 256) % 256;

            $word1 = (($type & 0x03) << 14) | ($x & 0x3FFF);
            $word2 = ($y & 0x3FFF);

            $minutiaeBytes .= pack('nnCC', $word1, $word2, $angleRaw, $quality);
            $generatedCount++;
        }

        if ($generatedCount < 8) {
            return self::generate($seed, $numMinutiae);
        }

        $viewHeader = pack('CCCC', 0x00, 0x00, 75, $generatedCount);
        $extendedDataLen = pack('nn', 0, 0);
        $body = $viewHeader . $minutiaeBytes . $extendedDataLen;

        $totalLength = 24 + strlen($body);

        $header = "FMR\x00"
            . " 20\x00"
            . pack('N', $totalLength)
            . pack('n', 0)
            . pack('nn', $width, $height)
            . pack('nn', $resX, $resY)
            . pack('CC', 1, 0);

        return strtoupper(bin2hex($header . $body));
    }

    /**
     * Create a simple deterministic pseudo-RNG from a string seed.
     */
    private static function seededRng(string $seed): \Closure
    {
        $hash = md5($seed);
        $state = [
            hexdec(substr($hash, 0, 8)),
            hexdec(substr($hash, 8, 8)),
            hexdec(substr($hash, 16, 8)),
            hexdec(substr($hash, 24, 8)),
        ];

        return function () use (&$state): int {
            $state[0] = (($state[0] * 1103515245) + 12345) & 0x7FFFFFFF;
            $state[1] = (($state[1] * 214013) + 2531011) & 0x7FFFFFFF;
            $result = ($state[0] ^ $state[1]) & 0x7FFFFFFF;
            $state[2] = $state[0];
            $state[3] = $state[1];
            return $result;
        };
    }
}
