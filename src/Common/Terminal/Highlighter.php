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

use Testomat\TerminalColour\Contract\WrappableFormatter as WrappableFormatterContract;
use Testomat\TerminalColour\Style;

final class Highlighter
{
    public const TOKEN_DEFAULT = 'token_default';
    public const TOKEN_COMMENT = 'token_comment';
    public const TOKEN_STRING = 'token_string';
    public const TOKEN_HTML = 'token_html';
    public const TOKEN_KEYWORD = 'token_keyword';

    public const ACTUAL_LINE_MARK = 'actual_line_mark';
    public const LINE_NUMBER = 'line_number';
    public const LINE_NUMBER_DIVIDER = 'line_divider';
    public const MARKED_LINE_NUMBER = 'marked_line';
    private const WIDTH = 3;

    private static $arrowSymbol = '▶';

    private static $delimiter = '│';

    /** @var array */
    private static $defaultTheme = [
        self::TOKEN_STRING => ['light_grey'],
        self::TOKEN_COMMENT => ['dark_grey', null, ['italic']],
        self::TOKEN_KEYWORD => ['magenta', null, ['bold']],
        self::TOKEN_DEFAULT => ['default', null, ['bold']],
        self::TOKEN_HTML => ['blue', null, ['bold']],

        self::ACTUAL_LINE_MARK => ['red', null, ['bold']],
        self::LINE_NUMBER => ['dark_grey'],
        self::MARKED_LINE_NUMBER => ['red', null, ['bold']],
        self::LINE_NUMBER_DIVIDER => ['dark_grey'],
    ];

    /** @var \Testomat\TerminalColour\Contract\WrappableFormatter */
    private $colour;

    /**
     * @param null|array<int, array<int, array<int, string>|string>> $theme
     */
    public function __construct(WrappableFormatterContract $colour, bool $isUtf8 = false, ?array $theme = null)
    {
        $this->colour = $colour;

        if ($theme === null) {
            $theme = self::$defaultTheme;
        }

        foreach ($theme as $name => $style) {
            if (\is_array($style)) {
                $style = new Style(...$style);
            }

            $this->colour->setStyle($name, $style);
        }

        if ($isUtf8) {
            self::$arrowSymbol = '➜';
            self::$delimiter = '▕';
        }
    }

    public function render(string $source, int $lineNumber, int $linesBefore = 2, int $linesAfter = 2): string
    {
        $source = str_replace(["\r\n", "\r"], "\n", $source);

        $tokenLines = $this->splitToLines($this->tokenize($source));
        $offset = max($lineNumber - $linesBefore - 1, 0);
        $length = $linesAfter + $linesBefore + 1;
        $tokenLines = \array_slice($tokenLines, $offset, $length, true);

        return $this->lineNumbers($this->colorLines($tokenLines), $lineNumber);
    }

    /**
     * @return array<int, string>
     */
    private function tokenize(string $source): array
    {
        $tokens = token_get_all($source);

        $output = [];
        $currentType = null;
        $buffer = '';

        foreach ($tokens as $token) {
            if (\is_array($token)) {
                switch ($token[0]) {
                    case \T_WHITESPACE:
                        break;
                    case \T_OPEN_TAG:
                    case \T_OPEN_TAG_WITH_ECHO:
                    case \T_CLOSE_TAG:
                    case \T_STRING:
                    case \T_VARIABLE:
                        // Constants
                    case \T_DIR:
                    case \T_FILE:
                    case \T_METHOD_C:
                    case \T_DNUMBER:
                    case \T_LNUMBER:
                    case \T_NS_C:
                    case \T_LINE:
                    case \T_CLASS_C:
                    case \T_FUNC_C:
                    case \T_TRAIT_C:
                        $newType = self::TOKEN_DEFAULT;

                        break;
                    case \T_COMMENT:
                    case \T_DOC_COMMENT:
                        $newType = self::TOKEN_COMMENT;

                        break;
                    case \T_ENCAPSED_AND_WHITESPACE:
                    case \T_CONSTANT_ENCAPSED_STRING:
                        $newType = self::TOKEN_STRING;

                        break;
                    case \T_INLINE_HTML:
                        $newType = self::TOKEN_HTML;

                        break;

                    default:
                        $newType = self::TOKEN_KEYWORD;
                }
            } else {
                $newType = $token === '"' ? self::TOKEN_STRING : self::TOKEN_KEYWORD;
            }

            if ($currentType === null) {
                $currentType = $newType;
            }

            if ($currentType !== $newType) {
                $output[] = [$currentType, $buffer];
                $buffer = '';
                $currentType = $newType;
            }

            $buffer .= \is_array($token) ? $token[1] : $token;
        }

        if (isset($newType)) {
            $output[] = [$newType, $buffer];
        }

        return $output;
    }

