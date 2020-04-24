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

namespace Testomat\PHPUnit\Printer\Style;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\BaseTestRunner;
use Testomat\PHPUnit\Common\Util;
use Testomat\PHPUnit\Printer\Contract\TestResult as TestResultContract;
use Testomat\PHPUnit\Printer\State;
use Testomat\PHPUnit\Printer\TestResult;

final class Expanded extends AbstractStyle
{
    /**
     * {@inheritdoc}
     */
    public function endTestSuite(State $state, TestSuite $suite, bool $ended, int $numAssertions): void
    {
        if ($ended) {
            $this->writeCurrentRecap($state);

            $this->updateFooter($state);

            $this->writeRecap($state, $numAssertions);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function startTest(State $state, TestCase $testCase): void
    {
        // Let's check first if the testCase is over.
        if ($state->testCaseHasChanged($testCase)) {
            $this->writeCurrentRecap($state);

            $state->moveTo($testCase);
        }

        $this->updateFooter($state, $testCase);
    }

    /**
     * {@inheritdoc}
     */
    public function writeCurrentRecap(State $state): void
    {
        if (! $state->testCaseTestsCount()) {
            return;
        }

        $this->footerSection->clear();

        if ($state->headerPrinted === false) {
            $this->contentSection->writeln($this->titleLineFrom(
                $state->getTestCaseTitleColor(),
                $state->getTestCaseTitle(),
                $state->testCaseName
            ));

            $state->headerPrinted = true;
        }

        $state->eachTestCaseTests(function (TestResultContract $testResult): void {
            $this->contentSection->writeln($this->testLineFrom(
                $testResult->type,
                $testResult->icon,
                $testResult->time,
                $testResult->isSlow,
                $testResult->description,
                $testResult->warning
            ));
        });

        $this->contentSection->writeln('');
    }

    /**
     * {@inheritdoc}
     */
    private function updateFooter(State $state, ?TestCase $testCase = null): void
    {
        $runs = [];

        if ($testCase) {
            $runs[] = $this->titleLineFrom(
                TestResult::RUNS,
                'RUNS',
                \get_class($testCase)
            );

            $testResult = TestResult::fromTestCase($state->testCaseName, $testCase, TestResult::RUNS);

            $runs[] = $this->testLineFrom(
                $testResult->type,
                $testResult->icon,
                $testResult->time,
                $testResult->isSlow,
                $testResult->description
            ) . \PHP_EOL;
        }

        $tests = $this->calculateTests($state);

        if (\count($tests) !== 0) {
            $runs[] = $this->colour->format(\Safe\sprintf('<fg=default;effects=bold>Tests:           </>%s', implode(', ', $tests)));

            $this->footerSection->overwrite($runs);
        }
    }

    /**
     * Returns the title contents.
     */
    private function titleLineFrom(int $theme, string $title, string $testCaseName): string
    {
        $isFile = file_exists($testCaseName);

        if (! $isFile) {
            $nameParts = explode('\\', $testCaseName);
        } else {
            $testCaseName = substr($testCaseName, \strlen((string) getcwd()) + 1);
            $nameParts = explode(\DIRECTORY_SEPARATOR, $testCaseName);
        }

        $highlightedPart = array_pop($nameParts);

        if ($isFile) {
            $highlightedPart = substr($highlightedPart, 0, (int) strrpos($highlightedPart, '.'));
        }

        $nonHighlightedPart = implode('\\', $nameParts);

        $testCaseName = $this->colour->format(\Safe\sprintf("<fg=default>{$nonHighlightedPart}\\</> <fg=default;effects=bold>%s</>", $highlightedPart));

        return $this->colour->format(\Safe\sprintf('<%s> %s </> %s', TestResult::MAPPER[$theme], $title, $testCaseName));
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

        $message = ' ' . $this->colour->format(\Safe\sprintf('<%s>%s </>', TestResult::MAPPER[$theme], $icon));

        if ($time !== null) {
            $message .= $this->colour->format(\Safe\sprintf('<%s>%s</>', $isSlow ? TestResult::MAPPER[BaseTestRunner::STATUS_WARNING] : 'fg=white', Util::getPreparedTimeString($time, true)));
        }

        $message .= $this->colour->format(\Safe\sprintf('<fg=default>%s</>', $description));

        return $message . ($warning !== '' ? ' ' . $this->colour->format(\Safe\sprintf('<%s>%s</>', TestResult::MAPPER[BaseTestRunner::STATUS_WARNING], $warning)) : '');
    }
}
