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

namespace Ixnode\PhpCoordinate\Tests\Unit;

use Ixnode\PhpCoordinate\Base\BaseCoordinateValue;
use Ixnode\PhpCoordinate\Constants\Direction;
use Ixnode\PhpCoordinate\Coordinate;
use Ixnode\PhpException\Case\CaseUnsupportedException;
use Ixnode\PhpException\Parser\ParserException;
use PHPUnit\Framework\TestCase;

/**
 * Class CoordinateConverterTest
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-07-10)
 * @since 0.1.0 (2023-07-10) First version.
 * @link Coordinate
 */
final class CoordinateTest extends TestCase
{
    /**
     * Test wrapper.
     *
     * @dataProvider dataProviderDecimalDegreeGivenByFloatValues
     * @dataProvider dataProviderDecimalDegreeGivenByStringValues
     * @dataProvider dataProviderDecimalDegreeGivenBySingleString
     * @dataProvider dataProviderGetLatitudeDMSv1
     * @dataProvider dataProviderGetLatitudeDMSv2
     * @dataProvider dataProviderGetDistance
     * @dataProvider dataProviderGetDegree
     * @dataProvider dataProviderGetDirection
     *
     * @test
     * @testdox $number) Test Coordinate: $method from "$given"
     * @param int $number
     * @param string $method
     * @param string|Coordinate|null $parameter1
     * @param string|null $parameter2
     * @param string|float $given1
     * @param string|float|null $given2
     * @param float|string $expected
     * @throws CaseUnsupportedException
     * @throws ParserException
     */
    public function wrapper(
        int $number,
        string $method,
        string|Coordinate|null $parameter1,
        string|null $parameter2,
        string|float $given1,
        string|float|null $given2,
        float|string $expected
    ): void
    {
        /* Arrange */

        /* Act */
        $coordinate = match (true) {
            !is_null($given2) => new Coordinate($given1, $given2),
            default => new Coordinate($given1),
        };
        $callback = [$coordinate, $method];

        /* Assert */
        $this->assertIsNumeric($number); // To avoid phpmd warning.
        $this->assertContains($method, get_class_methods(Coordinate::class));
        $this->assertIsCallable($callback);

        $result = match (true) {
            !is_null($parameter2) => $coordinate->{$method}($parameter1, $parameter2),
            !is_null($parameter1) => $coordinate->{$method}($parameter1),
            default => $coordinate->{$method}(),
        };

        $this->assertSame($expected, $result);
    }

    /**
     * Data provider (decimal degree given by float values).
     *
     * @return array<int, array<int, string|int|float|null>>
     */
    public function dataProviderDecimalDegreeGivenByFloatValues(): array
    {
        $number = 0;

        return [

            /**
             * getLatitude (decimal degree given by float values)
             */
            [++$number, 'getLatitude', null, null, 51.0504, 13.7373, 51.0504], // Dresden, Germany
            [++$number, 'getLatitude', null, null, -33.940525, 18.414006, -33.940525], // Kapstadt, South Africa
            [++$number, 'getLatitude', null, null, 40.690069, -74.045508, 40.690069], // New York, United States
            [++$number, 'getLatitude', null, null, -31.425299, -64.201743, -31.425299], // Córdoba, Argentina

            /**
             * getLongitude (decimal degree given by float values)
             */
            [++$number, 'getLongitude', null, null, 51.0504, 13.7373, 13.7373], // Dresden, Germany
            [++$number, 'getLongitude', null, null, -33.940525, 18.414006, 18.414006], // Kapstadt, South Africa
            [++$number, 'getLongitude', null, null, 40.690069, -74.045508, -74.045508], // New York, United States
            [++$number, 'getLongitude', null, null, -31.425299, -64.201743, -64.201743], // Córdoba, Argentina

        ];
    }

