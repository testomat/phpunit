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

use PHPUnit\TextUI\Configuration\Configuration as ConfigurationV9;
use PHPUnit\TextUI\Configuration\PHPUnit;
use PHPUnit\Util\Configuration as ConfigurationV8;
use Testomat\PHPUnit\Common\Contract\Exception\InvalidArgumentException;

final class PHPUnitConfiguration
{
    /** @var bool */
    private $isConfigurationV8 = false;

    /** @var array<string, mixed>|\PHPUnit\TextUI\Configuration\PHPUnit */
    private static $phpunitConfigurationCache;

    /** @var \PHPUnit\TextUI\Configuration\Configuration|\PHPUnit\Util\Configuration */
    private $phpunitConfiguration;

    /**
     * @param \PHPUnit\TextUI\Configuration\Configuration|\PHPUnit\Util\Configuration $phpunitConfiguration
     */
    public function __construct($phpunitConfiguration)
    {
        if ($phpunitConfiguration instanceof ConfigurationV8 && $phpunitConfiguration instanceof ConfigurationV9) {
            throw InvalidArgumentException::create(1, 'instance of [\PHPUnit\TextUI\Configuration\Configuration] or [\PHPUnit\Util\Configuration]');
        }

        if ($phpunitConfiguration instanceof ConfigurationV8) {
            $this->isConfigurationV8 = true;
        }

        $this->phpunitConfiguration = $phpunitConfiguration;
    }

    public function isConfigurationV8(): bool
    {
        return $this->isConfigurationV8;
    }

    public function hasValidationErrors(): bool
    {
        return $this->phpunitConfiguration->hasValidationErrors();
    }

    public function getValidationErrors(): array
    {
        if ($this->isConfigurationV8) {
            return $this->phpunitConfiguration->getValidationErrors();
        }

        return $this->phpunitConfiguration->validationErrors();
    }

    public function getFilename(): string
    {
        if ($this->isConfigurationV8) {
            return $this->phpunitConfiguration->getFilename();
        }

        return $this->phpunitConfiguration->filename();
    }

    public function stopOnWarning(): bool
    {
        if ($this->isConfigurationV8) {
            return $this->getPHPUnitConfigurationV8()['stopOnWarning'] ?? false;
        }

        return $this->getPHPUnitConfigurationV9()->stopOnWarning();
    }

    public function stopOnIncomplete(): bool
    {
        if ($this->isConfigurationV8) {
            return $this->getPHPUnitConfigurationV8()['stopOnIncomplete'] ?? false;
        }

        return $this->getPHPUnitConfigurationV9()->stopOnIncomplete();
    }

    public function stopOnRisky(): bool
    {
        if ($this->isConfigurationV8) {
            return $this->getPHPUnitConfigurationV8()['stopOnRisky'] ?? false;
        }

        return $this->getPHPUnitConfigurationV9()->stopOnRisky();
    }

    public function stopOnSkipped(): bool
    {
        if ($this->isConfigurationV8) {
            return $this->getPHPUnitConfigurationV8()['stopOnSkipped'] ?? false;
        }

        return $this->getPHPUnitConfigurationV9()->stopOnSkipped();
    }

    public function stopOnError(): bool
    {
        if ($this->isConfigurationV8) {
            return $this->getPHPUnitConfigurationV8()['stopOnError'] ?? false;
        }

        return $this->getPHPUnitConfigurationV9()->stopOnError();
    }

    public function stopOnFailure(): bool
    {
        if ($this->isConfigurationV8) {
            return $this->getPHPUnitConfigurationV8()['stopOnFailure'] ?? false;
        }

        return $this->getPHPUnitConfigurationV9()->stopOnFailure();
    }

    public function getExecutionOrder(): int
    {
        if ($this->isConfigurationV8) {
            return $this->getPHPUnitConfigurationV8()['executionOrder'];
        }

        return $this->getPHPUnitConfigurationV9()->executionOrder();
    }

    private function getPHPUnitConfigurationV8(): array
    {
        if (self::$phpunitConfigurationCache !== null) {
            return self::$phpunitConfigurationCache;
        }

        return self::$phpunitConfigurationCache = $this->phpunitConfiguration->getPHPUnitConfiguration();
    }

    private function getPHPUnitConfigurationV9(): PHPUnit
    {
        if (self::$phpunitConfigurationCache !== null) {
            return self::$phpunitConfigurationCache;
        }

        return self::$phpunitConfigurationCache = $this->phpunitConfiguration->phpunit();
    }
}
