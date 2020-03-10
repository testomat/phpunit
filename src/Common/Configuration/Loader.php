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

use DOMDocument;
use DOMElement;
use DOMXPath;
use PHPUnit\Util\Xml;

final class Loader
{
    public function load(string $filename): Configuration
    {
        $document = Xml::loadFile($filename, false, true, true);
        $xpath = new DOMXPath($document);

        [$type, $isUtf8, $showErrorOn, $excludeDirectories] = $this->getPrinterConfig($xpath);
        [$speedTrapActive, $speedTrapSlowThreshold, $speedTrapReportLength] = $this->getSpeedTrapConfig($xpath);
        [$overAssertiveActive, $overAssertiveThreshold, $overAssertiveReportLength] = $this->getOverAssertiveConfig($xpath);

        return new Configuration(
            $filename,
            $this->validate($document),
            $type,
            $isUtf8,
            $showErrorOn,
            $excludeDirectories,
            $speedTrapActive,
            $speedTrapSlowThreshold,
            $speedTrapReportLength,
            $overAssertiveActive,
            $overAssertiveThreshold,
            $overAssertiveReportLength
        );
    }

    /**
     * @psalm-return array<int,array<int,string>>
     */
    private function validate(DOMDocument $document): array
    {
        $original = libxml_use_internal_errors(true);

        $document->schemaValidate(\dirname(__DIR__, 1) . \DIRECTORY_SEPARATOR . 'Schema' . \DIRECTORY_SEPARATOR . 'testomat.xsd');

        $tmp = libxml_get_errors();

        libxml_clear_errors();
        libxml_use_internal_errors($original);

        $errors = [];

        foreach ($tmp as $error) {
            if (! isset($errors[$error->line])) {
                $errors[$error->line] = [];
            }

            $errors[$error->line][] = trim($error->message);
        }

        return $errors;
    }

    /**
     * @psalm-return array{0: string, 1: string, 2: \Testomat\PHPUnit\Common\Configuration\Collection}
     *
     * @return array<int, string|\Testomat\PHPUnit\Common\Configuration\Collection>
     */
    private function getPrinterConfig(DOMXPath $xpath): array
    {
        $type = Configuration::TYPE_EXPANDED;
        $showErrorOn = Configuration::SHOW_ERROR_ON_END;
        $isUtf8 = false;
        $directories = [
            'vendor/phpunit/phpunit/src' => true,
            'vendor/mockery/mockery' => true,
        ];

        foreach ($xpath->query('printer') as $element) {
            if ($element instanceof DOMElement) {
                if ($element->hasAttribute('type')) {
                    $type = $element->getAttribute('type');
                }

                if ($element->hasAttribute('utf8')) {
                    $isUtf8 = (bool) $element->getAttribute('utf8');
                }

                if ($element->hasAttribute('show_error_on')) {
                    $showErrorOn = $element->getAttribute('show_error_on');
                }

                if ($element->hasChildNodes()) {
                    foreach ($element->firstChild->childNodes as $node) {
                        if ($node instanceof DOMElement) {
                            $directories[$node->nodeValue] = true;
                        }
                    }
                }
            }
        }

        return [$type, $isUtf8, $showErrorOn, new Collection(array_keys($directories))];
    }

    /**
     * @return array{0: bool, 1: int, 2: int}
     */
    private function getSpeedTrapConfig(DOMXPath $xpath): array
    {
        $settings = [
            true,
            500,
            10,
        ];

        foreach ($xpath->query('speedtrap') as $element) {
            if ($element instanceof DOMElement) {
                if ($element->hasAttribute('enabled')) {
                    $settings[0] = $this->getBooleanAttribute($element, 'enabled', true);
                }

                if ($element->hasChildNodes()) {
                    foreach ($element->childNodes as $node) {
                        if ($node instanceof DOMElement) {
                            if ($node->nodeName === 'slow_threshold') {
                                $settings[1] = (int) $node->nodeValue;
                            }

                            if ($node->nodeName === 'report_length') {
                                $settings[2] = (int) $node->nodeValue;
                            }
                        }
                    }
                }
            }
        }

        return $settings;
    }

    /**
     * @return array{0: bool, 1: int, 2: int}
     */
    private function getOverAssertiveConfig(DOMXPath $xpath): array
    {
        $settings = [
            true,
            10,
            10,
        ];

        foreach ($xpath->query('over_assertive') as $element) {
            if ($element instanceof DOMElement) {
                if ($element->hasAttribute('enabled')) {
                    $settings[0] = $this->getBooleanAttribute($element, 'enabled', true);
                }

                if ($element->hasChildNodes()) {
                    foreach ($element->childNodes as $node) {
                        if ($node instanceof DOMElement) {
                            if ($node->nodeName === 'threshold') {
                                $settings[1] = (int) $node->nodeValue;
                            }

                            if ($node->nodeName === 'report_length') {
                                $settings[2] = (int) $node->nodeValue;
                            }
                        }
                    }
                }
            }
        }

        return $settings;
    }

    private function getBooleanAttribute(DOMElement $element, string $attribute, bool $default): bool
    {
        if (! $element->hasAttribute($attribute)) {
            return $default;
        }

        return (bool) $this->getBoolean(
            (string) $element->getAttribute($attribute),
            false
        );
    }

    /**
     * if $value is 'false' or 'true', this returns the value that $value represents.
     * Otherwise, returns $default, which may be a string in rare cases.
     * See PHPUnit\TextUI\ConfigurationTest::testPHPConfigurationIsReadCorrectly.
     *
     * @param bool|string $default
     *
     * @return bool|string
     */
    private function getBoolean(string $value, $default)
    {
        if (strtolower($value) === 'false') {
            return false;
        }

        if (strtolower($value) === 'true') {
            return true;
        }

        return $default;
    }
}
