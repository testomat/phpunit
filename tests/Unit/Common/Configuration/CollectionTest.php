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

namespace Testomat\PHPUnit\Tests\Unit\Common\Configuration;

use PHPUnit\Framework\TestCase;
use Testomat\PHPUnit\Common\Configuration\Collection;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class CollectionTest extends TestCase
{
    /** @var array<int|string, mixed> */
    private $arrayData;

    /** @var \Testomat\PHPUnit\Common\Configuration\Collection */
    private $collection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->arrayData = [
            'foo',
            'bar',
        ];
        $this->collection = new Collection($this->arrayData);
    }

    public function testToArray(): void
    {
        self::assertSame($this->arrayData, $this->collection->toArray());
    }

    public function testGetIterator(): void
    {
        foreach ($this->collection->getIterator() as $key => $value) {
            self::assertContains($value, ['foo', 'bar']);
        }
    }
}
