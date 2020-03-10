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

namespace Testomat\PHPUnit\ResultPrinter\Terminal;

use AlecRabbit\ConsoleColour\Contracts\Effect;
use AlecRabbit\ConsoleColour\Contracts\Styles;
use AlecRabbit\ConsoleColour\Themes as AlecRabbitThemes;
use PHPUnit\Runner\BaseTestRunner;
use Testomat\PHPUnit\ResultPrinter\TestResult;

final class Themes extends AlecRabbitThemes
{
    public const MY_STYLES = [
        'colorType' . BaseTestRunner::STATUS_UNKNOWN => [Effect::BOLD, Styles::CYAN],
        'colorType' . BaseTestRunner::STATUS_PASSED => [Effect::BOLD, Styles::GREEN],
        'colorType' . BaseTestRunner::STATUS_SKIPPED => [Effect::BOLD, Styles::WHITE],
        'colorType' . BaseTestRunner::STATUS_INCOMPLETE => [Effect::BOLD, Styles::CYAN],
        'colorType' . BaseTestRunner::STATUS_FAILURE => [Effect::BOLD, Styles::RED],
        'colorType' . BaseTestRunner::STATUS_ERROR => [Effect::BOLD, Styles::LIGHT_RED],
        'colorType' . BaseTestRunner::STATUS_RISKY => [Effect::BOLD, Styles::MAGENTA],
        'colorType' . BaseTestRunner::STATUS_WARNING => [Effect::BOLD, Styles::YELLOW],
        'colorType' . TestResult::RUNS => [],
        'default' => [Styles::DEFAULT_COLOR, Styles::BG_DEFAULT],
    ];

    /**
     * {@inheritdoc}
     */
    protected function prepareThemes(): array
    {
        return array_merge(self::MY_STYLES, parent::prepareThemes());
    }
}
