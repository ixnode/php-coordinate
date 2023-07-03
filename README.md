# PHP Coordinate

This library offers a collection of various PHP coordinate classes like Coordinate, etc.
It converts various coordinate strings and values into a unique format.

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

#### 2.1.1 Basic Decimal Parser

##### 2.1.1.1 Parser formats

| Given value (string)              | latitude (float) | longitude (float) |
|-----------------------------------|------------------|-------------------|
| `"51.0504,13.7373"`               | _51.0504_        | _13.7373_         |
| `"51.0504, 13.7373"`              | _51.0504_        | _13.7373_         |
| `"51.0504 13.7373"`               | _51.0504_        | _13.7373_         |
| `"POINT(-31.425299, -64.201743)"` | _-31.425299_     | _-64.201743_      |
| `"POINT(-31.425299 -64.201743)"`  | _-31.425299_     | _-64.201743_      |

##### 2.1.1.2 Code example

```php
$coordinate = new Coordinate('51.0504 13.7373');

$latitude = $coordinate->getLatitude();
// (float) 51.0504

$longitude = $coordinate->getLongitude();
// (float) 13.7373
```

### 2.2 Converter

#### 2.2.1 Methods

| Method            | Type     | Description                                                 | Example             |
|-------------------|----------|-------------------------------------------------------------|---------------------|
| `getLatitude`     | _float_  | Returns the decimal degree presentation of latitude value.  | _-31.425299_        |
| `getLongitude`    | _float_  | Returns the decimal degree presentation of longitude value. | _-64.201743_        |
| `getLatitudeDD`   | _float_  | Alias of `getLatitude`.                                     | _-31.425299_        |
| `getLongitudeDD`  | _float_  | Alias of `getLongitude`.                                    | _-64.201743_        |
| `getLatitudeDMS`  | _string_ | Returns the dms representation of the latitude value.       | `"31°25′31.0764″S"` |
| `getLongitudeDMS` | _string_ | Returns the dms representation of the longitude value.      | `"64°12′6.2748″W"`  |

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
