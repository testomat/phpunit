<?php
//
//declare(strict_types=1);
//
///**
// * Copyright (c) 2020 Daniel Bannert
// *
// * For the full copyright and license information, please view
// * the LICENSE.md file that was distributed with this source code.
// *
// * @see https://github.com/testomat/phpunit
// */
//
//namespace Testomat\PHPUnit\Tests\Fixture;
//
//use PHPUnit\Framework\TestCase;
//
///**
// * @internal
// *
// * @small
// * @coversNothing
// */
//final class DifferentTestCasesTest extends TestCase
//{
//    public function testSuccess(): void
//    {
//        self::assertTrue(true);
//    }
//
//    public function testSkip(): void
//    {
//        self::markTestSkipped('skipped');
//    }
//
//    public function testIncomplete(): void
//    {
//        self::markTestIncomplete('This is a incomplete description');
//    }
//
//    public function testShouldConvertTitleCaseToLowercasedWords(): void
//    {
//        self::assertTrue(true);
//    }
//
//    public function testShouldConvertSnakeCaseToLowercasedWords(): void
//    {
//        self::assertTrue(true);
//    }
//
//    public function test should convert non breaking spaces to lowercased words(): void
//    {
//        self::assertTrue(true);
//    }
//
//    public function testCanContain1Or99Numbers(): void
//    {
//        self::assertTrue(true);
//    }
//
//    public function test123CanStartOrEndWithNumbers456(): void
//    {
//        self::assertTrue(true);
//    }
//
//    public function testShouldPreserveCAPITALIZEDAndPaRTiaLLYCAPitaLIZedWords(): void
//    {
//        self::assertTrue(true);
//    }
//
//    public function testLong(): void
//    {
//        sleep(5);
//
//        self::assertTrue(true);
//    }
//
//    public function testFail(): void
//    {
//        self::assertTrue(false);
//    }
//
//    public function testFailedDiffAssert(): void
//    {
//        self::assertSame(
//            [
//                'null' => null,
//                'true' => true,
//                'false' => false,
//                'int1' => 1,
//                'int0' => 0,
//                'float' => 31.10,
//                'empty' => '',
//                'Foo' => 'bar',
//                'BAR' => 'foo',
//                'foo' => '{Foo}',
//                'baz' => 'foo is {}foo baz',
//                'escape' => '@escapeme',
//                'binary' => "\xf0\xf0\xf0\xf0",
//                'binary-control-char' => "This is a Bell char \x07",
//                'true2' => 'true',
//                'false2' => 'false',
//                'null2' => 'null',
//                'callableName' => 'key',
//            ],
//            [
//                'binary' => "\xf0\xf0\xf0\xf0",
//                'binary-control-char' => "This is a Bell char \x07",
//                'true2' => 'true',
//                'false2' => 'false',
//                'null2' => 'null',
//                'callableName' => 'key',
//                'test' => 'd',
//                'int1' => 1,
//                'int0' => 0,
//                'float' => 31.10,
//                'empty' => '',
//                'Foo' => 'bar',
//                'BAR' => 'foo',
//                'foo' => '{Foo}',
//                'baz' => 'foo is {}foo baz',
//                'escape' => '@escapeme',
//            ]
//        );
//    }
//}
