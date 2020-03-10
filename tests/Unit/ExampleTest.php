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

namespace Testomat\PHPUnit\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Testomat\PHPUnit\Tests\Fixture\ExceptionDummyClass;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class ExampleTest extends TestCase
{
    public function testSuccess(): void
    {
        self::assertTrue(true);
    }

    public function testFail(): void
    {
        self::assertTrue(false);
    }

    public function testError(): void
    {
        (new ExceptionDummyClass())->get();
    }

    public function testSkip(): void
    {
        self::markTestSkipped('skipped');
    }

    public function testIncomplete(): void
    {
        self::markTestIncomplete('incomplete');
    }

    public function testShouldConvertTitleCaseToLowercasedWords(): void
    {
        self::assertTrue(true);
    }

    public function testShouldConvertSnakeCaseToLowercasedWords(): void
    {
        self::assertTrue(true);
    }

    public function test should convert non breaking spaces to lowercased words(): void
    {
        self::assertTrue(true);
    }

    public function testCanContain1Or99Numbers(): void
    {
        self::assertTrue(true);
    }

    public function test123CanStartOrEndWithNumbers456(): void
    {
        self::assertTrue(true);
    }

    public function testShouldPreserveCAPITALIZEDAndPaRTiaLLYCAPitaLIZedWords(): void
    {
        self::assertTrue(true);
    }

    public function testLong(): void
    {
        sleep(10);

        self::assertTrue(true);
    }
}
