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
use Testomat\PHPUnit\Printer\Contract\TestResult as TestResultContract;
use Testomat\PHPUnit\Printer\State;
use Testomat\PHPUnit\Printer\TestResult\Content;

final class Compact extends AbstractStyle
{
    /**
     * {@inheritdoc}
     */
    public function endTestSuite(State $state, TestSuite $suite, bool $ended, int $numAssertions): void
    {
        if ($ended) {
            $this->writeCurrentRecap($state);

            $tests = $this->calculateTests($state);

            if (\count($tests) !== 0) {
                $this->output->writeln([
                    '',
                    '',
                    $this->colour->format(\Safe\sprintf('<fg=default;effects=bold>Tests:           </>%s', implode(', ', $tests))),
                ]);
            }

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
    }

    /**
     * {@inheritdoc}
     */
    public function writeCurrentRecap(State $state): void
    {
        if (! $state->testCaseTestsCount()) {
            return;
        }

        $state->eachTestCaseTests(function (TestResultContract $testResult): void {
            echo $this->testLineFrom($testResult->type, $testResult->icon);
        });
    }

    /**
     * Returns the test contents.
     */
    private function testLineFrom(int $theme, string $icon): string
    {
        return $this->colour->format(\Safe\sprintf('<%s>%s</> ', Content::MAPPER[$theme], $icon));
    }
}
