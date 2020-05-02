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

namespace Testomat\PHPUnit\Tests\Unit\Common;

use PHPUnit\Framework\TestCase;
use Testomat\PHPUnit\Common\Timer;

/**
 * @covers \Testomat\PHPUnit\Common\Timer
 */
class TimerTest extends TestCase
{
    /**
     * @dataProvider provideSecondsToTimeStringCases
     */
    public function testSecondsToTimeString(float $time, string $expected): void
    {
        self::assertSame($expected, Timer::secondsToTimeString($time));
    }

    /**
     * @return iterable<array<int, int|float|string>>
     */
    public static function provideSecondsToTimeStringCases(): iterable
    {
        yield [0.10, '100 ms'];
        yield [10, '10 sec'];
        yield [60, '1 min'];
        yield [100, '1 min, 40 sec'];
    }
}
