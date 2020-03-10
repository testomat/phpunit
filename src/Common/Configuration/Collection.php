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

namespace Testomat\PHPUnit\Common\Configuration;

use ArrayIterator;
use IteratorAggregate;

/**
 * @psalm-immutable
 * @implements \IteratorAggregate<int|string, mixed>
 */
final class Collection implements IteratorAggregate
{
    /** @var array<int|string, mixed> */
    private $extensions;

    /**
     * @param array<int|string, mixed> $extensions
     */
    public function __construct(array $extensions)
    {
        $this->extensions = $extensions;
    }

    /**
     * @return array<int|string, mixed>
     */
    public function toArray(): array
    {
        return $this->extensions;
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->toArray());
    }
}
