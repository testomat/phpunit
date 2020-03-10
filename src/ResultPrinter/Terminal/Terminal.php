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

namespace Testomat\PHPUnit\ResultPrinter\Terminal;

use SebastianBergmann\Environment\Console;
use Testomat\PHPUnit\ResultPrinter\Terminal\Output\StreamOutput;

final class Terminal extends StreamOutput
{
    /** @var array<TerminalSection> */
    private $consoleSectionOutputs = [];

    /** @var bool */
    private $hasColorSupport;

    /** @var int */
    private $columns;

    public function __construct()
    {
        $this->stream = $this->openOutputStream();

        $console = new Console();

        $this->hasColorSupport = $console->hasColorSupport();
        $this->columns = $console->getNumberOfColumns();
    }

    public function hasColorSupport(): bool
    {
        return $this->hasColorSupport;
    }

    public function getColumns(): int
    {
        return $this->columns;
    }

    public function section(): TerminalSection
    {
        return new TerminalSection($this->consoleSectionOutputs, $this->getStream(), $this->hasColorSupport(), $this->getColumns());
    }
}
