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

namespace Testomat\PHPUnit\ResultPrinter\Traits;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use PHPUnit\Runner\BaseTestRunner;
use PHPUnit\Runner\PhptTestCase;
use PHPUnit\TextUI\DefaultResultPrinter;
use ReflectionException;
use ReflectionObject;
use SebastianBergmann\Timer\Timer;
use Testomat\PHPUnit\ResultPrinter\Config;
use Testomat\PHPUnit\ResultPrinter\Exception\ShouldNotHappenException;
use Testomat\PHPUnit\ResultPrinter\State;
use Testomat\PHPUnit\ResultPrinter\Style;
use Testomat\PHPUnit\ResultPrinter\Terminal\Terminal;
use Testomat\PHPUnit\ResultPrinter\TestResult;
use Throwable;

trait PrinterContentsTrait
{
    /** @var int */
    protected $numAssertions = 0;

    /**
     * Collection of slow tests.
     *
     * @varint
     */
    protected $slow = 0;

    /**
     * Holds an instance of the style.
     *
     * Style is a class we use to interact with output.
     *
     * @var Style
     */
    private $style;

    /**
     * Holds the state of the test
     * suite. The number of tests, etc.
     *
     * @var State
     */
    private $state;

    /**
     * If the test suite has ended before.
     *
     * @var bool
     */
    private $ended = false;

    /**
     * Collection of test errors.
     *
     * @var array<int, string>
     */
    private $errors = [];

    /**
     * Creates a new instance of the listener.
     *
     * @param null|resource|string $out
     * @param int|string           $numberOfColumns
     *
     * @throws ReflectionException
     */
    public function __construct(
        $out = null,
        bool $verbose = false,
        string $colors = DefaultResultPrinter::COLOR_DEFAULT,
        bool $debug = false,
        $numberOfColumns = 80,
        bool $reverse = false
    ) {
        if ((int) (substr(\PHPUnit\Runner\Version::id(), 0, 1)) === 8) {
            parent::__construct($out, $verbose, $colors, $debug, $numberOfColumns, $reverse);
        }

        Timer::start();

        $output = new Terminal();
        $this->style = new Style($output, $colors);
        $this->state = State::from(new /**
  * @internal
  *
  * @small
 * @coversNothing
  */ class() extends TestCase {
        });
    }

    /**
     * {@inheritdoc}
     */
    public function addError(Test $testCase, Throwable $throwable, float $time): void
    {
        $testCase = $this->testCaseFromTest($testCase);

        $this->state->add(TestResult::fromTestCase($testCase, BaseTestRunner::STATUS_FAILURE, $time));

        $this->errors[] = $this->style->writeError($throwable);
    }

    /**
     * {@inheritdoc}
     */
    public function addWarning(Test $testCase, Warning $warning, float $time): void
    {
        $testCase = $this->testCaseFromTest($testCase);

        $this->state->add(TestResult::fromTestCase($testCase, BaseTestRunner::STATUS_WARNING, $time, $warning->getMessage()));
    }

    /**
     * {@inheritdoc}
     */
    public function addFailure(Test $testCase, AssertionFailedError $error, float $time): void
    {
        $testCase = $this->testCaseFromTest($testCase);

        $this->state->add(TestResult::fromTestCase($testCase, BaseTestRunner::STATUS_FAILURE, $time));

        $reflector = new ReflectionObject($error);

        if ($reflector->hasProperty('message')) {
            $message = trim((string) preg_replace("/\r|\n/", ' ', $error->getMessage()));
            $property = $reflector->getProperty('message');
            $property->setAccessible(true);
            $property->setValue($error, $message);
        }

        $this->errors[] = $this->style->writeError($error);
    }

    /**
     * {@inheritdoc}
     */
    public function addIncompleteTest(Test $testCase, Throwable $t, float $time): void
    {
        $testCase = $this->testCaseFromTest($testCase);

        $this->state->add(TestResult::fromTestCase($testCase, BaseTestRunner::STATUS_INCOMPLETE, $time, false, $t->getMessage()));
    }

    /**
     * {@inheritdoc}
     */
    public function addRiskyTest(Test $testCase, Throwable $t, float $time): void
    {
        $testCase = $this->testCaseFromTest($testCase);

        $this->state->add(TestResult::fromTestCase($testCase, BaseTestRunner::STATUS_RISKY, $time, false, $t->getMessage()));
    }

    /**
     * {@inheritdoc}
     */
    public function addSkippedTest(Test $testCase, Throwable $t, float $time): void
    {
        $testCase = $this->testCaseFromTest($testCase);

        $this->state->add(TestResult::fromTestCase($testCase, BaseTestRunner::STATUS_SKIPPED, $time, false, $t->getMessage()));
    }

    /**
     * {@inheritdoc}
     */
    public function startTestSuite(TestSuite $suite): void
    {
        if ($this->state->suiteTotalTests === null) {
            $this->state->suiteTotalTests = $suite->count();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function endTestSuite(TestSuite $suite): void
    {
        if (! $this->ended && $this->state->suiteTotalTests === $this->state->testSuiteTestsCount()) {
            $this->ended = true;

            $this->style->writeCurrentRecap($this->state);

            $this->style->updateFooter($this->state);
            $this->style->writeRecap($this->numAssertions, $this->errors, $this->slow);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function startTest(Test $testCase): void
    {
        $testCase = $this->testCaseFromTest($testCase);

        // Let's check first if the testCase is over.
        if ($this->state->testCaseHasChanged($testCase)) {
            $this->style->writeCurrentRecap($this->state);

            $this->state->moveTo($testCase);
        }

        $this->style->updateFooter($this->state, $testCase);
    }

    /**
     * {@inheritdoc}
     */
    public function endTest(Test $testCase, float $time): void
    {
        $testCase = $this->testCaseFromTest($testCase);

        if (! $this->state->existsInTestCase($testCase)) {
            $isSlow = false;

            if ((int) round($time * 1000) >= $this->getSlowThreshold($testCase)) {
                $this->slow++;
                $isSlow = true;
            }

            if ($testCase instanceof TestCase) {
                $this->numAssertions += $testCase->getNumAssertions();
            } elseif ($testCase instanceof PhptTestCase) {
                $this->numAssertions++;
            }

            $this->state->add(TestResult::fromTestCase($testCase, BaseTestRunner::STATUS_PASSED, $time, $isSlow));
        }
    }

    /**
     * Intentionally left blank as we output things on events of the listener.
     */
    public function write(string $content): void
    {
        // ..
    }

    /**
     * Calculate slow test threshold for given test. A TestCase may override the
     * suite-wide slowness threshold by using the annotation {@slowThreshold}
     * with a threshold value in milliseconds.
     *
     * For example, the following test would be considered slow if its execution
     * time meets or exceeds 5000ms (5 seconds):
     *
     * <code>
     * \@slowThreshold 5000
     * public function testLongRunningProcess() {}
     * </code>
     */
    protected function getSlowThreshold(TestCase $test): int
    {
        $ann = $test->getAnnotations();

        return isset($ann['method']['slowThreshold'][0]) ? (int) $ann['method']['slowThreshold'][0] : Config::getSlowThreshold();
    }

    /**
     * Returns a test case from the given test.
     *
     * Note: This printer is do not work with normal Test classes - only
     * with Test Case classes. Please report an issue if you think
     * this should work any other way.
     */
    private function testCaseFromTest(Test $test): TestCase
    {
        if (! $test instanceof TestCase) {
            throw new ShouldNotHappenException();
        }

        return $test;
    }
}
