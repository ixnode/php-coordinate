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

use DateTimeZone;
use Exception;
use Ixnode\PhpException\ArrayType\ArrayKeyNotFoundException;
use Ixnode\PhpException\Case\CaseUnsupportedException;

/**
 * Class CoordinateParser
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-07-10)
 * @since 0.1.0 (2023-07-10) First version.
 */
abstract class BaseCoordinateParser
{
    private const REGEXP_SPLIT_PART = '[,: ]+';

    protected const REGEXP_SPLIT = '~'.self::REGEXP_SPLIT_PART.'~';

    final public const REGEXP_PARSER_GOOGLE_SPOT_LINK = '~!3d([0-9]+)\.([0-9]+)+.+!4d([0-9]+)\.([0-9]+)~';

    final public const REGEXP_PARSER_GOOGLE_REDIRECT_LINK = '~(https://maps.app.goo.gl/[a-zA-Z0-9]+)$~';

    final public const REGEXP_GOOGLE_LOCATION_REDIRECT = '~^location: .+!3d([0-9]+)\.([0-9]+)+.+!4d([0-9]+)\.([0-9]+).+~m';

    /* 51.0504, 13.7373, -33.940525, 18.414006, 40.690069, -74.045508, etc. */
    final public const REGEXP_PARSER_DECIMAL_DEGREE_PART = '(-?[0-9]+)[.]([0-9]+)';
    final public const REGEXP_PARSER_DECIMAL_DEGREE = '~'.self::REGEXP_PARSER_DECIMAL_DEGREE_PART.'~';

    /* 51°3′1.44″N, 13°44′14.28″E, etc. */
    final public const REGEXP_PARSER_DMS_V1_PART = '(\d+)°(\d+)′(\d+)[.](\d+)″([NSEW])';
    final public const REGEXP_PARSER_DMS_V1 = '~'.self::REGEXP_PARSER_DMS_V1_PART.'~';

    /* N51°3′1.44″, E13°44′14.28″, etc. */
    final public const REGEXP_PARSER_DMS_V2_PART = '([NSEW])(\d+)°(\d+)′(\d+)[.](\d+)″';
    final public const REGEXP_PARSER_DMS_V2 = '~'.self::REGEXP_PARSER_DMS_V2_PART.'~';

    protected const MATCHES_LENGTH_POINTS = 2;

    private const MATCHES_LENGTH_COORDINATE = 5;

    private const MATCHES_LENGTH_DMS = 6;

    private const MATCHES_LENGTH_DECIMAL = 3;

    private const MATCHES_LENGTH_LINK = 2;

    private const MATCHES_LENGTH_TIMEZONE = 2;

    protected const DECIMAL_PRECISION = 6;

    /* North: >0 (+) */
    final public const DIRECTION_LATITUDE_NORTH = 'N';

    /* South: <0 (-) */
    final public const DIRECTION_LATITUDE_SOUTH = 'S';

    /* East: >0 (+) */
    final public const DIRECTION_LONGITUDE_EAST = 'E';

    /* West: <0 (-) */
    final public const DIRECTION_LONGITUDE_WEST = 'W';

    final public const DIRECTIONS_LATITUDE = [
        self::DIRECTION_LATITUDE_NORTH,
        self::DIRECTION_LATITUDE_SOUTH,
    ];

    final public const DIRECTIONS_LONGITUDE = [
        self::DIRECTION_LONGITUDE_WEST,
        self::DIRECTION_LONGITUDE_EAST,
    ];

    final public const TYPE_DIRECTION_LATITUDE = 'latitude';

    final public const TYPE_DIRECTION_LONGITUDE = 'longitude';

    private const VERSION_V1 = 'v1';

    protected const VERSION_V2 = 'v2';

    protected string $coordinate;

    private ?string $error = null;

    /** @var float[]|false|null $parsed */
    protected array|false|null $parsed = null;

    /**
     * @param string $coordinate
     * @throws CaseUnsupportedException
     */
    public function __construct(string $coordinate)
    {
        $this->coordinate = trim($coordinate);
        $this->parsed = $this->doParse();
    }

    /**
     * Parser: Tries to parse the given coordinate string (coordinate, link, etc.).
     *
     * @return float[]|false
     * @throws CaseUnsupportedException
     */
    abstract public function doParse(): array|false;

    /**
     * Point: Returns the latitude and longitude values from given regexp matches.
     *
     * @param array<int, string> $matches
     * @return float[]
     * @throws CaseUnsupportedException
     */
    protected function getPointFromDecimalDegree(array $matches): array
    {
        if (count($matches) !== self::MATCHES_LENGTH_COORDINATE) {
            throw new CaseUnsupportedException(sprintf(
                'The given length of matches must be %d.',
                self::MATCHES_LENGTH_COORDINATE
            ));
        }

        return [
            round($this->getFloatValueFromSimple([$matches[0], $matches[1], $matches[2]]), self::DECIMAL_PRECISION),
            round($this->getFloatValueFromSimple([$matches[0], $matches[3], $matches[4]]), self::DECIMAL_PRECISION),
        ];
    }