    /**
     * @param array<int, string> $tokens
     *
     * @return array<int, string>
     */
    private function splitToLines(array $tokens): array
    {
        $lines = [];
        $line = [];

        foreach ($tokens as $token) {
            foreach (explode("\n", $token[1]) as $count => $tokenLine) {
                if ($count > 0) {
                    $lines[] = $line;
                    $line = [];
                }

                if ($tokenLine === '') {
                    continue;
                }

                $line[] = [$token[0], $tokenLine];
            }
        }

        $lines[] = $line;

        return $lines;
    }

    private function colorLines(array $tokenLines): array
    {
        $lines = [];

        foreach ($tokenLines as $lineCount => $tokenLine) {
            $line = '';

            foreach ($tokenLine as $token) {
                [$tokenType, $tokenValue] = $token;

                if ($this->colour->hasStyle($tokenType)) {
                    $line .= $this->colour->format(\Safe\sprintf('<%s>%s</>', $tokenType, $tokenValue));
                } else {
                    $line .= $tokenValue;
                }
            }

            $lines[$lineCount] = $line;
        }

        return $lines;
    }

    private function lineNumbers(array $lines, ?int $markLine = null): string
    {
        end($lines);

        $lineStrlen = \strlen((string) (key($lines) + 1));
        $lineStrlen = $lineStrlen < self::WIDTH ? self::WIDTH : $lineStrlen;
        $snippet = '';
        $mark = '  ' . self::$arrowSymbol . ' ';

        foreach ($lines as $i => $line) {
            $coloredLineNumber = $this->coloredLineNumber(self::LINE_NUMBER, $i, $lineStrlen);

            if ($markLine !== null) {
                $code = '    ';

                if ($markLine === $i + 1) {
                    if ($this->colour->hasStyle(self::ACTUAL_LINE_MARK)) {
                        $code = $this->colour->format(\Safe\sprintf('<%s>%s</>', self::ACTUAL_LINE_MARK, $mark));
                    }

                    $coloredLineNumber = $this->coloredLineNumber(self::MARKED_LINE_NUMBER, $i, $lineStrlen);
                }

                $snippet .= $code;
            }

            $snippet .= $coloredLineNumber;

            $delimiter = self::$delimiter . ' ';
            $delimiterColor = $markLine === $i + 1 ? self::ACTUAL_LINE_MARK : self::LINE_NUMBER_DIVIDER;

            if ($this->colour->hasStyle($delimiterColor)) {
                $snippet .= $this->colour->format(\Safe\sprintf('<%s>%s</>', $delimiterColor, $delimiter));
            } else {
                $snippet .= $delimiter;
            }

            $snippet .= $line . \PHP_EOL;
        }

        return $snippet;
    }

    private function coloredLineNumber(string $style, int $i, int $lineStrlen): string
    {
        $code = str_pad((string) ($i + 1), $lineStrlen, ' ', \STR_PAD_LEFT);

        if (! $this->colour->hasStyle($style)) {
            return $code;
        }

        return $this->colour->format(\Safe\sprintf('<%s>%s</>', $style, $code));
    }
}
