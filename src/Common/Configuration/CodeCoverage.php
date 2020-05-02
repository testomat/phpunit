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

namespace Testomat\PHPUnit\Common\Configuration;

final class CodeCoverage
{
    /** @var bool */
    private $showUncoveredFiles;

    /** @var bool */
    private $showOnlySummary;

    /** @var int */
    private $lowUpperBound;

    /** @var int */
    private $highLowerBound;

    public function __construct(
        bool $showUncoveredFiles,
        bool $showOnlySummary,
        int $lowUpperBound,
        int $highLowerBound
    ) {
        $this->showUncoveredFiles = $showUncoveredFiles;
        $this->showOnlySummary = $showOnlySummary;
        $this->lowUpperBound = $lowUpperBound;
        $this->highLowerBound = $highLowerBound;
    }

    public function isShowUncoveredFiles(): bool
    {
        return $this->showUncoveredFiles;
    }

    public function hasShowUncoveredFiles(): bool
    {
        return $this->showUncoveredFiles !== null;
    }

    public function setShowUncoveredFiles(bool $showUncoveredFiles): void
    {
        $this->showUncoveredFiles = $showUncoveredFiles;
    }

    public function isShowOnlySummary(): bool
    {
        return $this->showOnlySummary;
    }

    public function hasShowOnlySummary(): bool
    {
        return $this->showOnlySummary !== null;
    }

    public function setShowOnlySummary(bool $showOnlySummary): void
    {
        $this->showOnlySummary = $showOnlySummary;
    }

    public function getLowUpperBound(): int
    {
        return $this->lowUpperBound;
    }

    public function setLowUpperBound(int $lowUpperBound): void
    {
        $this->lowUpperBound = $lowUpperBound;
    }

    public function getHighLowerBound(): int
    {
        return $this->highLowerBound;
    }

    public function setHighLowerBound(int $highLowerBound): void
    {
        $this->highLowerBound = $highLowerBound;
    }
}
