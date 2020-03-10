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

namespace Testomat\PHPUnit\Common\Contract\Exception;

use Exception;
use InvalidArgumentException as BaseInvalidArgumentException;

final class InvalidArgumentException extends BaseInvalidArgumentException
{
    private function __construct(string $message = '', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function create(int $argument, string $type): self
    {
        $stack = debug_backtrace();

        return new self(
            sprintf(
                'Argument #%d of %s::%s() must be %s %s',
                $argument,
                $stack[1]['class'],
                $stack[1]['function'],
                \in_array(lcfirst($type)[0], ['a', 'e', 'i', 'o', 'u'], true) ? 'an' : 'a',
                $type
            )
        );
    }
}
