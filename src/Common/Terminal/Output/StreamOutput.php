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

namespace Testomat\PHPUnit\Common\Terminal\Output;

use Testomat\PHPUnit\Common\Contract\Exception\ShouldNotHappenException;

abstract class StreamOutput
{
    /** @var resource */
    protected $stream;

    final public function getStream()
    {
        return $this->stream;
    }

    final public function writeln($messages): void
    {
        foreach ((array) $messages as $message) {
            $this->doWrite($message, true);
        }
    }

    final public function write($messages): void
    {
        foreach ((array) $messages as $message) {
            $this->doWrite($message, false);
        }
    }

    /**
     * Returns true if current environment supports writing console output to
     * STDOUT.
     */
    protected function hasStdoutSupport(): bool
    {
        return false === $this->isRunningOS400();
    }

    /**
     * Writes a message to the output.
     */
    protected function doWrite(string $message, bool $newline): void
    {
        if ($newline) {
            $message .= \PHP_EOL;
        }

        if (false === @fwrite($this->stream, $message)) {
            // should never happen; Unable to write output.
            throw new ShouldNotHappenException();
        }

        fflush($this->stream);
    }

    /**
     * @return resource
     */
    protected function openOutputStream()
    {
        if (! $this->hasStdoutSupport()) {
            return fopen('php://output', 'wb');
        }

        return @fopen('php://stdout', 'wb') ?: fopen('php://output', 'wb');
    }

    /**
     * Checks if current executing environment is IBM iSeries (OS400), which
     * doesn't properly convert character-encodings between ASCII to EBCDIC.
     */
    private function isRunningOS400(): bool
    {
        $checks = [
            \function_exists('php_uname') ? php_uname('s') : '',
            getenv('OSTYPE'),
            \PHP_OS,
        ];

        return false !== stripos(implode(';', $checks), 'OS400');
    }
}
