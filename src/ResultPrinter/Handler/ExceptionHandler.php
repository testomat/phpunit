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

namespace Testomat\PHPUnit\ResultPrinter\Handler;

use AlecRabbit\ConsoleColour\Themes;
use ErrorException;
use PHPUnit\Framework\ExceptionWrapper;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Runner\BaseTestRunner;
use Throwable;

final class ExceptionHandler
{
    /** @var Themes */
    private $terminalColor;

    public function __construct(Themes $terminalColor)
    {
        $this->terminalColor = $terminalColor;
    }

    /**
     * Render an exception to the console.
     */
    public function render(Throwable $throwable): string
    {
        $exceptionMessage = $throwable->getMessage();
        $exceptionName = \get_class($throwable);

        $message = $this->terminalColor->{'colorType' . BaseTestRunner::STATUS_FAILURE}($exceptionName) . \PHP_EOL;
        $message .= $this->terminalColor->whiteBold($exceptionMessage) . \PHP_EOL . \PHP_EOL;

        if ($throwable instanceof ExceptionWrapper && $throwable->getOriginalException() !== null) {
            $throwable = $throwable->getOriginalException();
        }

        $message = $this->renderEditor($throwable, $message);
        $message = $this->renderTrace($throwable, $message);

        if ($throwable instanceof ExpectationFailedException && $comparisionFailure = $throwable->getComparisonFailure()) {
            $message .= \PHP_EOL . $comparisionFailure->getDiff();
        }

        return $message;
    }

    /**
     * Renders the editor containing the code that was the
     * origin of the exception.
     */
    private function renderEditor(Throwable $exception, string $message): string
    {
        $message .= $this->terminalColor->green($exception->getFile()) . ':' . $this->terminalColor->green((string) $exception->getLine()) . \PHP_EOL;

        $range = self::getFileLines(
            $exception->getFile(),
            $exception->getLine() - 5,
            10
        );

        foreach ($range as $k => $code) {
            $line = $k + 1;
            $code = $exception->getLine() === $line ? $this->terminalColor->red($code) : $this->terminalColor->white($code);

            $message .= sprintf('%s %s', $this->terminalColor->white((string) $line . ':'), $code) . \PHP_EOL;
        }

        return $message . \PHP_EOL;
    }

    /**
     * Renders the trace of the exception.
     */
    private function renderTrace(Throwable $exception, string $message): string
    {
        $message .= $this->terminalColor->white('Exception trace:') . \PHP_EOL;
        $count = 0;

        foreach ($this->getFrames($exception) as $i => $frame) {
            $class = isset($frame['class']) ? $frame['class'] . '::' : '';
            $function = $frame['function'] ?? '';

            if ($class !== '' && $function !== '') {
                $message .= $this->terminalColor->white(sprintf(
                    '%s%s%s(%s)',
                    str_pad((string) ((int) $i + 1), 4, ' '),
                    $class,
                    $function,
                    isset($frame['args']) ? self::formatsArgs($frame['args']) : ''
                )) . \PHP_EOL;
            }

            if (isset($frame['file'], $frame['line'])) {
                $message .= sprintf(
                    '    %s:%s',
                    $this->terminalColor->green($frame['file']),
                    $this->terminalColor->green((string) $frame['line'])
                );
            }

            if ($count !== 4) {
                $message .= \PHP_EOL;
            }

            $count++;
        }

        return $message;
    }

    /**
     * Gets the backtrace from an exception.
     *
     * If xdebug is installed
     */
    private function getTrace(Throwable $exception): array
    {
        $traces = $exception->getTrace();

        // Get trace from xdebug if enabled, failure exceptions only trace to the shutdown handler by default
        if (! $exception instanceof ErrorException) {
            return $traces;
        }

        if (! self::isLevelFatal($exception->getSeverity())) {
            return $traces;
        }

        if (! \extension_loaded('xdebug') || ! xdebug_is_enabled()) {
            return [];
        }

        // Use xdebug to get the full stack trace and remove the shutdown handler stack trace
        $stack = array_reverse(xdebug_get_function_stack());
        $trace = debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS);

        return array_diff_key($stack, $trace);
    }

    /**
     * Returns an iterator for the inspected exception's
     * frames.
     */
    private function getFrames(Throwable $exception): array
    {
        $frames = $this->getTrace($exception);

        // Fill empty line/file info for call_user_func_array usages (PHP Bug #44428)
        foreach ($frames as $k => $frame) {
            if (empty($frame['file'])) {
                // Default values when file and line are missing
                $file = '[internal]';
                $line = 0;
                $nextFrame = \count($frames[$k + 1]) !== 0 ? $frames[$k + 1] : [];

                if ($this->isValidNextFrame($nextFrame)) {
                    $file = $nextFrame['file'];
                    $line = $nextFrame['line'];
                }

                $frames[$k]['file'] = $file;
                $frames[$k]['line'] = $line;
            }

            $frames['function'] = $frame['function'] ?? '';
        }

        // Find latest non-error handling frame index ($i) used to remove error handling frames
        $i = 0;

        foreach ($frames as $k => $frame) {
            if (isset($frame['file'], $frame['line'])
                && $frame['file'] === $exception->getFile()
                && $frame['line'] === $exception->getLine()
            ) {
                $i = $k;
            }
        }

        // Remove error handling frames
        if ($i > 0) {
            array_splice($frames, 0, $i);
        }

        array_unshift($frames, $this->getFrameFromException($exception));

        // show the last 5 frames
        return \array_slice($frames, 0, 5);
    }

    /**
     * Given an exception, generates an array in the format
     * generated by Exception::getTrace().
     */
    private function getFrameFromException(Throwable $exception): array
    {
        return [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'class' => \get_class($exception),
            'function' => '__construct',
            'args' => [
                $exception->getMessage(),
            ],
        ];
    }

    /**
     * Determine if the frame can be used to fill in previous frame's missing info
     * happens for call_user_func and call_user_func_array usages (PHP Bug #44428).
     */
    private function isValidNextFrame(array $frame): bool
    {
        if (empty($frame['file']) || empty($frame['line'])) {
            return false;
        }

        if (empty($frame['function']) || stripos($frame['function'], 'call_user_func') === false) {
            return false;
        }

        return true;
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
     * Returns the contents of the file for this frame as an
     * array of lines, and optionally as a clamped range of lines.
     *
     * @return null|string[]
     */
    private static function getFileLines(string $filePath, int $start, int $length): ?array
    {
        if (($contents = self::getFileContents($filePath)) !== null) {
            $lines = explode("\n", $contents);

            if ($start < 0) {
                $start = 0;
            }

            return \array_slice($lines, $start, $length, true);
        }
    }

    /**
     * Returns the full contents of the file for this frame,
     * if it's known.
     */
    private static function getFileContents(string $filePath): ?string
    {
        // Leave the stage early when 'Unknown' is passed
        // this would otherwise raise an exception when
        // open_basedir is enabled.
        if ($filePath === 'Unknown') {
            return null;
        }

        // Return null if the file doesn't actually exist.
        if (! is_file($filePath)) {
            return null;
        }

        return file_get_contents($filePath);
    }
}
