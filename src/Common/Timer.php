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

        $timeString = str_replace(['milliseconds', 'millisecond', 'millisec', 'seconds'], ['ms', 'ms', 'ms', 'sec'], $timeString);

        return str_replace('minute', 'min', $timeString);
    }
}
