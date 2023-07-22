# PHP Coordinate

[![Release](https://img.shields.io/github/v/release/ixnode/php-coordinate)](https://github.com/ixnode/php-coordinate/releases)
[![](https://img.shields.io/github/release-date/ixnode/php-coordinate)](https://github.com/ixnode/php-coordinate/releases)
![](https://img.shields.io/github/repo-size/ixnode/php-coordinate.svg)
[![PHP](https://img.shields.io/badge/PHP-^8.2-777bb3.svg?logo=php&logoColor=white&labelColor=555555&style=flat)](https://www.php.net/supported-versions.php)
[![PHPStan](https://img.shields.io/badge/PHPStan-Level%20Max-777bb3.svg?style=flat)](https://phpstan.org/user-guide/rule-levels)
[![PHPUnit](https://img.shields.io/badge/PHPUnit-Unit%20Tests-6b9bd2.svg?style=flat)](https://phpunit.de)
[![PHPCS](https://img.shields.io/badge/PHPCS-PSR12-416d4e.svg?style=flat)](https://www.php-fig.org/psr/psr-12/)
[![PHPMD](https://img.shields.io/badge/PHPMD-ALL-364a83.svg?style=flat)](https://github.com/phpmd/phpmd)
[![Rector - Instant Upgrades and Automated Refactoring](https://img.shields.io/badge/Rector-PHP%208.2-73a165.svg?style=flat)](https://github.com/rectorphp/rector)
[![LICENSE](https://img.shields.io/github/license/ixnode/php-api-version-bundle)](https://github.com/ixnode/php-api-version-bundle/blob/master/LICENSE)

> This library offers a collection of various PHP coordinate classes like Coordinate, etc.
> It converts various coordinate strings and values into a unique format.

## 1. Installation

```bash
composer require ixnode/php-coordinate
```

```bash
vendor/bin/php-coordinate -V
```

```bash
php-coordinate 0.1.0 (03-07-2023 01:17:26) - Björn Hempel <bjoern@hempel.li>
```

## 2. Usage

```php
use Ixnode\PhpCoordinate\Coordinate;
```

### 2.1 Parser

#### 2.1.1 Basic decimal degree parser

##### 2.1.1.1 Parser formats

| Given value (string)              | Latitude (float) | Longitude (float) | Place              |
|-----------------------------------|------------------|-------------------|--------------------|
| `"51.0504,13.7373"`               | _51.0504_        | _13.7373_         | Dresden, Germany   |
| `"51.0504, 13.7373"`              | _51.0504_        | _13.7373_         | Dresden, Germany   |
| `"51.0504 13.7373"`               | _51.0504_        | _13.7373_         | Dresden, Germany   |
| `"POINT(-31.425299, -64.201743)"` | _-31.425299_     | _-64.201743_      | Córdoba, Argentina |
| `"POINT(-31.425299 -64.201743)"`  | _-31.425299_     | _-64.201743_      | Córdoba, Argentina |

##### 2.1.1.2 Code example

```php
$coordinate = new Coordinate('51.0504 13.7373');

$latitude = $coordinate->getLatitude();
// (float) 51.0504

$longitude = $coordinate->getLongitude();
// (float) 13.7373
```

#### 2.1.2 Basic DMS Parser

##### 2.1.2.1 Parser formats

| Given value (string)                       | Latitude (float) | Longitude (float) | Place              |
|--------------------------------------------|------------------|-------------------|--------------------|
| `"51°3′1.44″N,13°44′14.28″E"`              | _51.0504_        | _13.7373_         | Dresden, Germany   |
| `"51°3′1.44″N, 13°44′14.28″E"`             | _51.0504_        | _13.7373_         | Dresden, Germany   |
| `"51°3′1.44″N 13°44′14.28″E"`              | _51.0504_        | _13.7373_         | Dresden, Germany   |
| `"POINT(31°25′31.0764″S, 64°12′6.2748″W)"` | _-31.425299_     | _-64.201743_      | Córdoba, Argentina |
| `"POINT(31°25′31.0764″S 64°12′6.2748″W)"`  | _-31.425299_     | _-64.201743_      | Córdoba, Argentina |

##### 2.1.2.2 Code example

```php
$coordinate = new Coordinate('51°3′1.44″N 13°44′14.28″E');

$latitude = $coordinate->getLatitude();
// (float) 51.0504

$longitude = $coordinate->getLongitude();
// (float) 13.7373
```

#### 2.1.3 Google Url Parser Parser

##### 2.1.3.1 Parser formats

| Given value (string)                                   | Latitude (float) | Longitude (float) | Place            |
|--------------------------------------------------------|------------------|-------------------|------------------|
| Copied Google Maps Short Url<sup><code>1)</code></sup> | _54.07304830_    | _18.992402_       | Malbork, Poland  |
| Copied Google Maps Link<sup><code>2)</code></sup>      | _51.31237_       | _12.4132924_      | Leipzig, Germany |

* <sup><code>1)</code></sup> [Copied Google Maps Short Url](https://maps.app.goo.gl/PHq5axBaDdgRWj4T6)
* <sup><code>2)</code></sup> [Copied Google Maps Link](https://www.google.com/maps/place/V%C3%B6lkerschlachtdenkmal,+04277+Leipzig/@51.3123709,12.4132924,17z/data=!3m1!4b1!4m6!3m5!1s0x47a6f9a9d013ca23:0x277b49a142da988c!8m2!3d51.3123709!4d12.4132924!16s%2Fg%2F12ls2f87w?entry=ttu)

##### 2.1.3.2 Code example

```php
$coordinate = new Coordinate('https://maps.app.goo.gl/PHq5axBaDdgRWj4T6');

$latitude = $coordinate->getLatitude();
// (float) 54.07304830

$longitude = $coordinate->getLongitude();
// (float) 18.992402
```

### 2.2 Converter

#### 2.2.1 Methods

| Method            | Type     | Parameter                             | Description                                                 | Example             |
|-------------------|----------|---------------------------------------|-------------------------------------------------------------|---------------------|
| `getLatitude`     | _float_  | ---                                   | Returns the decimal degree presentation of latitude value.  | _-31.425299_        |
| `getLongitude`    | _float_  | ---                                   | Returns the decimal degree presentation of longitude value. | _-64.201743_        |
| `getLatitudeDD`   | _float_  | ---                                   | Alias of `getLatitude`.                                     | _-31.425299_        |
| `getLongitudeDD`  | _float_  | ---                                   | Alias of `getLongitude`.                                    | _-64.201743_        |
| `getLatitudeDMS`  | _string_ | ---                                   | Returns the dms representation of the latitude value.       | `"31°25′31.0764″S"` |
| `getLongitudeDMS` | _string_ | ---                                   | Returns the dms representation of the longitude value.      | `"64°12′6.2748″W"`  |
| `getLatitudeDMS`  | _string_ | `CoordinateValue::FORMAT_DMS_SHORT_2` | Returns the dms representation of the latitude value (v2).  | `"S31°25′31.0764″"` |
| `getLongitudeDMS` | _string_ | `CoordinateValue::FORMAT_DMS_SHORT_2` | Returns the dms representation of the longitude value (v2). | `"W64°12′6.2748″"`  |

#### 2.2.2 Code example

```php
$coordinate = new Coordinate('-31.425299, -64.201743');

$latitude = $coordinate->getLatitudeDMS();
// (string) "31°25′31.0764″S"

$longitude = $coordinate->getLongitudeDMS();
// (string) "64°12′6.2748″W"
```

## 3. Library development

```bash
git clone git@github.com:ixnode/php-coordinate.git && cd php-coordinate
```

```bash
composer install
```

```bash
composer test
```

## 4. License

This library is licensed under the MIT License - see the [LICENSE](/LICENSE) file for details.
