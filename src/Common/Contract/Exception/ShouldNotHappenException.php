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

use RuntimeException;

final class ShouldNotHappenException extends RuntimeException
{
    public function __construct()
    {
        $message = 'This should not happen, please open an issue on testomat/phpunit repository: %s';

        parent::__construct(sprintf($message, 'https://github.com/testomat/phpunit/issues/new'));
    }
}
