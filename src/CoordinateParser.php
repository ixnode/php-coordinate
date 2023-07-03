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

use Ixnode\PhpException\Case\CaseUnsupportedException;

/**
 * Class CoordinateParser
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-07-03)
 * @since 0.1.0 (2023-07-03) First version.
 */
class CoordinateParser
{
    final public const REGEXP_PARSER_GOOGLE_SPOT_LINK = '~!3d([0-9]+)\.([0-9]+)+.+!4d([0-9]+)\.([0-9]+)~';

    final public const REGEXP_PARSER_GOOGLE_REDIRECT_LINK = '~(https://maps.app.goo.gl/[a-zA-Z0-9]+)$~';

    final public const REGEXP_GOOGLE_LOCATION_REDIRECT = '~^location: .+!3d([0-9]+)\.([0-9]+)+.+!4d([0-9]+)\.([0-9]+).+~m';

    final public const REGEXP_PARSER_DECIMAL_DEGREES = '~(-?[0-9]+)[.]([0-9]+)[,: ]*(-?[0-9]+)[.]([0-9]+)~';

    private const MATCHES_LENGTH_COORDINATE = 5;

    private const MATCHES_LENGTH_LINK = 2;

    private ?string $error = null;

    private bool $parsed = false;

    /**
     * @param string $coordinate
     */
    public function __construct(protected string $coordinate)
    {
    }

    /**
     * Tries to parse the given coordinate string.
     *
     * @return float[]|false
     * @throws CaseUnsupportedException
     */
    public function doParse(): array|false
    {
        $matches = [];

        $point = match (true) {
            /* Try to parse Google Maps redirect link like https://maps.app.goo.gl/PHq5axBaDdgRWj4T6 */
            preg_match(self::REGEXP_PARSER_GOOGLE_REDIRECT_LINK, $this->coordinate, $matches) > 0 =>
            $this->getLatitudeAndLongitude(
                $this->convertRedirectMatch($matches)
            ),

            /* Try to parse copied Google Maps link from browser */
            preg_match(self::REGEXP_PARSER_GOOGLE_SPOT_LINK, $this->coordinate, $matches) > 0 =>
                $this->getLatitudeAndLongitude($matches),

            /* Try to parse decimal values like: "51.0504, 13.7373", "51.0504 13.7373", etc. */
            preg_match(self::REGEXP_PARSER_DECIMAL_DEGREES, $this->coordinate, $matches) > 0 =>
                $this->getLatitudeAndLongitude($matches),

            default => false,
        };

        if ($point === false) {
            $this->setError(sprintf('Unable to parse coordinate "%s".', $this->coordinate));
        }

        return $point;
    }

    /**
     * Gets the matches from redirect location header.
     *
     * @param array<int, string> $matches
     * @return string[]
     * @throws CaseUnsupportedException
     */
    private function convertRedirectMatch(array $matches): array
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
     * Returns the latitude and longitude values from given coordinate string.
     *
     * @param array<int, string> $matches
     * @return float[]
     * @throws CaseUnsupportedException
     */
    private function getLatitudeAndLongitude(array $matches): array
    {
        if (count($matches)!== self::MATCHES_LENGTH_COORDINATE) {
            throw new CaseUnsupportedException(sprintf(
                'The given length of matches must be %d.',
                self::MATCHES_LENGTH_COORDINATE
            ));
        }

        $this->parsed = true;

        return [
            $this->buildFloat($matches[1], $matches[2]),
            $this->buildFloat($matches[3], $matches[4]),
        ];
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
    private function setError(string $error): void
    {
        $this->error = $error;
    }

    /**
     * @return bool
     */
    public function isParsed(): bool
    {
        return $this->parsed;
    }
}
