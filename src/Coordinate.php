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

use Ixnode\PhpCoordinate\Base\BaseCoordinateValue;
use Ixnode\PhpCoordinate\Tests\Unit\CoordinateTest;
use Ixnode\PhpException\Case\CaseUnsupportedException;
use Ixnode\PhpException\Parser\ParserException;
use JetBrains\PhpStorm\NoReturn;

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
    protected CoordinateValueLatitude $latitude;

    protected CoordinateValueLongitude $longitude;

    protected const PARSED_VALUES = 2;

    protected const ARGUMENTS_1 = 1;

    protected const ARGUMENTS_2 = 2;

    /**
     * @throws CaseUnsupportedException
     * @throws ParserException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    #[NoReturn]
    public function __construct()
    {
        $arguments = func_get_args();

        if (count($arguments) < self::ARGUMENTS_1) {
            throw new CaseUnsupportedException('No coordinates are given.');
        }

        $result = match (true) {
            /* 1 argument as string given. */
            count($arguments) === self::ARGUMENTS_1 && is_string($arguments[0]) =>
                $this->doParse($arguments[0]),

            /* 2 arguments and both are floats. */
            count($arguments) === self::ARGUMENTS_2 && is_float($arguments[0]) && is_float($arguments[1]) =>
                $this->buildCoordinate($arguments[0], $arguments[1]),

            /* 2 arguments and at least one is a string. */
            count($arguments) === self::ARGUMENTS_2 && is_string($arguments[0]) && is_float($arguments[1]),
            count($arguments) === self::ARGUMENTS_2 && is_float($arguments[0]) && is_string($arguments[1]),
            count($arguments) === self::ARGUMENTS_2 && is_string($arguments[0]) && is_string($arguments[1]) =>
                $this->doParse(sprintf('%s %s', $arguments[0], $arguments[1])),
            default => throw new CaseUnsupportedException('Unsupported parameter given.'),
        };

        if ($result === false) {
            throw new CaseUnsupportedException(sprintf(
                'Unable to parse coordinate "%s".',
                implode(', ', $arguments)
            ));
        }
    }

    /**
     * Builds this coordinate from given latitude and longitude values.
     *
     * @param float $latitude
     * @param float $longitude
     * @return bool
     * @throws CaseUnsupportedException
     */
    private function buildCoordinate(float $latitude, float $longitude): bool
    {
        $this->latitude = new CoordinateValueLatitude($latitude);
        $this->longitude = new CoordinateValueLongitude($longitude);

        return true;
    }

    /**
     * Parses the given coordinate string.
     *
     * @param string $coordinate
     * @return bool
     * @throws CaseUnsupportedException
     * @throws ParserException
     */
    private function doParse(string $coordinate): bool
    {
        $coordinateParser = new CoordinateParser($coordinate);

        $parsed = $coordinateParser->getParsed();

        if ($parsed === false || !$coordinateParser->isParsed()) {
            throw new ParserException($coordinate, 'latitude, longitude parser');
        }

        if (count($parsed) !== self::PARSED_VALUES) {
            throw new CaseUnsupportedException(sprintf('The number of parsed values must be "%s".', self::PARSED_VALUES));
        }

        [$latitude, $longitude] = $parsed;

        $this->buildCoordinate($latitude, $longitude);

        return true;
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
}
