<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/testomat/phpunit
 */

namespace Testomat\PHPUnit\ResultPrinter;

final class Config
{
    public static function getSlowThreshold(): int
    {
        $slowThreshold = 500;

        if (filter_var($threshold = getenv('TESTOMAT_PHPUNIT_SPEED_TRAP_THRESHOLD'), \FILTER_VALIDATE_INT)) {
            $slowThreshold = $threshold;
        }

        return $slowThreshold;
    }

    public static function isSpeedTrapActive(): bool
    {
        $enabled = true;

        if (filter_var($e = getenv('TESTOMAT_PHPUNIT_SPEED_TRAP'), \FILTER_VALIDATE_INT)) {
            $enabled = (bool) $e;
        }

        return $enabled;
    }
}
