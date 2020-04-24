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

namespace Testomat\PHPUnit\Tests\Unit\Common\Contract\Exception;

use PHPUnit\Framework\TestCase;
use Testomat\PHPUnit\Common\Contract\Exception\ShouldNotHappenException;

/**
 * @internal
 *
 * @covers \Testomat\PHPUnit\Common\Contract\Exception\ShouldNotHappenException
 *
 * @small
 */
final class ShouldNotHappenExceptionTest extends TestCase
{
    public function testExceptionMessage(): void
    {
        $this->expectExceptionMessage('This should not happen, please open an issue on testomat/phpunit repository: https://github.com/testomat/phpunit/issues/new');

        throw new ShouldNotHappenException();
    }
}
