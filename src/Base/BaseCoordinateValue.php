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

namespace Ixnode\PhpCoordinate\Base;

use Ixnode\PhpException\Case\CaseUnsupportedException;

/**
 * Class CoordinateValue
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-07-03)
 * @since 0.1.0 (2023-07-03) First version.
 */
abstract class BaseCoordinateValue
{
    /** @var array{degree: int<0, 180>, minutes: int<0, 59>, seconds: float, direction: 'E'|'N'|'S'|'W'} */
    protected array $dms;

    protected string $type;

    final public const SECONDS_PER_HOUR = 3600;

    final public const SECONDS_PER_MINUTE = 60;

    final public const DEGREE_MIN = 0;

    final public const DEGREE_MAX = 180;

    final public const MINUTES_MIN = 0;

    final public const MINUTES_MAX = 59;

    final public const SECONDS_PRECISION = 6;

    final public const FORMAT_DMS_SHORT_1 = '%d°%d′%s″%s';

    final public const FORMAT_DMS_SHORT_2 = '%s%d°%d′%s″';

    final public const TYPE_LATITUDE = 'latitude';

    final public const TYPE_LONGITUDE = 'longitude';

    final public const DIRECTION_LATITUDE_NORTH = 'N';
    final public const DIRECTION_LATITUDE_SOUTH = 'S';
    final public const DIRECTION_LONGITUDE_WEST = 'W';
    final public const DIRECTION_LONGITUDE_EAST = 'E';

    final public const DIRECTIONS_LATITUDE = [
        1 => self::DIRECTION_LATITUDE_NORTH,
        -1 => self::DIRECTION_LATITUDE_SOUTH,
    ];

    final public const DIRECTIONS_LONGITUDE = [
        1 => self::DIRECTION_LONGITUDE_EAST,
        -1 => self::DIRECTION_LONGITUDE_WEST,
    ];

    /**
     * @param float $decimalDegree
     * @param string $type
     * @throws CaseUnsupportedException
     */
    public function __construct(protected float $decimalDegree, string $type = self::TYPE_LATITUDE)
    {
        $this->type = match ($type) {
            self::TYPE_LATITUDE => self::TYPE_LATITUDE,
            self::TYPE_LONGITUDE => self::TYPE_LONGITUDE,
            default => throw new CaseUnsupportedException(sprintf('Unsupported type "%s" given.', $type)),
        };

        $this->dms = $this->convertDecimalToDms($decimalDegree, $type);
    }

    /**
     * Converts the given decimal degree value to a dms representation.
     *
     * @param float $decimalDegree
     * @param string $type
     * @return array{degree: int<0, 180>, minutes: int<0, 59>, seconds: float, direction: 'E'|'N'|'S'|'W'}
     * @throws CaseUnsupportedException
     */
    private function convertDecimalToDms(float $decimalDegree, string $type = self::TYPE_LATITUDE): array
    {
        $direction = match ($type) {
            self::TYPE_LATITUDE => $decimalDegree < self::DEGREE_MIN ? self::DIRECTION_LATITUDE_SOUTH : self::DIRECTION_LATITUDE_NORTH,
            self::TYPE_LONGITUDE => $decimalDegree < self::DEGREE_MIN ? self::DIRECTION_LONGITUDE_WEST : self::DIRECTION_LONGITUDE_EAST,
            default => throw new CaseUnsupportedException(sprintf('Unsupported type "%s" given.', $this->type)),
        };

        $decimalDegree = abs($decimalDegree);

        $degree = floor($decimalDegree);

        $secondsOverall = ($decimalDegree - $degree) * self::SECONDS_PER_HOUR;

        $minutes = floor($secondsOverall / self::SECONDS_PER_MINUTE);

        $seconds = $secondsOverall - $minutes * self::SECONDS_PER_MINUTE;

        $degree = (int) $degree;
        $minutes = (int) $minutes;

        if ($degree < self::DEGREE_MIN || $degree > self::DEGREE_MAX) {
            throw new CaseUnsupportedException(sprintf('Unsupported degree "%d" given.', $degree));
        }

        if ($minutes < self::MINUTES_MIN || $minutes > self::MINUTES_MAX) {
            throw new CaseUnsupportedException(sprintf('Unsupported minutes "%d" given.', $minutes));
        }

        return [
            'degree' => $degree,
            'minutes' => $minutes,
            'seconds' => round(floatval($seconds), self::SECONDS_PRECISION),
            'direction' => $direction,
        ];
    }

    /**
     * Returns the decimal value of this coordinate value.
     *
     * @return float
     */
    public function getDecimal(): float
    {
        return $this->decimalDegree;
    }

    /**
     * Returns the type of this coordinate value (latitude or longitude).
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Returns the degree of gps position.
     *
     * @return int<0, 180>
     */
    public function getDegree(): int
    {
        return $this->dms['degree'];
    }

    /**
     * Returns the minutes of gps position.
     *
     * @return int<0, 59>
     */
    public function getMinutes(): int
    {
        return $this->dms['minutes'];
    }

    /**
     * Returns the minutes of gps position.
     *
     * @return float
     */
    public function getSeconds(): float
    {
        return $this->dms['seconds'];
    }

    /**
     * Returns the direction of gps position.
     *
     * @return 'E'|'N'|'S'|'W'
     */
    public function getDirection(): string
    {
        return $this->dms['direction'];
    }

    /**
     * Returns dms of gps position.
     *
     * @param string $format
     * @return string
     * @throws CaseUnsupportedException
     */
    public function getDms(string $format = self::FORMAT_DMS_SHORT_1): string
    {
        return match ($format) {
            self::FORMAT_DMS_SHORT_1 => sprintf($format, $this->getDegree(), $this->getMinutes(), $this->getSeconds(), $this->getDirection()),
            self::FORMAT_DMS_SHORT_2 => sprintf($format, $this->getDirection(), $this->getDegree(), $this->getMinutes(), $this->getSeconds()),
            default => throw new CaseUnsupportedException(sprintf('Unknown format "%s" given.', $format)),
        };
    }
}
