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

namespace Testomat\PHPUnit\Common;

use SebastianBergmann\Timer\Timer as SBTimer;

final class Timer
{
    /**
     * This function is changing seconds to sec and minute to min.
     */
    public static function secondsToTimeString(float $time): string
    {
        $timeString = SBTimer::secondsToTimeString($time);

        return str_replace(['minute', 'milliseconds', 'millisecond', 'millisec', 'seconds'], ['min', 'ms', 'ms', 'ms', 'sec'], $timeString);
    }
}
