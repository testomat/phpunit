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

namespace Testomat\PHPUnit\Printer\Traits;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use PHPUnit\Runner\BaseTestRunner;
use PHPUnit\Runner\PhptTestCase;
use PHPUnit\Runner\Version;
use SebastianBergmann\Timer\Timer;
use Testomat\PHPUnit\Common\Configuration\Configuration;
use Testomat\PHPUnit\Common\Configuration\PHPUnitConfiguration;
use Testomat\PHPUnit\Common\Contract\Exception\InvalidArgumentException;
use Testomat\PHPUnit\Common\Contract\Exception\ShouldNotHappenException;
use Testomat\PHPUnit\Common\Terminal\Terminal;
use Testomat\PHPUnit\Common\Util;
use Testomat\PHPUnit\Printer\State;
use Testomat\PHPUnit\Printer\Style\Compact;
use Testomat\PHPUnit\Printer\Style\Expanded;
use Testomat\PHPUnit\Printer\TestResult\Content;
use Testomat\TerminalColour\Util as TerminalColourUtil;
use Throwable;

trait PrinterContentsTrait
{
    /** @var int */
    protected $numAssertions = 0;

    /**
     * Collection of over assertive tests.
     *
     * @var array<int, string>
     */
    protected $assertive = [];

    /**
     * Collection of slow tests.
     *
     * @var array<int, string>
     */
    protected $slow = [];

    /**
     * Holds an instance of the style.
     *
     * Expanded is a class we use to interact with output.
     *
     * @var \Testomat\PHPUnit\Printer\Contract\Style
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

    /** @var Configuration */
    private $configuration;

    /**
     * Creates a new instance of the listener.
     *
     * @param null|resource|string $out
     * @param int|string           $numberOfColumns
     */
    public function __construct(
        $out = null,
        bool $verbose = false,
        string $colors = 'never',
        bool $debug = false,
        $numberOfColumns = 80,
        bool $reverse = false
    ) {
        $this->validateVariables($colors, $numberOfColumns);

        if ((int) (substr(Version::id(), 0, 1)) === 8) {
            parent::__construct($out, $verbose, $colors, $debug, $numberOfColumns, $reverse);
        }

        $phpunitConfiguration = new PHPUnitConfiguration(Util::getPHPUnitConfiguration());
        $this->configuration = Util::getTestomatConfiguration();

        $output = new Terminal();

        $maxNumberOfColumns = TerminalColourUtil::getNumberOfColumns();

        if ($numberOfColumns === 'max' || ($numberOfColumns !== 80 && $numberOfColumns > $maxNumberOfColumns)) {
            $numberOfColumns = $maxNumberOfColumns;
        }

        if ($this->configuration->getType() === Configuration::TYPE_COMPACT) {
            $this->style = new Compact($output, $colors, $numberOfColumns, $verbose, $this->configuration, $phpunitConfiguration);
        } else {
            $this->style = new Expanded($output, $colors, $numberOfColumns, $verbose, $this->configuration, $phpunitConfiguration);
        }

        $this->state = State::from(new /**
         * @internal
         *
         * @small
         * @coversNothing
         */ class() extends TestCase {
        });

        Timer::start();

        $this->style->writePHPUnitHeader();
    }

    /**
     * {@inheritdoc}
     */
    public function addError(Test $testCase, Throwable $throwable, float $time): void
    {
        $testCase = $this->testCaseFromTest($testCase);

        $this->state->add(
            Content::fromTestCase($this->state->testCaseName, $testCase, BaseTestRunner::STATUS_FAILURE, $time)
                ->setFailureContent($this->style->renderError($throwable))
        );

        $this->style->addError($this->state, $testCase, $throwable, $time);
    }

    /**
     * {@inheritdoc}
     */
    public function addWarning(Test $testCase, Warning $warning, float $time): void
    {
        $testCase = $this->testCaseFromTest($testCase);

        $this->state->add(
            Content::fromTestCase(
                $this->state->testCaseName,
                $testCase,
                BaseTestRunner::STATUS_WARNING,
                $time,
                $warning->toString()
            )
        );

        $this->style->addWarning($this->state, $testCase, $warning, $time);
    }

    /**
     * {@inheritdoc}
     */
    public function addFailure(Test $testCase, AssertionFailedError $error, float $time): void
    {
        $testCase = $this->testCaseFromTest($testCase);

        $this->state->add(
            Content::fromTestCase($this->state->testCaseName, $testCase, BaseTestRunner::STATUS_FAILURE, $time)
                ->setFailureContent($this->style->renderFailure($error))
        );

        $this->style->addFailure($this->state, $testCase, $error, $time);
    }

    /**
     * {@inheritdoc}
     */
    public function addIncompleteTest(Test $testCase, Throwable $throwable, float $time): void
    {
        $testCase = $this->testCaseFromTest($testCase);

        $this->state->add(
            Content::fromTestCase(
                $this->state->testCaseName,
                $testCase,
                BaseTestRunner::STATUS_INCOMPLETE,
                $time,
                $throwable->getMessage()
            )
        );

        $this->style->addIncompleteTest($this->state, $testCase, $throwable, $time);
    }

