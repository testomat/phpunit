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

namespace Testomat\PHPUnit\Printer;

use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\BaseTestRunner;
use Testomat\PHPUnit\Common\Contract\Exception\RuntimeException;
use Testomat\PHPUnit\Common\Contract\PHPUnit\PrettyTestCaseName as PrettyTestCaseNameContract;

/**
 * @internal
 */
final class State
{
    /**
     * The complete test suite number of tests.
     *
     * @var null|int
     */
    public $suiteTotalTests;

    /**
     * The complete test suite tests.
     *
     * @var array<int, \Testomat\PHPUnit\Printer\TestResult>
     */
    public $suiteTests = [];

    /**
     * The current test case name.
     *
     * @var string
     */
    public $testCaseName;

    /**
     * The current test case tests.
     *
     * @var array<int, \Testomat\PHPUnit\Printer\TestResult>
     */
    public $testCaseTests = [];

    /**
     * The current (test case tests.
     *
     * @var array<int, \Testomat\PHPUnit\Printer\TestResult>
     */
    public $printedCaseTests = [];

    /** @var bool */
    public $headerPrinted = false;

    private function __construct(string $testCaseName)
    {
        $this->testCaseName = $testCaseName;
    }

    /**
     * Creates a new State starting from the given test case.
     */
    public static function from(TestCase $test): self
    {
        return new self(self::getPrintableTestCaseName($test));
    }

    /**
     * Adds the given test to the State.
     */
    public function add(TestResult $test): void
    {
        $this->testCaseTests[] = $test;
        $this->printedCaseTests[] = $test;

        $this->suiteTests[] = $test;
    }

    /**
     * Gets the test case title.
     */
    public function getTestCaseTitle(): string
    {
        foreach ($this->testCaseTests as $test) {
            if ($test->type === BaseTestRunner::STATUS_FAILURE) {
                return 'FAIL';
            }
        }

        foreach ($this->testCaseTests as $test) {
            if ($test->type !== BaseTestRunner::STATUS_PASSED) {
                return 'WARN';
            }
        }

        return 'PASS';
    }

    /**
     * Gets the test case title color.
     */
    public function getTestCaseTitleColor(): int
    {
        foreach ($this->testCaseTests as $test) {
            if ($test->type === BaseTestRunner::STATUS_FAILURE) {
                return BaseTestRunner::STATUS_FAILURE;
            }
        }

        foreach ($this->testCaseTests as $test) {
            if ($test->type !== BaseTestRunner::STATUS_PASSED) {
                return BaseTestRunner::STATUS_WARNING;
            }
        }

        return BaseTestRunner::STATUS_PASSED;
    }

    /**
     * Returns the number of tests on the current test case.
     */
    public function testCaseTestsCount(): int
    {
        return \count($this->testCaseTests);
    }

    /**
     * Returns the number of tests on the complete test suite.
     */
    public function testSuiteTestsCount(): int
    {
        return \count($this->suiteTests);
    }

    /**
     * Checks if the given test case is different from the current one.
     */
    public function testCaseHasChanged(TestCase $testCase): bool
    {
        return self::getPrintableTestCaseName($testCase) !== $this->testCaseName;
    }

    /**
     * Moves the new test case.
     */
    public function moveTo(TestCase $testCase): void
    {
        $this->testCaseName = self::getPrintableTestCaseName($testCase);

        $this->testCaseTests = [];

        $this->headerPrinted = false;
    }

    /**
     * Foreach test in the test case.
     */
    public function eachTestCaseTests(callable $callback): void
    {
        foreach ($this->printedCaseTests as $test) {
            $callback($test);
        }

        $this->printedCaseTests = [];
    }

    public function countTestsInTestSuiteBy(int $type): int
    {
        return \count(array_filter($this->suiteTests, static function (TestResult $testResult) use ($type): bool {
            return $testResult->type === $type;
        }));
    }

    /**
     * Checks if the given test already contains a result.
     */
    public function existsInTestCase(TestCase $test): bool
    {
        foreach ($this->testCaseTests as $testResult) {
            if (TestResult::makeDescription($test) === $testResult->description) {
                return true;
            }
        }

        return false;
    }

    public function getLastTestCase(): ?TestResult
    {
        $testResult = end($this->testCaseTests);

        if ($testResult === false) {
            return null;
        }

        return $testResult;
    }

    /**
     * Returns the printable test case name from the given `TestCase`.
     */
    private static function getPrintableTestCaseName(TestCase $test): string
    {
        if ($test instanceof PrettyTestCaseNameContract) {
            $name = $test->getPrettyName();
        } else {
            $name = \get_class($test);
        }

        return $name;
    }
}