    /**
     * Data provider (decimal degree given by string values).
     *
     * @return array<int, array<int, string|int|float|null>>
     */
    public function dataProviderDecimalDegreeGivenByStringValues(): array
    {
        $number = 0;

        return [

            /**
             * getLatitude (decimal degree given by string values)
             */
            [++$number, 'getLatitude', null, null, '51.0504', '13.7373', 51.0504], // Dresden, Germany
            [++$number, 'getLatitude', null, null, '-33.940525', '18.414006', -33.940525], // Kapstadt, South Africa
            [++$number, 'getLatitude', null, null, 40.690069, '-74.045508', 40.690069], // New York, United States
            [++$number, 'getLatitude', null, null, '-31.425299', -64.201743, -31.425299], // Córdoba, Argentina

            /**
             * getLongitude (decimal degree given by string values)
             */
            [++$number, 'getLongitude', null, null, '51.0504', '13.7373', 13.7373], // Dresden, Germany
            [++$number, 'getLongitude', null, null, '-33.940525', '18.414006', 18.414006], // Kapstadt, South Africa
            [++$number, 'getLongitude', null, null, 40.690069, '-74.045508', -74.045508], // New York, United States
            [++$number, 'getLongitude', null, null, '-31.425299', -64.201743, -64.201743], // Córdoba, Argentina

            /**
             * getLatitude (decimal degree given by dms string values)
             */
            [++$number, 'getLatitude', null, null, '51°3′1.44″N', '13°44′14.28″E', 51.0504], // Dresden, Germany
            [++$number, 'getLatitude', null, null, '33°56′25.89″S', '18°24′50.42″E', -33.940525], // Kapstadt, South Africa
            [++$number, 'getLatitude', null, null, '40°41′24.2484″N', '74°2′43.8288″W', 40.690069], // New York, United States
            [++$number, 'getLatitude', null, null, '31°25′31.0764″S', '64°12′6.2748″W', -31.425299], // Córdoba, Argentina

            /**
             * getLatitude (decimal degree given by dms string values)
             */
            [++$number, 'getLongitude', null, null, '51°3′1.44″N', '13°44′14.28″E', 13.7373], // Dresden, Germany
            [++$number, 'getLongitude', null, null, '33°56′25.89″S', '18°24′50.42″E', 18.414006], // Kapstadt, South Africa
            [++$number, 'getLongitude', null, null, '40°41′24.2484″N', '74°2′43.8288″W', -74.045508], // New York, United States
            [++$number, 'getLongitude', null, null, '31°25′31.0764″S', '64°12′6.2748″W', -64.201743], // Córdoba, Argentina

        ];
    }

    /**
     * Data provider (decimal degree given by string values).
     *
     * @return array<int, array<int, string|int|float|null>>
     */
    public function dataProviderDecimalDegreeGivenBySingleString(): array
    {
        $number = 0;

        return [

            /**
             * getLatitude (decimal degree given by single string)
             */
            [++$number, 'getLatitude', null, null, '51.0504, 13.7373', null, 51.0504], // Dresden, Germany
            [++$number, 'getLatitude', null, null, '-33.940525, 18.414006', null, -33.940525], // Kapstadt, South Africa
            [++$number, 'getLatitude', null, null, '40.690069, -74.045508', null, 40.690069], // New York, United States
            [++$number, 'getLatitude', null, null, '-31.425299, -64.201743', null, -31.425299], // Córdoba, Argentina

            /**
             * getLongitude (decimal degree given by single string)
             */
            [++$number, 'getLongitude', null, null, '51.0504, 13.7373', null, 13.7373], // Dresden, Germany
            [++$number, 'getLongitude', null, null, '-33.940525, 18.414006', null, 18.414006], // Kapstadt, South Africa
            [++$number, 'getLongitude', null, null, '40.690069, -74.045508', null, -74.045508], // New York, United States
            [++$number, 'getLongitude', null, null, '-31.425299, -64.201743', null, -64.201743], // Córdoba, Argentina



            /**
             * getLatitude (decimal degree given by single dms string)
             */
            [++$number, 'getLatitude', null, null, '51°3′1.44″N, 13°44′14.28″E', null, 51.0504], // Dresden, Germany
            [++$number, 'getLatitude', null, null, '33°56′25.89″S, 18°24′50.42″E', null, -33.940525], // Kapstadt, South Africa
            [++$number, 'getLatitude', null, null, '40°41′24.2484″N, 74°2′43.8288″W', null, 40.690069], // New York, United States
            [++$number, 'getLatitude', null, null, '31°25′31.0764″S, 64°12′6.2748″W', null, -31.425299], // Córdoba, Argentina

            /**
             * getLatitude (decimal degree given by single dms string)
             */
            [++$number, 'getLongitude', null, null, '51°3′1.44″N, 13°44′14.28″E', null, 13.7373], // Dresden, Germany
            [++$number, 'getLongitude', null, null, '33°56′25.89″S, 18°24′50.42″E', null, 18.414006], // Kapstadt, South Africa
            [++$number, 'getLongitude', null, null, '40°41′24.2484″N, 74°2′43.8288″W', null, -74.045508], // New York, United States
            [++$number, 'getLongitude', null, null, '31°25′31.0764″S, 64°12′6.2748″W', null, -64.201743], // Córdoba, Argentina



            /**
             * getLatitudeDD (decimal degree given by single string - getLatitudeDecimal)
             */
            [++$number, 'getLatitudeDecimal', null, null, '51.0504, 13.7373', null, 51.0504], // Dresden, Germany
            [++$number, 'getLatitudeDecimal', null, null, '-33.940525, 18.414006', null, -33.940525], // Kapstadt, South Africa
            [++$number, 'getLatitudeDecimal', null, null, '40.690069, -74.045508', null, 40.690069], // New York, United States
            [++$number, 'getLatitudeDecimal', null, null, '-31.425299, -64.201743', null, -31.425299], // Córdoba, Argentina

            /**
             * getLongitudeDD (decimal degree given by single string - getLongitudeDecimal)
             */
            [++$number, 'getLongitudeDecimal', null, null, '51.0504, 13.7373', null, 13.7373], // Dresden, Germany
            [++$number, 'getLongitudeDecimal', null, null, '-33.940525, 18.414006', null, 18.414006], // Kapstadt, South Africa
            [++$number, 'getLongitudeDecimal', null, null, '40.690069, -74.045508', null, -74.045508], // New York, United States
            [++$number, 'getLongitudeDecimal', null, null, '-31.425299, -64.201743', null, -64.201743], // Córdoba, Argentina

        ];
    }

