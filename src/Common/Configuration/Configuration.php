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

final class Configuration
{
    /** @var string */
    public const TYPE_COMPACT = 'compact';

    /** @var string */
    public const TYPE_EXPANDED = 'expanded';

    /** @var string */
    public const SHOW_ERROR_ON_TEST = 'test';

    /** @var string */
    public const SHOW_ERROR_ON_END = 'end';

    /** @var string */
    private $filename;

    /**
     * @var array<int, array>
     * @psalm-var array<int,array<int,string>>
     */
    private $validationErrors;

    /** @var string */
    private $type;

    /** @var bool */
    private $isUtf8;

    /** @var string */
    private $showErrorOn;

    /** @var bool */
    private $speedTrapActive;

    /** @var int */
    private $speedTrapSlowThreshold;

    /** @var int */
    private $speedTrapReportLength;

    /** @var bool */
    private $overAssertiveActive;

    /** @var int */
    private $overAssertiveAlertThreshold;

    /** @var int */
    private $overAssertiveReportLength;

    /** @var \Testomat\PHPUnit\Common\Configuration\Collection */
    private $ignoredFiles;

    /**
     * @psalm-param array<int, array<int, string>> $validationErrors
     * @psalm-param array<int, string> $ignoredFiles
     */
    public function __construct(
        string $filename,
        array $validationErrors,
        string $type,
        bool $isUtf8,
        string $showErrorOn,
        Collection $ignoredFiles,
        bool $speedTrapActive,
        int $speedTrapSlowThreshold,
        int $speedTrapReportLength,
        bool $overAssertiveActive,
        int $overAssertiveAlertThreshold,
        int $overAssertiveReportLength
    ) {
        $this->filename = $filename;
        $this->validationErrors = $validationErrors;
        $this->type = $type;
        $this->isUtf8 = $isUtf8;
        $this->showErrorOn = $showErrorOn;
        $this->ignoredFiles = $ignoredFiles;

        $this->speedTrapActive = $speedTrapActive;
        $this->speedTrapSlowThreshold = $speedTrapSlowThreshold;
        $this->speedTrapReportLength = $speedTrapReportLength;

        $this->overAssertiveActive = $overAssertiveActive;
        $this->overAssertiveAlertThreshold = $overAssertiveAlertThreshold;
        $this->overAssertiveReportLength = $overAssertiveReportLength;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    public function hasValidationErrors(): bool
    {
        return \count($this->validationErrors) > 0;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isUtf8(): bool
    {
        return $this->isUtf8;
    }

    public function showErrorOn(): string
    {
        return $this->showErrorOn;
    }

    public function isSpeedTrapActive(): bool
    {
        return $this->speedTrapActive;
    }

    public function getSpeedTrapSlowThreshold(): int
    {
        return $this->speedTrapSlowThreshold;
    }

    public function getSpeedTrapReportLength(): int
    {
        return $this->speedTrapReportLength;
    }

    public function isOverAssertiveActive(): bool
    {
        return $this->overAssertiveActive;
    }

    public function getOverAssertiveAlertThreshold(): int
    {
        return $this->overAssertiveAlertThreshold;
    }

    public function getOverAssertiveReportLength(): int
    {
        return $this->overAssertiveReportLength;
    }

    public function getIgnoredFiles(): Collection
    {
        return $this->ignoredFiles;
    }
}
