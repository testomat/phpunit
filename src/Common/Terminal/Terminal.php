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

namespace Testomat\PHPUnit\Common\Terminal;

use Testomat\PHPUnit\Common\Terminal\Output\StreamOutput;
use Testomat\TerminalColour\Util;

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

        $this->hasColorSupport = Util::getSupportedColor($this->stream) !== 0;
        $this->columns = Util::getNumberOfColumns();
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
