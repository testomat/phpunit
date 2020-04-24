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

final class TerminalSection extends StreamOutput
{
    /** @var array */
    private $sections;

    /** @var array */
    private $content = [];

    /** @var int */
    private $lines = 0;

    /** @var bool */
    private $hasColorSupport;

    /** @var int */
    private $columns;

    public function __construct(array &$sections, $stream, bool $hasColorSupport, int $columns)
    {
        $this->stream = $stream;

        array_unshift($sections, $this);

        $this->sections = &$sections;

        $this->hasColorSupport = $hasColorSupport;
        $this->columns = $columns;
    }

    public function getContent(): string
    {
        return implode('', $this->content);
    }

    public function hasColorSupport(): bool
    {
        return $this->hasColorSupport;
    }

    /**
     * Clears previous output for this section.
     *
     * @param int $lines Number of lines to clear. If null, then the entire output of this section is cleared
     */
    public function clear(?int $lines = null): void
    {
        if (\count($this->content) === 0) {
            return;
        }

        if ($lines !== null) {
            array_splice($this->content, -($lines * 2)); // Multiply lines by 2 to cater for each new line added between content
        } else {
            $lines = $this->lines;
            $this->content = [];
        }

        $this->lines -= $lines;

        parent::doWrite($this->popStreamContentUntilCurrentSection($lines), false);
    }

    /**
     * Overwrites the previous output with a new message.
     *
     * @param array|string $message
     */
    public function overwrite($message): void
    {
        $this->clear();
        $this->writeln($message);
    }

    /**
     * @internal
     */
    public function addContent(string $input): void
    {
        foreach (explode(\PHP_EOL, $input) as $lineContent) {
            $this->lines += ceil($this->getDisplayLength($lineContent) / $this->columns) ?: 1;
            $this->content[] = $lineContent;
            $this->content[] = \PHP_EOL;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function doWrite(string $message, bool $newline): void
    {
        $erasedContent = $this->popStreamContentUntilCurrentSection();

        $this->addContent($message);

        parent::doWrite($message, true);
        parent::doWrite($erasedContent, false);
    }

    /**
     * At initial stage, cursor is at the end of stream output. This method makes cursor crawl upwards until it hits
     * current section. Then it erases content it crawled through. Optionally, it erases part of current section too.
     */
    private function popStreamContentUntilCurrentSection($numberOfLinesToClearFromCurrentSection = 0): string
    {
        $numberOfLinesToClear = $numberOfLinesToClearFromCurrentSection;
        $erasedContent = [];

        foreach ($this->sections as $section) {
            if ($section === $this) {
                break;
            }

            $numberOfLinesToClear += $section->lines;
            $erasedContent[] = $section->getContent();
        }

        if ($numberOfLinesToClear > 0) {
            // move cursor up n lines
            parent::doWrite(sprintf("\x1b[%dA", $numberOfLinesToClear), false);
            // erase to end of screen
            parent::doWrite("\x1b[0J", false);
        }

        return implode('', array_reverse($erasedContent));
    }

    /**
     * Returns the length of a string, using mb_strwidth if it is available.
     *
     * @return int The length of the string
     */
    private static function strlen(?string $string): int
    {
        if (false === $encoding = mb_detect_encoding($string, null, true)) {
            return \strlen($string);
        }

        return mb_strwidth($string, $encoding);
    }

    private function getDisplayLength(string $text): int
    {
        $text = str_replace("\t", '        ', $text);
        $text = (string) preg_replace("/\033\\[[^m]*m/", '', $text);

        return self::strlen($text);
    }
}
