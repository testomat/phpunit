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

namespace Testomat\PHPUnit\Printer\Contract;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use Testomat\PHPUnit\Printer\State;
use Throwable;

interface Style
{
    /**
     * An error occurred.
     */
    public function addError(State $state, TestCase $test, Throwable $throwable, float $time): void;

    /**
     * A warning occurred.
     */
    public function addWarning(State $state, TestCase $test, Warning $error, float $time): void;

    /**
     * A failure occurred.
     */
    public function addFailure(State $state, TestCase $test, AssertionFailedError $error, float $time): void;

    /**
     * Incomplete test.
     */
    public function addIncompleteTest(State $state, TestCase $test, Throwable $throwable, float $time): void;

    /**
     * Risky test.
     */
    public function addRiskyTest(State $state, TestCase $test, Throwable $throwable, float $time): void;

    /**
     * Skipped test.
     */
    public function addSkippedTest(State $state, TestCase $test, Throwable $throwable, float $time): void;

    /**
     * A test suite started.
     */
    public function startTestSuite(State $state, TestSuite $suite): void;

    /**
     * A test suite ended.
     */
    public function endTestSuite(State $state, TestSuite $suite, bool $ended, int $numAssertions): void;

    /**
     * A test started.
     */
    public function startTest(State $state, TestCase $test): void;

    /**
     * A test ended.
     */
    public function endTest(State $state, TestCase $test, float $time): void;
}