    /**
     * Data provider (test getLatitudeDMS method - v1).
     *
     * @return array<int, array<int, string|int|float|null>>
     */
    public function dataProviderGetLatitudeDMSv1(): array
    {
        $number = 0;

        return [

            /**
             * getLatitudeDMS v1
             */
            [++$number, 'getLatitudeDMS', null, null, '51.0504, 13.7373', null, '51°3′1.44″N'], // Dresden, Germany
            [++$number, 'getLatitudeDMS', null, null, '-33.940525, 18.414006', null, '33°56′25.89″S'], // Kapstadt, South Africa
            [++$number, 'getLatitudeDMS', null, null, '40.690069, -74.045508', null, '40°41′24.2484″N'], // New York, United States
            [++$number, 'getLatitudeDMS', null, null, '-31.425299, -64.201743', null, '31°25′31.0764″S'], // Córdoba, Argentina

            /**
             * getLongitudeDMS v1
             */
            [++$number, 'getLongitudeDMS', null, null, '51.0504, 13.7373', null, '13°44′14.28″E'], // Dresden, Germany
            [++$number, 'getLongitudeDMS', null, null, '-33.940525, 18.414006', null, '18°24′50.4216″E'], // Kapstadt, South Africa
            [++$number, 'getLongitudeDMS', null, null, '40.690069, -74.045508', null, '74°2′43.8288″W'], // New York, United States
            [++$number, 'getLongitudeDMS', null, null, '-31.425299, -64.201743', null, '64°12′6.2748″W'], // Córdoba, Argentina

        ];
    }

    /**
     * Data provider (test getLatitudeDMS method - v2).
     *
     * @return array<int, array<int, string|int|float|null>>
     */
    public function dataProviderGetLatitudeDMSv2(): array
    {
        $number = 0;

        return [

            /**
             * getLatitudeDMS v2 (converter check)
             */
            [++$number, 'getLatitudeDMS', BaseCoordinateValue::FORMAT_DMS_SHORT_2, null, '51.0504, 13.7373', null, 'N51°3′1.44″'], // Dresden, Germany
            [++$number, 'getLatitudeDMS', BaseCoordinateValue::FORMAT_DMS_SHORT_2, null, '-33.940525, 18.414006', null, 'S33°56′25.89″'], // Kapstadt, South Africa
            [++$number, 'getLatitudeDMS', BaseCoordinateValue::FORMAT_DMS_SHORT_2, null, '40.690069, -74.045508', null, 'N40°41′24.2484″'], // New York, United States
            [++$number, 'getLatitudeDMS', BaseCoordinateValue::FORMAT_DMS_SHORT_2, null, '-31.425299, -64.201743', null, 'S31°25′31.0764″'], // Córdoba, Argentina

            /**
             * getLongitudeDMS v2 (converter check)
             */
            [++$number, 'getLongitudeDMS', BaseCoordinateValue::FORMAT_DMS_SHORT_2, null, '51.0504, 13.7373', null, 'E13°44′14.28″'], // Dresden, Germany
            [++$number, 'getLongitudeDMS', BaseCoordinateValue::FORMAT_DMS_SHORT_2, null, '-33.940525, 18.414006', null, 'E18°24′50.4216″'], // Kapstadt, South Africa
            [++$number, 'getLongitudeDMS', BaseCoordinateValue::FORMAT_DMS_SHORT_2, null, '40.690069, -74.045508', null, 'W74°2′43.8288″'], // New York, United States
            [++$number, 'getLongitudeDMS', BaseCoordinateValue::FORMAT_DMS_SHORT_2, null, '-31.425299, -64.201743', null, 'W64°12′6.2748″'], // Córdoba, Argentina

        ];
    }

