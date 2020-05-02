<h1 align="center">Testomat PHPUnit Printer</h1>
<p align="center">
    <a href="https://github.com/testomat/phpunit-printer/releases"><img src="https://img.shields.io/packagist/v/testomat/phpunit-printer.svg?style=flat-square"></a>
    <a href="https://php.net/"><img src="https://img.shields.io/badge/php-%5E7.3.0-8892BF.svg?style=flat-square"></a>
    <a href="https://codecov.io/gh/testomat/phpunit"><img src="https://img.shields.io/codecov/c/github/testomat/phpunit/master.svg?style=flat-square"></a>
    <a href="#"><img src="https://img.shields.io/badge/style-level%207-brightgreen.svg?style=flat-square&label=phpstan"></a>
    <a href="http://opensource.org/licenses/MIT"><img src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square"></a>
</p>

Provides a PHPUnit printer class, with:
- beautiful error reporting
- slow-running tests reporting
- over assertive test reporting
- provides a beautiful text-coverage output
- provides output for a compact and expanded test runner view

## Installation

Run

```
$ composer require testomat/phpunit-printer
```

## Usage

Enable the printer by adding the following to your `phpunit.xml` or `phpunit.xml.dist` file:

```xml
<phpunit
    printerClass="Testomat\PHPUnit\Printer\Printer">
</phpunit>
```

Now run your test suite as normal.

## Configuration

Within the configuration file `testomat.xml` a number of options can be passed to the printer.

First create a `testomat.xml` in the root folder of your project and copy this into it.

```xml
<?xml version="1.0" encoding="UTF-8"?>
<testomat
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:noNamespaceSchemaLocation="vendor/testomat/phpunit-common/Schema/testomat.xsd"
>
... printer configuration
</testomat>
```

#### Printer settings

The printer comes with 2 types of printer views `expanded` and `compact` (default).
To change the printer view, use the `type` argument.

```xml
    <printer type="expanded"/>
```

You want to change how the errors printed by the PHPUnit printer, use the `show_error_on` option.

```xml
    <printer show_error_on="end"/>
```

To print the error after a failed test use `show_error_on="test"`, default is to print the errors after the test runner finished.

> Note: the printer supports all PHPUnit stopOn... settings.

To reduce the exception trace on the beautiful error output, use the ` <exclude>...</exclude>` argument.

```xml
    <printer>
        <exclude>
            <directory>vendor/phpunit/phpunit/src</directory>
            <directory>vendor/mockery/mockery</directory>
        </exclude>
    </printer>
```

> Note: The PHPUnit and Mockery exception trace, is filtered out by default.

#### Speed trap settings

By default, the speed trap collector is active, to deactivate the speed trap use `enabled="false"`.

```xml
    <speedtrap enabled="false"/>
```

To change the overall suite threshold from `500` (default) to something higher use the `<slow_threshold>500</slow_threshold>` argument.

```xml
    <speedtrap>
        <slow_threshold>1000</slow_threshold>
    </speedtrap>
```

or [PHPUnit annotations](https://phpunit.readthedocs.io/en/9.1/annotations.html), the `@slowThreshold` annotation can be added to test classes or test methods to override any suite or group thresholds:

```php
/**
 * @slowThreshold 2000
 */
class SomeTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @slowThreshold 5000
     */
    public function testLongRunningProcess()
    {
    }
}
```

To change the default speed trap report length of 10 lowest tests, use the `<report_length>10</report_length>` argument.

```xml
    <speedtrap>
        <report_length>20</report_length>
    </speedtrap>
```

#### Over assertive settings

By default, the over assertive collector is active, to deactivate the over assertive use `enabled="false"`.

```xml
    <over_assertive enabled="true"/>
```

To change the overall suite assertion from `10` (default) to something higher use the `<threshold>10</threshold>` argument.

```xml
    <over_assertive>
        <threshold>15</threshold>
    </over_assertive>
```

or [PHPUnit annotations](https://phpunit.readthedocs.io/en/9.1/annotations.html), the `@assertionThreshold` annotation can be added to test classes or test methods to override any suite or group thresholds:

```php
/**
 * @assertionThreshold 15
 */
class SomeTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @assertionThreshold 20
     */
    public function testLongRunningProcess()
    {
        self::assertTrue(true);
        ...
    }
}
```

To change the default over assertive report length of 10 assertions per tests, use the `<report_length>10</report_length>` argument.

```xml
    <over_assertive>
        <report_length>20</report_length>
    </over_assertive>
```

## Versioning

This library follows semantic versioning, and additions to the code ruleset are performed in major releases.

## Changelog

Please have a look at [`CHANGELOG.md`](../../CHANGELOG.md).

## Contributing

Please have a look at [`CONTRIBUTING.md`](../../.github/CONTRIBUTING.md).

## Code of Conduct

Please have a look at [`CODE_OF_CONDUCT.md`](../../.github/CODE_OF_CONDUCT.md).

## License

This package is licensed using the MIT License.

Please have a look at [`LICENSE.md`](../../LICENSE.md).
