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
use Testomat\PHPUnit\Common\Contract\Exception\RuntimeException;
use Testomat\PHPUnit\Common\Util;

/**
 * @covers \Testomat\PHPUnit\Common\Util
 */
class UtilTest extends TestCase
{
    /**
     * @dataProvider provideSecondsToTimeStringCases
     */
    public function testGetPreparedTimeString(float $time, string $expected, bool $whitoutMs = false): void
    {
        self::assertSame($expected, Util::getPreparedTimeString($time, $whitoutMs));
    }

    public static function provideSecondsToTimeStringCases(): iterable
    {
        yield [0.10, '  [100 ms]  '];
        yield [10, '  [10 sec] '];
        yield [60, '  [1 min] '];
        yield [100, '  [1 min] ', true];
        yield [100, '  [1 min, 40 sec] '];
    }

    public function testGetPHPUnitTestRunnerArguments(): void
    {
        Util::reset();

        try {
            Util::getPHPUnitTestRunnerArguments();
        } catch (RuntimeException $exception) {
            self::fail($exception->getMessage());
        }

        $this->doesNotPerformAssertions();
    }

    public function testGetPHPUnitConfiguration(): void
    {
        Util::reset();

        try {
            Util::getPHPUnitConfiguration();
        } catch (RuntimeException $exception) {
            self::fail($exception->getMessage());
        }

        $this->doesNotPerformAssertions();
    }

    public function testGetTestomatConfiguration(): void
    {
        Util::reset();

        $configuration = Util::getTestomatConfiguration();

        $testomatFile = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'testomat.xml';

        self::assertSame($testomatFile, $configuration->getFilename());

        $configuration = Util::getTestomatConfiguration();

        self::assertSame($testomatFile, $configuration->getFilename()); // cache
    }
}
