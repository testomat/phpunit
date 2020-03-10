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

namespace Testomat\PHPUnit\Printer\TestResult;

use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\BaseTestRunner;
use Testomat\PHPUnit\Printer\Contract\TestResult as TestResultContract;

/**
 * @internal
 */
final class Content implements TestResultContract
{
    /**
     * @readonly
     *
     * @var int
     */
    public const RUNS = 7;

    /**
     * @readonly
     *
     * @var array<int, string>
     */
    public const MAPPER = [
        BaseTestRunner::STATUS_UNKNOWN => 'unknown',
        BaseTestRunner::STATUS_PASSED => 'passed',
        BaseTestRunner::STATUS_SKIPPED => 'skipped',
        BaseTestRunner::STATUS_INCOMPLETE => 'incomplete',
        BaseTestRunner::STATUS_FAILURE => 'failure',
        BaseTestRunner::STATUS_ERROR => 'error',
        BaseTestRunner::STATUS_RISKY => 'risky',
        BaseTestRunner::STATUS_WARNING => 'warning',
        self::RUNS => 'pending',
    ];

    /**
     * @readonly
     *
     * @var string
     */
    public $class;

    /**
     * @readonly
     *
     * @var string
     */
    public $method;

    /**
     * @readonly
     *
     * @var string
     */
    public $description;

    /**
     * @readonly
     *
     * @var int
     */
    public $type;

    /**
     * @readonly
     *
     * @var string
     */
    public $icon;

    /**
     * @readonly
     *
     * @var null|float
     */
    public $time;

    /**
     * @readonly
     *
     * @var string
     */
    public $warning = '';

    /**
     * @readonly
     *
     * @var bool
     */
    public $isSlow = false;

    /**
     * @readonly
     *
     * @var int
     */
    public $speedTrapThreshold = 0;

    /**
     * @readonly
     *
     * @var bool
     */
    public $isOverAssertive = false;

    /**
     * @readonly
     *
     * @var int
     */
    public $overAssertiveThreshold = 0;

    /**
     * @readonly
     *
     * @var string
     */
    public $failureContent = '';

    /**
     * @readonly
     *
     * @var int
     */
    public $numAssertions;

    private function __construct(
        string $class,
        string $method,
        string $description,
        int $type,
        string $icon,
        ?float $time = null,
        string $warning = ''
    ) {
        $this->class = $class;
        $this->method = $method;
        $this->description = $description;
        $this->type = $type;
        $this->icon = $icon;
        $this->time = $time;
        $this->warning = trim((string) preg_replace("/\r|\n/", ' ', $warning));
    }

    public function setFailureContent(string $content): TestResultContract
    {
        $this->failureContent = $content;

        return $this;
    }

    public function setNumAssertions(int $numAssertions): TestResultContract
    {
        $this->numAssertions = $numAssertions;

        return $this;
    }

    /**
     * Creates a new test from the given test case.
     */
    public static function fromTestCase(
        string $class,
        TestCase $testCase,
        int $type,
        ?float $time = null,
        string $warning = ''
    ): self {
        return new self($class, $testCase->getName(), self::makeDescription($testCase), $type, self::makeIcon($type), $time, $warning);
    }

    public function setSpeedTrap(bool $isSlow, int $threshold): TestResultContract
    {
        $this->isSlow = $isSlow;
        $this->speedTrapThreshold = $threshold;

        return $this;
    }

    public function setOverAssertive(bool $isOverAssertive, int $overAssertiveThreshold): TestResultContract
    {
        $this->isOverAssertive = $isOverAssertive;
        $this->overAssertiveThreshold = $overAssertiveThreshold;

        return $this;
    }

    /**
     * Get the test case description.
     */
    public static function makeDescription(TestCase $testCase): string
    {
        $name = $testCase->getName(false);

        // Convert non-breaking method name to camelCase
        $name = str_replace(' ', '', $name);

        // First, lets replace underscore by spaces.
        $name = str_replace('_', ' ', $name);

        // Then, replace upper cases by spaces.
        $name = (string) preg_replace('/([A-Z])/', ' $1', $name);

        // Finally, if it starts with `test`, we remove it.
        $name = (string) preg_replace('/^test/', '', $name);

        // Removes spaces
        $name = trim($name);

        // Finally, lower case everything
        if (false === $encoding = mb_detect_encoding($name, null, true)) {
            $name = strtolower($name);
        } else {
            $name = mb_strtolower($name, $encoding);
        }

        // Add the dataset name if it has one
        if ($dataName = $testCase->dataName()) {
            if (\is_int($dataName)) {
                $name .= sprintf(' with data set #%d', $dataName);
            } else {
                $name .= sprintf(' with data set "%s"', $dataName);
            }
        }

        return $name;
    }

    /**
     * Get the test case icon.
     */
    public static function makeIcon(int $type): string
    {
        switch ($type) {
            case BaseTestRunner::STATUS_ERROR:
            case BaseTestRunner::STATUS_FAILURE:
                return '✘';
            case BaseTestRunner::STATUS_SKIPPED:
                return '↩';
            case BaseTestRunner::STATUS_RISKY:
                return '☢';
            case BaseTestRunner::STATUS_INCOMPLETE:
                return '∅';
            case BaseTestRunner::STATUS_WARNING:
                return '⚠';
            case BaseTestRunner::STATUS_UNKNOWN:
                return '?';
            case self::RUNS:
                return '•';
            case BaseTestRunner::STATUS_PASSED:
            default:
                return '✓';
        }
    }
}
