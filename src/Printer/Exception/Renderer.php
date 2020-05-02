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

namespace Testomat\PHPUnit\Printer\Exception;

use Narrowspark\ExceptionInspector\Frame;
use Narrowspark\ExceptionInspector\Inspector;
use PHPUnit\Framework\ExceptionWrapper;
use PHPUnit\Framework\ExpectationFailedException;
use Testomat\PHPUnit\Common\Terminal\Highlighter;
use Testomat\TerminalColour\Contract\WrappableFormatter as WrappableFormatterContract;
use Throwable;

final class Renderer
{
    /** @var \Testomat\TerminalColour\Contract\WrappableFormatter */
    private $colour;

    /** @var \Testomat\PHPUnit\Common\Terminal\Highlighter */
    private $highlighter;

    /**
     * Ignores traces where the file string matches one
     * of the provided regex expressions.
     *
     * @var string[]
     */
    private $ignore = [];

    /** @var bool */
    private $hideHeader = false;

    public function __construct(WrappableFormatterContract $colour, Highlighter $highlighter)
    {
        $this->colour = $colour;
        $this->highlighter = $highlighter;
    }

    /**
     * Ignores traces where the file string matches one
     * of the provided regex expressions.
     *
     * @param string[] $ignore the regex expressions
     */
    public function ignoreFilesIn(array $ignore): self
    {
        foreach ($ignore as $key => $value) {
            $ignore[$key] = '/' . str_replace('/', '\/', $value) . '/';
        }

        $this->ignore = $ignore;

        return $this;
    }

    public function disableHeader(): self
    {
        $this->hideHeader = true;

        return $this;
    }

    public function render(Throwable $throwable): string
    {
        $message = '';

        if (! $this->hideHeader) {
            $message .= $this->colour->format(\Safe\sprintf('<fg=default;bg=red> %s </>', \get_class($throwable))) . \PHP_EOL . \PHP_EOL;
        }

        $message .= $this->colour->format(\Safe\sprintf('<fg=default;effects=bold> %s</>', $throwable->getMessage())) . \PHP_EOL . \PHP_EOL;

        if ($throwable instanceof ExceptionWrapper && $throwable->getOriginalException() !== null) {
            $throwable = $throwable->getOriginalException();
        }

        $inspector = new Inspector($throwable);

        $frames = $inspector->getFrames()->filter(function (Frame $frame): bool {
            foreach ($this->ignore as $ignore) {
                if (\Safe\preg_match($ignore, $frame->getFile()) === 1) {
                    return false;
                }
            }

            return true;
        })->getArray();

        $editorFrame = array_shift($frames);

        if ($editorFrame !== null) {
            $message = $this->renderEditor($editorFrame, $message);
        }

        if ($throwable instanceof ExpectationFailedException) {
            if ($comparisionFailure = $throwable->getComparisonFailure()) {
                $message .= $this->formatExceptionMessage($comparisionFailure->getDiff()) . \PHP_EOL . \PHP_EOL;
            } else {
                $message = trim($message) . \PHP_EOL . \PHP_EOL;
            }
        }

        return $this->renderTrace($frames, $message);
    }

    /**
     * Renders the editor containing the code that was the
     * origin of the exception.
     */
    private function renderEditor(Frame $frame, string $message): string
    {
        $file = '';
        $line = 0;

        if ($frame->getFile() !== null) {
            $file = $frame->getFile();
            $line = $frame->getLine();
        }

        $file = self::getFileRelativePath($file);

        $message .= $this->colour->format(\Safe\sprintf(' at <fg=green>%s</>:<fg=green>%s</>', $file, (string) $line) . \PHP_EOL);

        $message .= $this->highlighter->render((string) $frame->getFileContents(), $frame->getLine(), 5, 5);

        return $message . \PHP_EOL;
    }

    /**
     * Renders the trace of the exception.
     *
     * @param array<int, Frame> $frames
     */
    private function renderTrace(array $frames, string $message): string
    {
        $count = 0;
        $vendorFrames = '';
        $userFrames = '';

        foreach ($frames as $i => $frame) {
            $content = '';
            $class = $frame->getClass();

            $content .= $this->colour->format(
                sprintf(
                    '<fg=default> %s%s%s(%s)</>',
                    str_pad((string) ((int) $i + 1), 4, ' '),
                    $class !== null || $class !== '' ? $class . '::' : '',
                    $frame->getFunction() ?? '',
                    self::formatsArgs($frame->getArgs())
                )
            ) . \PHP_EOL;

            $file = $frame->getFile();

            if ($file !== '') {
                $content .= $this->colour->format(sprintf(
                    '    <fg=default>%s</>:<fg=default>%s</>',
                    $file,
                    (string) $frame->getLine()
                ));
            }

            if ($count !== 4) {
                $content .= \PHP_EOL;
            }

            if (strpos($frame->getFile(), '/vendor/') !== false) {
                $vendorFrames .= $content;
            } else {
                $userFrames .= $content;
            }

            $count++;
        }

        if ($vendorFrames !== '' || $userFrames !== '') {
            $message .= $this->colour->format(' <fg=default>Exception trace:</>') . \PHP_EOL . $userFrames . $vendorFrames;
        }

        return trim($message);
    }

    /**
     * Adds color support to the expected message.
     *
     * @throws \Safe\Exceptions\StringsException
     */
    private function formatExceptionMessage(string $exceptionMessage): string
    {
        $exceptionMessage = trim(str_replace(["+++ Actual\n", "--- Expected\n", '@@ @@'], '', $exceptionMessage));

        $exceptionMessage = preg_replace('/^(Exception.*)$/m', $this->colour->format('  <fg=green>$1</>'), $exceptionMessage);
        $exceptionMessage = preg_replace('/(Failed.*)$/m', $this->colour->format('  <fg=red>$1</>'), $exceptionMessage);
        $exceptionMessage = preg_replace('/(\\-+.*)$/m', $this->colour->format('  <fg=green>$1</>'), $exceptionMessage);
        $exceptionMessage = preg_replace('/(\\++.*)$/m', $this->colour->format('  <fg=red>$1</>'), $exceptionMessage);

        return $this->colour->format(\Safe\sprintf(' <fg=default>%s</>', $exceptionMessage));
    }

    /**
     * Format the given function args to a string.
     */
    private static function formatsArgs(array $arguments, bool $recursive = true): string
    {
        $result = [];

        foreach ($arguments as $argument) {
            switch (true) {
                case \is_string($argument):
                    $result[] = '"' . $argument . '"';

                    break;
                case \is_array($argument):
                    $associative = array_keys($argument) !== range(0, \count($argument) - 1);

                    if ($recursive && $associative && \count($argument) <= 5) {
                        $result[] = '[' . self::formatsArgs($argument, false) . ']';
                    }

                    break;
                case \is_object($argument):
                    $class = \get_class($argument);
                    $result[] = "Object({$class})";

                    break;
            }
        }

        return implode(', ', $result);
    }

    /**
     * Returns the relative path of the given file path.
     */
    private static function getFileRelativePath(string $filePath): string
    {
        if (false !== $cwd = getcwd()) {
            return str_replace("{$cwd}/", '', $filePath);
        }

        return $filePath;
    }
}
