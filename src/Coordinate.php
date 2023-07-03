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
class Coordinate
{
    protected CoordinateValue $latitude;

    protected CoordinateValue $longitude;

    /**
     * @param string $coordinate
     * @throws CaseUnsupportedException
     */
    public function __construct(string $coordinate)
    {
        $result = $this->doParse($coordinate);

        if ($result === false) {
            throw new CaseUnsupportedException(sprintf(
                'Unable to parse coordinate "%s".',
                $coordinate
            ));
        }
    }

    /**
     * Builds a float number from given integer and decimal part.
     *
     * @param string $integer
     * @param string $decimals
     * @return float
     */
    private function buildFloat(string $integer, string $decimals): float
    {
        $integerConverted = floatval($integer);
        $decimalsConverted = floatval(intval($decimals) * 10 ** (-strlen($decimals)));

        return $integerConverted + ($integerConverted < 0 ? -1 : 1) * $decimalsConverted;
    }

    /**
     * Builds this coordinate from given latitude and longitude values.
     *
     * @param float $latitude
     * @param float $longitude
     * @return void
     * @throws CaseUnsupportedException
     */
    private function buildCoordinate(float $latitude, float $longitude): void
    {
        $this->latitude = new CoordinateValueLatitude($latitude);
        $this->longitude = new CoordinateValueLongitude($longitude);
    }

    /**
     * Parses the given coordinate string.
     *
     * @param string $coordinate
     * @return bool
     * @throws CaseUnsupportedException
     */
    private function doParse(string $coordinate): bool
    {
        $matches = [];

        /* Try to parse decimal values like: "51.0504, 13.7373", "51.0504 13.7373", etc. */
        if (preg_match('~(-?[0-9]+)[.]([0-9]+)[,: ]*(-?[0-9]+)[.]([0-9]+)~', $coordinate, $matches)) {
            $this->buildCoordinate(
                $this->buildFloat(strval($matches[1]), strval($matches[2])),
                $this->buildFloat(strval($matches[3]), strval($matches[4]))
            );
            return true;
        }

        return false;
    }

    /**
     * Returns the latitude of given coordinate.
     *
     * @return float
     */
    public function getLatitude(): float
    {
        return $this->getLatitudeDD();
    }

    /**
     * Returns the longitude of given coordinate.
     *
     * @return float
     */
    public function getLongitude(): float
    {
        return $this->getLongitudeDD();
    }

    /**
     * Returns the latitude of given coordinate (decimal degree).
     *
     * @return float
     */
    public function getLatitudeDD(): float
    {
        return $this->latitude->getDecimal();
    }

    /**
     * Returns the longitude of given coordinate (decimal degree).
     *
     * @return float
     */
    public function getLongitudeDD(): float
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
    public function getLatitudeDMS(string $format = CoordinateValue::FORMAT_DMS_SHORT_1): string
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
    public function getLongitudeDMS(string $format = CoordinateValue::FORMAT_DMS_SHORT_1): string
    {
        return $this->longitude->getDms($format);
    }
}
