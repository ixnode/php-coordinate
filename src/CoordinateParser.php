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

use Ixnode\PhpCoordinate\Base\BaseCoordinateParser;
use Ixnode\PhpCoordinate\Tests\Unit\CoordinateParserTest;
use Ixnode\PhpException\Case\CaseUnsupportedException;

/**
 * Class CoordinateParser
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-07-03)
 * @since 0.1.0 (2023-07-03) First version.
 * @link CoordinateParserTest
 */
class CoordinateParser extends BaseCoordinateParser
{
    /**
     * Parser: Tries to parse the given coordinate string (coordinate, link, etc.).
     *
     * @return float[]|false
     * @throws CaseUnsupportedException
     */
    public function parse(): array|false
    {
        $matches = [];

        return match (true) {
            /* Special parser: Try to parse Google Maps redirect link like https://maps.app.goo.gl/PHq5axBaDdgRWj4T6 */
            preg_match(self::REGEXP_PARSER_GOOGLE_REDIRECT_LINK, $this->coordinate, $matches) > 0 =>
                $this->getPointFromDecimalDegree($this->convertRedirectMatch($matches)),

            /* Special parser: Try to parse copied Google Maps link from browser */
            preg_match(self::REGEXP_PARSER_GOOGLE_SPOT_LINK, $this->coordinate, $matches) > 0 =>
                $this->getPointFromDecimalDegree($matches),

            default => $this->getPointFromCoordinate(),
        };
    }

    /**
     * Point: Tries to parse the given coordinate string (coordinate).
     *
     * @return float[]|false
     * @throws CaseUnsupportedException
     */
    private function getPointFromCoordinate(): array|false
    {
        $split = preg_split(self::REGEXP_SPLIT, $this->coordinate);

        if ($split === false) {
            $this->setError(sprintf('Unable to parse coordinate "%s".', $this->coordinate));
            return false;
        }

        if (count($split) !== self::MATCHES_LENGTH_POINTS) {
            $this->setError(sprintf('Unable to parse coordinate "%s".', $this->coordinate));
            return false;
        }

        $point = [];
        foreach ($split as $key => $value) {
            $type = $key === 0 ? self::TYPE_DIRECTION_LATITUDE : self::TYPE_DIRECTION_LONGITUDE;
            $matches = [];
            $pointValue = match (true) {
                /* Try to parse DMS values like (v1): "51°3′1.44″N, 13°44′14.28″E", etc. */
                preg_match(self::REGEXP_PARSER_DMS_V1, $value, $matches) > 0 =>
                    $this->getFloatValueFromDMS($matches, $type),

                /* Try to parse DMS values like (v1): "N51°3′1.44″, E13°44′14.28″", etc. */
                preg_match(self::REGEXP_PARSER_DMS_V2, $value, $matches) > 0 =>
                    $this->getFloatValueFromDMS($matches, $type, self::VERSION_V2),

                /* Try to parse decimal values like: "51.0504", "13.7373", etc. */
                preg_match(self::REGEXP_PARSER_DECIMAL_DEGREE, $value, $matches) > 0 =>
                    $this->getFloatValueFromSimple($matches),

                /* Unable to parse the given values. */
                default => false,
            };

            if ($pointValue === false) {
                $this->setError(sprintf('Unable to parse coordinate "%s" (%s).', $this->coordinate, $value));
                return false;
            }

            $point[] = round($pointValue, self::DECIMAL_PRECISION);
        }

        $this->parsed = true;

        return $point;
    }
}
