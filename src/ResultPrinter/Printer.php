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

namespace Testomat\PHPUnit\ResultPrinter;

use Testomat\PHPUnit\ResultPrinter\Traits\PrinterContentsTrait;

/**
 * This `if` condition exists because phpunit v8 or v9
 * is not a direct dependency.
 *
 * This code bellow it's for phpunit@8
 */
if (class_exists(\PHPUnit\Runner\Version::class) && (int) (substr(\PHPUnit\Runner\Version::id(), 0, 1)) === 8) {
    /**
     * This is an Collision Phpunit Adapter implementation.
     *
     * @internal
     */
    final class Printer extends \PHPUnit\Util\Printer implements \PHPUnit\Framework\TestListener
    {
        use PrinterContentsTrait;
    }
}

/**
 * This `if` condition exists because phpunit v8 or v9
 * is not a direct dependency.
 *
 * This code bellow it's for phpunit@9
 */
if (class_exists(\PHPUnit\Runner\Version::class) && (int) (substr(\PHPUnit\Runner\Version::id(), 0, 1)) === 9) {
    /**
     * This is an Collision Phpunit Adapter implementation.
     *
     * @internal
     */
    final class Printer implements \PHPUnit\TextUI\ResultPrinter
    {
        use PrinterContentsTrait;

        /**
         * Intentionally left blank as we output things on events of the listener.
         */
        public function printResult(\PHPUnit\Framework\TestResult $result): void
        {
            // ..
        }
    }
}
