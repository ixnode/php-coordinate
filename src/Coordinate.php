<?php

/*
 * This file is part of the ixnode/php-coordinate project.
 *
 * (c) Björn Hempel <https://www.hempel.li/>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Ixnode\PhpCoordinate;

use Ixnode\PhpCoordinate\Base\BaseCoordinate;
use Ixnode\PhpCoordinate\Base\BaseCoordinateValue;
use Ixnode\PhpCoordinate\Tests\Unit\CoordinateTest;
use Ixnode\PhpException\Case\CaseUnsupportedException;

/**
 * Class Coordinate
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-07-03)
 * @since 0.1.0 (2023-07-03) First version.
 * @link CoordinateTest
 */
class Coordinate extends BaseCoordinate
{
    protected const PRECISION_METERS = 1;

    protected const PRECISION_KILOMETERS = 3;

    final public const RETURN_METERS = 'meters';

    final public const RETURN_KILOMETERS = 'kilometers';

    /* WGS84 */
    final public const EARTH_RADIUS_METER = 6_371_000;

    /**
     * Returns the latitude of given coordinate.
     *
     * @return float
     */
    public function getLatitude(): float
    {
        return $this->getLatitudeDecimal();
    }

    /**
     * Returns the longitude of given coordinate.
     *
     * @return float
     */
    public function getLongitude(): float
    {
        return $this->getLongitudeDecimal();
    }

    /**
     * Returns the latitude of given coordinate (decimal degree).
     *
     * @return float
     */
    public function getLatitudeDecimal(): float
    {
        return $this->latitude->getDecimal();
    }

    /**
     * Returns the longitude of given coordinate (decimal degree).
     *
     * @return float
     */
    public function getLongitudeDecimal(): float
    {
        return $this->longitude->getDecimal();
    }

    /**
     * Returns the latitude of given coordinate (in dms representation).
     *
     * @param string $format
     * @return string
     * @throws CaseUnsupportedException
     */
    public function getLatitudeDMS(string $format = BaseCoordinateValue::FORMAT_DMS_SHORT_1): string
    {
        return $this->latitude->getDms($format);
    }

    /**
     * Returns the longitude of given coordinate (in dms representation).
     *
     * @param string $format
     * @return string
     * @throws CaseUnsupportedException
     */
    public function getLongitudeDMS(string $format = BaseCoordinateValue::FORMAT_DMS_SHORT_1): string
    {
        return $this->longitude->getDms($format);
    }

    /**
     * Returns the WGS84 distance in meters.
     *
     * @param Coordinate $coordinate
     * @param string $returnValue
     * @return float
     * @throws CaseUnsupportedException
     */
    public function getDistance(Coordinate $coordinate, string $returnValue = self::RETURN_METERS): float
    {
        /* Conversion of latitude and longitude in radians. */
        $longitudeRadianStart = deg2rad($this->longitude->getDecimal());
        $latitudeRadianStart = deg2rad($this->latitude->getDecimal());

        $longitudeRadianEnd = deg2rad($coordinate->getLongitudeDecimal());
        $latitudeRadianEnd = deg2rad($coordinate->getLatitudeDecimal());

        /* Differences of latitudes and longitudes. */
        $longitudeDelta = $longitudeRadianEnd - $longitudeRadianStart;
        $latitudeDelta = $latitudeRadianEnd - $latitudeRadianStart;

        /* Haversine formula: https://en.wikipedia.org/wiki/Haversine_formula */
        $asinSqrt = sin($latitudeDelta / 2) ** 2 +
            sin($longitudeDelta / 2) ** 2 *
            cos($latitudeRadianStart) * cos($latitudeRadianEnd);

        $distance = 2 * self::EARTH_RADIUS_METER * asin(sqrt($asinSqrt));

        return match ($returnValue) {
            self::RETURN_METERS => round($distance, self::PRECISION_METERS),
            self::RETURN_KILOMETERS => round($distance / 1000, self::PRECISION_KILOMETERS),
            default => throw new CaseUnsupportedException(sprintf('The given return value "%s" is not supported.', $returnValue)),
        };
    }
}
