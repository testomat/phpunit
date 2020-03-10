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

namespace Testomat\PHPUnit\ResultPrinter;

use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\BaseTestRunner;
use PHPUnit\TextUI\DefaultResultPrinter;
use SebastianBergmann\Timer\Timer;
use Testomat\PHPUnit\ResultPrinter\Handler\ExceptionHandler;
use Testomat\PHPUnit\ResultPrinter\Terminal\Terminal;
use Testomat\PHPUnit\ResultPrinter\Terminal\TerminalSection;
use Testomat\PHPUnit\ResultPrinter\Terminal\Themes;
use Throwable;

final class Style
{
    /** @var Terminal */
    private $output;

    /** @var TerminalSection */
    private $footer;

    /** @var Themes */
    private $terminalColor;

    private static $firstLine = false;

    /** @var ExceptionHandler */
    private $exceptionHandler;

    public function __construct(Terminal $terminal, string $colors)
    {
        $this->output = $terminal;
        $this->footer = $this->output->section();

        if ($colors === DefaultResultPrinter::COLOR_ALWAYS) {
            $enableColor = true;
        } elseif ($colors === DefaultResultPrinter::COLOR_AUTO) {
            $enableColor = $this->output->hasColorSupport();
        } else {
            $enableColor = false;
        }

        $this->terminalColor = new Themes($this->output->getStream(), null, $enableColor);
        $this->exceptionHandler = new ExceptionHandler($this->terminalColor);
    }

    /**
     * Prints the content similar too:.
     *
     * ```
     *    PASS  Unit\ExampleTest
     *    ✓ basic test
     * ```
     */
    public function writeCurrentRecap(State $state): void
    {
        if (! $state->testCaseTestsCount()) {
            return;
        }

        $this->footer->clear();

        if (! self::$firstLine) {
            self::$firstLine = true;

            $this->output->writeln('');
        }

        $break = \PHP_EOL;

        if (self::$firstLine) {
            self::$firstLine = false;

            $break = '';
        }

        $this->output->writeln($break . $this->titleLineFrom(
            $state->getTestCaseTitleColor(),
            $state->getTestCaseTitle(),
            $state->testCaseClass
        ));

        $state->eachTestCaseTests(function (TestResult $testResult): void {
            $this->output->writeln($this->testLineFrom(
                $testResult->theme,
                $testResult->icon,
                $testResult->time,
                $testResult->isSlow,
                $testResult->description,
                $testResult->warning
            ));
        });
    }

    /**
     * Prints the content similar too on the footer. Where
     * we are updating the current test.
     *
     * ```
     *    Runs  Unit\ExampleTest
     *    • basic test
     * ```
     */
    public function updateFooter(State $state, ?TestCase $testCase = null): void
    {
        $runs = [];

        if ($testCase) {
            $runs[] = $this->titleLineFrom(
                TestResult::RUNS,
                'RUNS',
                \get_class($testCase)
            );

            $testResult = TestResult::fromTestCase($testCase, TestResult::RUNS);

            $runs[] = $this->testLineFrom(
                $testResult->theme,
                $testResult->icon,
                $testResult->time,
                $testResult->isSlow,
                $testResult->description
            );
        }

        $types = [BaseTestRunner::STATUS_FAILURE, BaseTestRunner::STATUS_WARNING, BaseTestRunner::STATUS_RISKY, BaseTestRunner::STATUS_INCOMPLETE, BaseTestRunner::STATUS_SKIPPED, BaseTestRunner::STATUS_PASSED];
        $tests = [];

        foreach ($types as $type) {
            if ($countTests = $state->countTestsInTestSuiteBy($type)) {
                $tests[] = $this->terminalColor->{'colorType' . $type}($countTests . ' ' . TestResult::MAPPER[$type]);
            }
        }

        $pending = $state->suiteTotalTests - $state->testSuiteTestsCount();

        if ($pending) {
            $tests[] = $this->terminalColor->white(" {$pending} pending");
        }

        if (\count($tests) !== 0) {
            $this->footer->overwrite(array_merge($runs, [
                '',
                $this->terminalColor->whiteBold('Tests:           ') . $this->terminalColor->green(implode(', ', $tests)),
            ]));
        }
    }

    /**
     * Writes the final recap.
     */
    public function writeRecap(int $assertions, array $errors, int $slow): void
    {
        $this->footer->writeln(
            $this->terminalColor->whiteBold('Assertions made: ') . $this->terminalColor->white((string) $assertions)
        );

        if ($slow !== 0 && Config::isSpeedTrapActive()) {
            $this->footer->writeln(
                $this->terminalColor->whiteBold('Slow tests:      ') . $this->terminalColor->{'colorType' . BaseTestRunner::STATUS_WARNING}((string) $slow)
            );
        }

        $this->footer->writeln(
            $this->terminalColor->whiteBold('Time:            ') . $this->terminalColor->white(Timer::secondsToTimeString(Timer::stop()))
        );
        $this->footer->writeln(
            $this->terminalColor->whiteBold('Memory:          ') . $this->terminalColor->white(Timer::bytesToString(memory_get_peak_usage(true)))
        );

        if (\count($errors) !== 0) {
            $this->footer->writeln('');
            $this->footer->write('Errors: ');

            foreach ($errors as $error) {
                $this->footer->writeln($error . \PHP_EOL);
            }
        }
    }

    /**
     * Prepares the error output.
     */
    public function writeError(Throwable $throwable): string
    {
        return $this->exceptionHandler->render($throwable);
    }

    /**
     * Returns the title contents.
     */
    private function titleLineFrom(int $theme, string $title, string $testCaseClass): string
    {
        $classParts = explode('\\', $testCaseClass);
        // Removes `Tests` part
        array_shift($classParts);
        $highlightedPart = array_pop($classParts);
        $nonHighlightedPart = implode('\\', $classParts);

        $testCaseClass = $this->terminalColor->white("{$nonHighlightedPart}\\") . $this->terminalColor->whiteBold($highlightedPart);

        return $this->terminalColor->{'colorType' . $theme}(' ' . $title . ' ') . ' ' . $testCaseClass;
    }

    /**
     * Returns the test contents.
     */
    private function testLineFrom(
        int $theme,
        string $icon,
        ?float $time,
        bool $isSlow,
        string $description,
        string $warning = ''
    ): string {
        if (! empty($warning)) {
            $warning = sprintf(
                '→ %s',
                $warning
            );
        }

        $message = $this->terminalColor->{'colorType' . $theme}($icon) . ' ';

        if ($time !== null && Config::isSpeedTrapActive()) {
            $timeString = Timer::secondsToTimeString($time);

            $timeString = str_replace('seconds', 'sec', $timeString);
            $timeString = str_replace('minute', 'min', $timeString);

            $space = ' ';

            if (strpos($timeString, 'ms') !== false) {
                $space = '   ';

                if ((int) filter_var($timeString, \FILTER_SANITIZE_NUMBER_INT) >= 10) {
                    $space = '  ';
                }
            } elseif (strpos($timeString, 'sec') !== false) {
                $space = '  ';

                if ((int) filter_var($timeString, \FILTER_SANITIZE_NUMBER_INT) >= 10) {
                    $space = ' ';
                }
            }

            $message .= $this->terminalColor->{$isSlow ? 'colorType' . BaseTestRunner::STATUS_WARNING : 'white'}(sprintf('  [%s]%s', $timeString, $space));
        }

        $message .= $this->terminalColor->white($description);

        return $message . ($warning !== '' ? ' ' . $this->terminalColor->yellow($warning) : '');
    }
}