    /**
     * Data provider (test getDistance method).
     *
     * @return array<int, array<int, string|int|float|Coordinate|null>>
     * @throws CaseUnsupportedException
     * @throws ParserException
     */
    public function dataProviderGetDistance(): array
    {
        $number = 0;

        return [

            /**
             * getLatitudeDMS v2 (converter check)
             */
            [++$number, 'getDistance', new Coordinate(51.0504, 13.7373), null, 51.0504, 13.7373, .0], // Dresden, Germany
            [++$number, 'getDistance', new Coordinate(51.0504, 13.7373), null, -33.940525, 18.414006, 9_461_663.6], // Kapstadt, South Africa
            [++$number, 'getDistance', new Coordinate(51.0504, 13.7373), null, 40.690069, -74.045508, 6_482_638.0], // New York, United States
            [++$number, 'getDistance', new Coordinate(51.0504, 13.7373), null, -31.425299, -64.201743, 11_904_668.4], // Córdoba, Argentina

            /**
             * getLatitudeDMS v2 (converter check)
             */
            [++$number, 'getDistance', new Coordinate(51.0504, 13.7373), Coordinate::RETURN_KILOMETERS, 51.0504, 13.7373, .0], // Dresden, Germany
            [++$number, 'getDistance', new Coordinate(51.0504, 13.7373), Coordinate::RETURN_KILOMETERS, -33.940525, 18.414006, 9461.664], // Kapstadt, South Africa
            [++$number, 'getDistance', new Coordinate(51.0504, 13.7373), Coordinate::RETURN_KILOMETERS, 40.690069, -74.045508, 6482.638], // New York, United States
            [++$number, 'getDistance', new Coordinate(51.0504, 13.7373), Coordinate::RETURN_KILOMETERS, -31.425299, -64.201743, 11904.668], // Córdoba, Argentina

        ];
    }

    /**
     * Data provider (test getDegree method).
     *
     * @return array<int, array<int, string|int|float|Coordinate|null>>
     * @throws CaseUnsupportedException
     * @throws ParserException
     */
    public function dataProviderGetDegree(): array
    {
        $number = 0;

        return [

            /**
             * getDegree (converter check)
             */
            [++$number, 'getDegree', new Coordinate(51.0504, 13.7373), null, 51.0504, 13.7373, .0], // Dresden, Germany
            [++$number, 'getDegree', new Coordinate(-33.940525, 18.414006), null, 51.0504, 13.7373, 176.85], // Kapstadt, South Africa
            [++$number, 'getDegree', new Coordinate(40.690069, -74.045508), null, 51.0504, 13.7373, -96.73], // New York, United States
            [++$number, 'getDegree', new Coordinate(-31.425299, -64.201743), null, 51.0504, 13.7373, -136.62], // Córdoba, Argentina

        ];
    }

    /**
     * Data provider (test getDegree method).
     *
     * @return array<int, array<int, string|int|float|Coordinate|null>>
     * @throws CaseUnsupportedException
     * @throws ParserException
     */
    public function dataProviderGetDirection(): array
    {
        $number = 0;

        return [

            /**
             * getDirection (converter check)
             */
            [++$number, 'getDirection', new Coordinate(51.0504, 13.7373), null, 51.0504, 13.7373, Direction::DIRECTION_NORTH], // Dresden, Germany
            [++$number, 'getDirection', new Coordinate(-33.940525, 18.414006), null, 51.0504, 13.7373, Direction::DIRECTION_SOUTH], // Kapstadt, South Africa
            [++$number, 'getDirection', new Coordinate(40.690069, -74.045508), null, 51.0504, 13.7373, Direction::DIRECTION_WEST], // New York, United States
            [++$number, 'getDirection', new Coordinate(-31.425299, -64.201743), null, 51.0504, 13.7373, Direction::DIRECTION_SOUTH_WEST], // Córdoba, Argentina

        ];
    }
}