    /**
     * Float value: Returns the decimal degree representation of given DMS values.
     *
     * @param array<int, string> $matches
     * @param string $type
     * @param string $version
     * @return float
     * @throws CaseUnsupportedException
     */
    protected function getFloatValueFromDMS(
        array $matches,
        string $type = self::TYPE_DIRECTION_LATITUDE,
        string $version = self::VERSION_V1
    ): float
    {
        if (count($matches) !== self::MATCHES_LENGTH_DMS) {
            throw new CaseUnsupportedException(sprintf(
                'The given length of matches must be %d.',
                self::MATCHES_LENGTH_DMS
            ));
        }

        if ($version === self::VERSION_V2) {
            $this->moveElementToEnd($matches, 1);
        }

        $degrees = (int)$matches[1];
        $minutes = (int)$matches[2];
        $seconds = $this->getFloatValueFromSimple([$matches[0], $matches[3], $matches[4]]);
        $direction = $matches[5];

        $check = match ($type) {
            self::TYPE_DIRECTION_LATITUDE => in_array($direction, self::DIRECTIONS_LATITUDE, true),
            self::TYPE_DIRECTION_LONGITUDE => in_array($direction, self::DIRECTIONS_LONGITUDE, true),
            default => false,
        };

        if ($check === false) {
            throw new CaseUnsupportedException(sprintf(
                'The given direction "%s" is not supported or does not match with the given type "%s".',
                $direction,
                $type
            ));
        }

        $decimalDegrees = $degrees + ($minutes / 60) + ($seconds / 3600);

        if (in_array($direction, [self::DIRECTION_LATITUDE_SOUTH, self::DIRECTION_LONGITUDE_WEST])) {
            $decimalDegrees *= -1;
        }

        return $decimalDegrees;
    }

    /**
     * Float value: Returns a float number from given integer and decimal part.
     *
     * @param array<int, string> $matches
     * @return float
     * @throws CaseUnsupportedException
     */
    protected function getFloatValueFromSimple(array $matches): float
    {
        if (count($matches) !== self::MATCHES_LENGTH_DECIMAL) {
            throw new CaseUnsupportedException(sprintf(
                'The given length of matches must be %d.',
                self::MATCHES_LENGTH_DECIMAL
            ));
        }

        $integerConverted = (float) $matches[1];
        $decimalsConverted = floatval(intval($matches[2]) * 10 ** (-strlen($matches[2])));

        $isNegative = $integerConverted < 0 || $matches[1] === '-0';

        return $integerConverted + ($isNegative ? -1 : 1) * $decimalsConverted;
    }

    /**
     * Gets the matches from redirect location header.
     *
     * @param array<int, string> $matches
     * @return string[]
     * @throws CaseUnsupportedException
     */
    protected function convertRedirectMatch(array $matches): array
    {
        if (count($matches) !== self::MATCHES_LENGTH_LINK) {
            throw new CaseUnsupportedException(sprintf(
                'The given length of matches must be %d.',
                self::MATCHES_LENGTH_LINK
            ));
        }

        $link = $matches[1];

        $curl = curl_init($link);

        if ($curl === false) {
            throw new CaseUnsupportedException(sprintf('Unable to initiate curl (%s:%d).', __FILE__, __LINE__));
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        $response = curl_exec($curl);

        if (is_bool($response)) {
            throw new CaseUnsupportedException(sprintf('Unable to exec curl (%s:%d).', __FILE__, __LINE__));
        }

        $headerSize = intval(curl_getinfo($curl, CURLINFO_HEADER_SIZE));

        $headerLines = substr($response, 0, $headerSize);

        /* Trys to get the url from location header. */
        $matchesLocation = [];
        if (!preg_match(self::REGEXP_GOOGLE_LOCATION_REDIRECT, $headerLines, $matchesLocation)) {
            throw new CaseUnsupportedException(sprintf('Unable to parse header from google link "%s".', ${$link}));
        }

        return $matchesLocation;
    }

    /**
     * Gets the matches from given timezone.
     *
     * @param array<int, string> $matches
     * @return string[]
     * @throws CaseUnsupportedException
     * @throws Exception
     */
    protected function convertTimezoneString(array $matches): array
    {
        if (count($matches) !== self::MATCHES_LENGTH_TIMEZONE) {
            print_r($matches);

            throw new CaseUnsupportedException(sprintf(
                'The given length of matches must be %d.',
                self::MATCHES_LENGTH_LINK
            ));
        }

        $timezoneString = $matches[0];

        $timezone = new DateTimeZone($timezoneString);

        $location = $timezone->getLocation();

        if ($location === false) {
            throw new CaseUnsupportedException(sprintf('Unable to parse timezone "%s".', $timezoneString));
        }

        $latitudeParts = explode('.', (string) $location['latitude']);
        $longitudeParts = explode('.', (string) $location['longitude']);

        if (count($latitudeParts) < self::MATCHES_LENGTH_TIMEZONE) {
            $latitudeParts[] = '0';
        }

        if (count($longitudeParts) < self::MATCHES_LENGTH_TIMEZONE) {
            $longitudeParts[] = '0';
        }

        return [
            $timezoneString,
            ...$latitudeParts,
            ...$longitudeParts
        ];
    }

    /**
     * @param array<int, string> $array
     * @param int $index
     * @return void
     */
    private function moveElementToEnd(array &$array, int $index): void
    {
        $element = $array[$index];
        unset($array[$index]);
        $array = array_values($array);
        $array[] = $element;
    }

    /**
     * @return bool
     */
    public function hasError(): bool
    {
        return !is_null($this->error);
    }

    /**
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * @param string $error
     * @return void
     */
    protected function setError(string $error): void
    {
        $this->error = $error;
    }

    /**
     * @return bool
     */
    public function isParsed(): bool
    {
        return !is_null($this->parsed);
    }

    /**
     * Returns the parsed value.
     *
     * @return float[]|false
     * @throws CaseUnsupportedException
     */
    public function getParsed(): array|false
    {
        if (is_null($this->parsed)) {
            throw new CaseUnsupportedException('Please execute the parse method before.');
        }

        return $this->parsed;
    }
}
