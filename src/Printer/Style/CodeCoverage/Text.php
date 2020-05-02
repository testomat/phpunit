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

namespace Testomat\PHPUnit\Printer\Style\CodeCoverage;

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Node\Directory;
use SebastianBergmann\CodeCoverage\Node\File;
use SebastianBergmann\CodeCoverage\Util;
use Testomat\PHPUnit\Common\Terminal\Terminal;
use Testomat\TerminalColour\Formatter;

final class Text
{
    /** @var int */
    private $numberOfColumns;

    /** @var \Testomat\PHPUnit\Common\Terminal\Terminal */
    private $output;

    /** @var \Testomat\TerminalColour\Contract\WrappableFormatter */
    private $colour;

    /** @var int */
    private $lowUpperBound;

    /** @var int */
    private $highLowerBound;

    /** @var bool */
    private $showUncoveredFiles;

    /** @var bool */
    private $showOnlySummary;

    public function __construct(Terminal $terminal, string $colors, int $numberOfColumns)
    {
        $this->output = $terminal;
        $this->numberOfColumns = $numberOfColumns;

        if ($colors === 'always') {
            $enableColor = true;
        } elseif ($colors === 'auto') {
            $enableColor = $this->output->hasColorSupport();
        } else {
            $enableColor = false;
        }

        $styles = [];

        $this->colour = new Formatter($enableColor, $styles, $this->output->getStream());
    }

    public function setLowUpperBound(int $lowUpperBound): void
    {
        $this->lowUpperBound = $lowUpperBound;
    }

    public function setHighLowerBound(int $highLowerBound): void
    {
        $this->highLowerBound = $highLowerBound;
    }

    public function setShowUncoveredFiles(bool $showUncoveredFiles): void
    {
        $this->showUncoveredFiles = $showUncoveredFiles;
    }

    public function setShowOnlySummary(bool $showOnlySummary): void
    {
        $this->showOnlySummary = $showOnlySummary;
    }

    public function process(CodeCoverage $coverage): void
    {
        $report = $coverage->getReport();

        if (! $this->showOnlySummary) {
            $this->output->writeln($this->colour->format('<effects=bold>Code Coverage:</>' . \PHP_EOL));

            /**
             * @psalm-var array{namespace: string, className: string, methodsCovered: float|int, methodCount: int, statementsCovered: int, statementCount: int} $classInfo
             *
             * @var string $fullQualifiedPath
             * @var array<string, string|int> $classInfo
             */
            foreach ($this->prepareClassCoverage($report) as $fullQualifiedPath => $classInfo) {
                if ($this->showUncoveredFiles || $classInfo['statementsCovered'] !== 0) {
                    $classPercent = Util::percent(
                        $classInfo['methodsCovered'],
                        $classInfo['methodCount'],
                        true,
                        true
                    );

                    if ($classPercent === '') {
                        $classPercent = '100.00%';
                    }

                    $color = $this->getCoverageColor($classPercent);

                    $this->output->writeln($this->colour->format(\Safe\sprintf(
                        ' <fg=white>[</><fg=%s>%s</><fg=white> %%]</>  %s',
                        $color,
                        str_replace('%', '', (string) $classPercent),
                        $this->getClassName($classInfo)
                    )));
                }
            }

            $this->output->writeln('');

            $this->output->write($this->colour->format('<effects=bold>Summary:</>         '));
        } else {
            $this->output->write($this->colour->format('<effects=bold>Coverage Summary:</>  '));
        }

        $classesPercent = Util::percent(
            $report->getNumTestedClassesAndTraits(),
            $report->getNumClassesAndTraits(),
            true
        );
        $methodsPercent = Util::percent(
            $report->getNumTestedMethods(),
            $report->getNumMethods(),
            true
        );
        $linesPercent = Util::percent(
            $report->getNumExecutedLines(),
            $report->getNumExecutableLines(),
            true
        );

        $this->output->write($this->colour->format(\Safe\sprintf(
            '<effects=bold>Classes</> <fg=%s>%s</> %%',
            $this->getCoverageColor($classesPercent),
            str_replace('%', '', (string) $classesPercent)
        )));
        $this->output->write($this->colour->format(\Safe\sprintf(
            '   <effects=bold>Methods</> <fg=%s>%s</> %%',
            $this->getCoverageColor($methodsPercent),
            str_replace('%', '', (string) $methodsPercent)
        )));
        $this->output->writeln($this->colour->format(\Safe\sprintf(
            '   <effects=bold>Lines</> <fg=%s>%s</> %%',
            $this->getCoverageColor($linesPercent),
            str_replace('%', '', (string) $linesPercent)
        )));
    }

    /**
     * @param float|int|string $coverage
     */
    private function getCoverageColor($coverage): string
    {
        if ($coverage >= $this->highLowerBound) {
            return 'green';
        }

        if ($coverage > $this->lowUpperBound) {
            return 'yellow';
        }

        return 'red';
    }

    /**
     * @psalm-return array{namespace: string, className: string, methodsCovered: float|int, methodCount: int, statementsCovered: int, statementCount: int}
     *
     * @return array<string, string|int>
     */
    private function prepareClassCoverage(Directory $report): array
    {
        $classCoverage = [];

        foreach ($report as $item) {
            if (! $item instanceof File) {
                continue;
            }

            $classes = $item->getClassesAndTraits();

            /**
             * @var string $className
             */
            foreach ($classes as $className => $class) {
                $classStatements = 0;
                $coveredClassStatements = 0;
                $coveredMethods = 0;
                $classMethods = 0;

                foreach ($class['methods'] as $method) {
                    if ($method['executableLines'] === 0) {
                        continue;
                    }

                    $classMethods++;
                    $classStatements += $method['executableLines'];
                    $coveredClassStatements += $method['executedLines'];

                    if ($method['coverage'] === 100) {
                        $coveredMethods++;
                    }
                }

                $namespace = '';

                if (isset($class['package']['namespace']) && $class['package']['namespace'] !== '') {
                    $namespace = '\\' . $class['package']['namespace'] . '::';
                } elseif (isset($class['package']['fullPackage']) && $class['package']['fullPackage'] !== '') {
                    $namespace = '@' . $class['package']['fullPackage'] . '::';
                }

                $classCoverage[$namespace . $className] = [
                    'namespace' => $namespace,
                    'className' => $className,
                    'methodsCovered' => $coveredMethods,
                    'methodCount' => $classMethods,
                    'statementsCovered' => $coveredClassStatements,
                    'statementCount' => $classStatements,
                ];
            }
        }

        ksort($classCoverage);

        return $classCoverage;
    }

    /**
     * @psalm-param array{namespace: string, className: string, methodsCovered: float|int, methodCount: int, statementsCovered: int, statementCount: int} $classInfo
     * @param array<string, int|string> $classInfo
     */
    private function getClassName(array $classInfo): string
    {
        $namespace = array_flip(explode('\\', str_replace('::', '', $classInfo['namespace'])));
        $class = array_flip(explode('\\', $classInfo['className']));

        return implode('\\', array_keys(array_merge($namespace, $class)));
    }
}
