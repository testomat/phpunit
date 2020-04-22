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

namespace Testomat\PHPUnit\Printer;

use Testomat\PHPUnit\Printer\Traits\PrinterContentsTrait;

/**
 * This `if` condition exists because phpunit v8 or v9
 * is not a direct dependency.
 *
 * This code bellow it's for phpunit@8
 */
if (class_exists(\PHPUnit\Runner\Version::class) && (int) (substr(\PHPUnit\Runner\Version::id(), 0, 1)) === 8) {
    final class Printer extends \PHPUnit\TextUI\ResultPrinter
    {
        use PrinterContentsTrait;

        /**
         * Intentionally left blank as we output things on events of the listener.
         */
        public function write(string $buffer): void
        {
        }
    }
}

/**
 * This `if` condition exists because phpunit v8 or v9
 * is not a direct dependency.
 *
 * This code bellow it's for phpunit@9
 */
if (class_exists(\PHPUnit\Runner\Version::class) && (int) (substr(\PHPUnit\Runner\Version::id(), 0, 1)) === 9) {
    final class Printer implements \PHPUnit\TextUI\ResultPrinter
    {
        use PrinterContentsTrait;

        /**
         * Intentionally left blank as we output things on events of the listener.
         */
        public function write(string $buffer): void
        {
        }
    }
}