    /**
     * {@inheritdoc}
     */
    public function addRiskyTest(Test $testCase, Throwable $throwable, float $time): void
    {
        $testCase = $this->testCaseFromTest($testCase);

        $this->state->add(
            Content::fromTestCase(
                $this->state->testCaseName,
                $testCase,
                BaseTestRunner::STATUS_RISKY,
                $time,
                $throwable->getMessage()
            )
        );

        $this->style->addRiskyTest($this->state, $testCase, $throwable, $time);
    }

    /**
     * {@inheritdoc}
     */
    public function addSkippedTest(Test $testCase, Throwable $throwable, float $time): void
    {
        $testCase = $this->testCaseFromTest($testCase);

        $this->state->add(
            Content::fromTestCase(
                $this->state->testCaseName,
                $testCase,
                BaseTestRunner::STATUS_SKIPPED,
                $time,
                $throwable->getMessage()
            )
        );

        $this->style->addSkippedTest($this->state, $testCase, $throwable, $time);
    }

    /**
     * {@inheritdoc}
     */
    public function startTestSuite(TestSuite $suite): void
    {
        if ($this->state->suiteTotalTests === null) {
            $this->state->suiteTotalTests = $suite->count();
        }

        $this->style->startTestSuite($this->state, $suite);
    }

    /**
     * {@inheritdoc}
     */
    public function endTestSuite(TestSuite $suite): void
    {
        $isEnded = ! $this->ended && $this->state->suiteTotalTests === $this->state->testSuiteTestsCount();

        if ($isEnded) {
            $this->ended = $isEnded;
        }

        $this->style->endTestSuite($this->state, $suite, $isEnded, $this->numAssertions);
    }

    /**
     * {@inheritdoc}
     */
    public function startTest(Test $testCase): void
    {
        $this->style->startTest($this->state, $this->testCaseFromTest($testCase));
    }

    /**
     * {@inheritdoc}
     */
    public function endTest(Test $testCase, float $time): void
    {
        $testCase = $this->testCaseFromTest($testCase);

        if (! $this->state->existsInTestCase($testCase)) {
            $isSlow = false;
            $speedTrapThreshold = 0;

            if ($this->configuration->isSpeedTrapActive()) {
                $speedTrapThreshold = $this->getSlowThreshold($testCase);

                if ((int) round($time * 1000) >= $speedTrapThreshold) {
                    $isSlow = true;
                }
            }

            $isOverAssertive = false;
            $overAssertiveThreshold = 0;
            $numAssertions = $testCase->getNumAssertions();

            if ($this->configuration->isOverAssertiveActive()) {
                $overAssertiveThreshold = $this->getAssertionThreshold($testCase);

                if ($numAssertions > $overAssertiveThreshold) {
                    $isOverAssertive = true;
                }
            }

            $this->state->add(
                Content::fromTestCase($this->state->testCaseName, $testCase, BaseTestRunner::STATUS_PASSED, $time)
                    ->setNumAssertions($numAssertions)
                    ->setSpeedTrap($isSlow, $speedTrapThreshold)
                    ->setOverAssertive($isOverAssertive, $overAssertiveThreshold)
            );

            if ($testCase instanceof TestCase) {
                $this->numAssertions += $numAssertions;
            } elseif ($testCase instanceof PhptTestCase) {
                $this->numAssertions++;
            }
        }

        $this->style->endTest($this->state, $testCase, $time);
    }

    /**
     * {@inheritdoc}
     */
    public function printResult(TestResult $result): void
    {
        $this->style->writeEmptyTestMessage($this->state);
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

    /**
     * Calculate assertion test threshold for given test. A TestCase may override the
     * suite-wide assertion threshold by using the annotation {@assertionThreshold}
     * with a threshold value as int.
     *
     * For example, the following test would be considered a over assertive if more than
     * 3 assertions are used:
     *
     * <code>
     * \@assertionThreshold 3
     * public function testLotOfAssertions() {}
     * </code>
     */
    private function getAssertionThreshold(TestCase $test): int
    {
        $annotations = $test->getAnnotations();

        if (isset($annotations['method']['assertionThreshold'][0])) {
            return $annotations['method']['assertionThreshold'][0];
        }

        if (isset($annotations['class']['assertionThreshold'][0])) {
            return $annotations['class']['assertionThreshold'][0];
        }

        // No matching thresholds, use the default
        return $this->configuration->getOverAssertiveAlertThreshold();
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
    private function getSlowThreshold(TestCase $test): int
    {
        $annotations = $test->getAnnotations();

        if (isset($annotations['method']['slowThreshold'][0])) {
            return $annotations['method']['slowThreshold'][0];
        }

        if (isset($annotations['class']['slowThreshold'][0])) {
            return $annotations['class']['slowThreshold'][0];
        }

        // No matching thresholds, use the default
        return $this->configuration->getSpeedTrapSlowThreshold();
    }

    /**
     * @param mixed $numberOfColumns
     */
    private function validateVariables(string $colors, $numberOfColumns): void
    {
        if (! \in_array($colors, $availableColors = ['never', 'auto', 'always'], true)) {
            throw InvalidArgumentException::create(
                3,
                vsprintf('value from [%s], [%s] or [%s]', $availableColors)
            );
        }

        if (! \is_int($numberOfColumns) && $numberOfColumns !== 'max') {
            throw InvalidArgumentException::create(5, '[integer] or [max]');
        }
    }
}
