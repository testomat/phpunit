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

namespace Testomat\PHPUnit\Tests\Fixture;

use Exception;

final class ExceptionDummyClass
{
    public function get(): void
    {
        throw new Exception('dadsa.');
    }
}
