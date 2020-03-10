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

/**
 * @internal
 */
final class TestResult
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
     * @var int
     */
    public $theme;

    /**
     * @readonly
     *
     * @var null|int
     */
    public $time;

    /**
     * @readonly
     *
     * @var null|string
     */
    public $warning;

    /**
     * @readonly
     *
     * @var bool
     */
    public $isSlow;

    private function __construct(
        string $description,
        int $type,
        string $icon,
        int $theme,
        ?float $time = null,
        bool $isSlow = false,
        string $warning = ''
    ) {
        $this->description = $description;
        $this->type = $type;
        $this->icon = $icon;
        $this->theme = $theme;
        $this->time = $time;
        $this->isSlow = $isSlow;
        $this->warning = trim((string) preg_replace("/\r|\n/", ' ', $warning));
    }

    /**
     * Creates a new test from the given test case.
     */
    public static function fromTestCase(
        TestCase $testCase,
        int $type,
        ?float $time = null,
        bool $isSlow = false,
        string $warning = ''
    ): self {
        $description = self::makeDescription($testCase);

        return new self($description, $type, self::makeIcon($type), $type, $time, $isSlow, $warning);
    }

    /**
     * Get the test case description.
     */
    public static function makeDescription(TestCase $testCase): string
    {
        $name = $testCase->getName(true);

        // Convert non-breaking method name to camelCase
        $name = str_replace(' ', '', $name);

        // First, lets replace underscore by spaces.
        $name = str_replace('_', ' ', $name);

        // Then, replace upper cases by spaces.
        $name = (string) preg_replace('/([A-Z])/', ' $1', $name);

        // Finally, if it starts with `test`, we remove it.
        $name = (string) preg_replace('/^test/', '', $name);

        // Removes spaces
        $name = (string) trim($name);

        // Finally, lower case everything
        if (false === $encoding = mb_detect_encoding($name, null, true)) {
            return strtolower($name);
        }

        return (string) mb_strtolower($name, $encoding);
    }

    /**
     * Get the test case icon.
     */
    public static function makeIcon(int $type): string
    {
        switch ($type) {
            case BaseTestRunner::STATUS_FAILURE:
                return '✕';
            case BaseTestRunner::STATUS_SKIPPED:
                return 's';
            case BaseTestRunner::STATUS_RISKY:
                return 'r';
            case BaseTestRunner::STATUS_INCOMPLETE:
                return 'i';
            case BaseTestRunner::STATUS_WARNING:
                return 'w';
            case BaseTestRunner::STATUS_UNKNOWN:
                return 'u';
            case self::RUNS:
                return '•';
            case BaseTestRunner::STATUS_PASSED:
            default:
                return '✓';
        }
    }
}
