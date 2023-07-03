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

use Ixnode\PhpCoordinate\Coordinate;
use Ixnode\PhpCoordinate\CoordinateValue;
use Ixnode\PhpException\Case\CaseUnsupportedException;
use PHPUnit\Framework\TestCase;

/**
 * Class CoordinateTest
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-07-03)
 * @since 0.1.0 (2023-07-03) First version.
 * @link Coordinate
 */
final class CoordinateTest extends TestCase
{
    private const GOOGLE_MAPS_SPOT_LINK_1 = 'https://www.google.com/maps/place/V%C3%B6lkerschlachtdenkmal,+04277+Leipzig/@51.3123709,12.4132924,17z/data=!3m1!4b1!4m6!3m5!1s0x47a6f9a9d013ca23:0x277b49a142da988c!8m2!3d51.3123709!4d12.4132924!16s%2Fg%2F12ls2f87w?entry=ttu';
    private const GOOGLE_MAPS_REDIRECT_LINK_1 = 'https://maps.app.goo.gl/PHq5axBaDdgRWj4T6';

    /**
     * Test wrapper.
     *
     * @dataProvider dataProvider
     *
     * @test
     * @testdox $number) Test Coordinate: $method
     * @param int $number
     * @param string $method
     * @param string|null $parameter
     * @param string $given
     * @param float|string $expected
     * @throws CaseUnsupportedException
     */
    public function wrapper(int $number, string $method, string|null $parameter, string $given, float|string $expected): void
    {
        /* Arrange */

        /* Act */
        $coordinate = new Coordinate($given);
        $callback = [$coordinate, $method];

        /* Assert */
        $this->assertIsNumeric($number); // To avoid phpmd warning.
        $this->assertContains($method, get_class_methods(Coordinate::class));
        $this->assertIsCallable($callback);

        $result = match (true) {
            is_null($parameter) => $coordinate->{$method}(),
            default => $coordinate->{$method}($parameter),
        };

        $this->assertSame($expected, $result);
    }

    /**
     * Data provider.
     *
     * @return array<int, array<int, string|int|float|null>>
     */
    public function dataProvider(): array
    {
        $number = 0;

        return [

            /**
             * basic: getLatitude (parser check)
             */
            [++$number, 'getLatitude', null, '51.0504,13.7373', 51.0504], // Dresden, Germany
            [++$number, 'getLatitude', null, '51.0504, 13.7373', 51.0504], // Dresden, Germany
            [++$number, 'getLatitude', null, '51.0504,  13.7373', 51.0504], // Dresden, Germany
            [++$number, 'getLatitude', null, '51.0504 13.7373', 51.0504], // Dresden, Germany
            [++$number, 'getLatitude', null, '51.0504:13.7373', 51.0504], // Dresden, Germany
            [++$number, 'getLatitude', null, 'POINT(51.0504,13.7373)', 51.0504], // Dresden, Germany
            [++$number, 'getLatitude', null, 'POINT(51.0504, 13.7373)', 51.0504], // Dresden, Germany
            [++$number, 'getLatitude', null, 'POINT(51.0504,  13.7373)', 51.0504], // Dresden, Germany
            [++$number, 'getLatitude', null, 'POINT(51.0504 13.7373)', 51.0504], // Dresden, Germany
            [++$number, 'getLatitude', null, '-33.940525, 18.414006', -33.940525], // Kapstadt, South Africa
            [++$number, 'getLatitude', null, '40.690069, -74.045508', 40.690069], // New York, United States
            [++$number, 'getLatitude', null, '-31.425299, -64.201743', -31.425299], // Córdoba, Argentina

            /**
             * complex: getLatitude (parser check)
             */
            [++$number, 'getLatitude', null, self::GOOGLE_MAPS_SPOT_LINK_1, 51.31237], // Leipzig, Germany
            [++$number, 'getLatitude', null, self::GOOGLE_MAPS_REDIRECT_LINK_1, 54.07304829999999], // Malbork, Poland

            /**
             * basic: getLongitude (parser check)
             */
            [++$number, 'getLongitude', null, '51.0504,13.7373', 13.7373], // Dresden, Germany
            [++$number, 'getLongitude', null, '51.0504, 13.7373', 13.7373], // Dresden, Germany
            [++$number, 'getLongitude', null, '51.0504,  13.7373', 13.7373], // Dresden, Germany
            [++$number, 'getLongitude', null, '51.0504 13.7373', 13.7373], // Dresden, Germany
            [++$number, 'getLongitude', null, '51.0504:13.7373', 13.7373], // Dresden, Germany
            [++$number, 'getLongitude', null, 'POINT(51.0504,13.7373)', 13.7373], // Dresden, Germany
            [++$number, 'getLongitude', null, 'POINT(51.0504, 13.7373)', 13.7373], // Dresden, Germany
            [++$number, 'getLongitude', null, 'POINT(51.0504,  13.7373)', 13.7373], // Dresden, Germany
            [++$number, 'getLongitude', null, 'POINT(51.0504 13.7373)', 13.7373], // Dresden, Germany
            [++$number, 'getLongitude', null, '-33.940525, 18.414006', 18.414006], // Kapstadt, South Africa
            [++$number, 'getLongitude', null, '40.690069, -74.045508', -74.045508], // New York, United States
            [++$number, 'getLongitude', null, '-31.425299, -64.201743', -64.201743], // Córdoba, Argentina

            /**
             * complex: getLongitude (parser check)
             */
            [++$number, 'getLongitude', null, self::GOOGLE_MAPS_SPOT_LINK_1, 12.4132924], // Leipzig, Germany
            [++$number, 'getLongitude', null, self::GOOGLE_MAPS_REDIRECT_LINK_1, 18.992402], // Malbork, Poland

            /**
             * getLatitudeDMS v1 (converter check)
             */
            [++$number, 'getLatitudeDMS', null, '51.0504, 13.7373', '51°3′1.44″N'], // Dresden, Germany
            [++$number, 'getLatitudeDMS', null, '-33.940525, 18.414006', '33°56′25.89″S'], // Kapstadt, South Africa
            [++$number, 'getLatitudeDMS', null, '40.690069, -74.045508', '40°41′24.2484″N'], // New York, United States
            [++$number, 'getLatitudeDMS', null, '-31.425299, -64.201743', '31°25′31.0764″S'], // Córdoba, Argentina

            /**
             * getLongitudeDMS v1 (converter check)
             */
            [++$number, 'getLongitudeDMS', null, '51.0504, 13.7373', '13°44′14.28″E'], // Dresden, Germany
            [++$number, 'getLongitudeDMS', null, '-33.940525, 18.414006', '18°24′50.4216″E'], // Kapstadt, South Africa
            [++$number, 'getLongitudeDMS', null, '40.690069, -74.045508', '74°2′43.8288″W'], // New York, United States
            [++$number, 'getLongitudeDMS', null, '-31.425299, -64.201743', '64°12′6.2748″W'], // Córdoba, Argentina

            /**
             * getLatitudeDMS v2 (converter check)
             */
            [++$number, 'getLatitudeDMS', CoordinateValue::FORMAT_DMS_SHORT_2, '51.0504, 13.7373', 'N51°3′1.44″'], // Dresden, Germany
            [++$number, 'getLatitudeDMS', CoordinateValue::FORMAT_DMS_SHORT_2, '-33.940525, 18.414006', 'S33°56′25.89″'], // Kapstadt, South Africa
            [++$number, 'getLatitudeDMS', CoordinateValue::FORMAT_DMS_SHORT_2, '40.690069, -74.045508', 'N40°41′24.2484″'], // New York, United States
            [++$number, 'getLatitudeDMS', CoordinateValue::FORMAT_DMS_SHORT_2, '-31.425299, -64.201743', 'S31°25′31.0764″'], // Córdoba, Argentina

            /**
             * getLongitudeDMS v2 (converter check)
             */
            [++$number, 'getLongitudeDMS', CoordinateValue::FORMAT_DMS_SHORT_2, '51.0504, 13.7373', 'E13°44′14.28″'], // Dresden, Germany
            [++$number, 'getLongitudeDMS', CoordinateValue::FORMAT_DMS_SHORT_2, '-33.940525, 18.414006', 'E18°24′50.4216″'], // Kapstadt, South Africa
            [++$number, 'getLongitudeDMS', CoordinateValue::FORMAT_DMS_SHORT_2, '40.690069, -74.045508', 'W74°2′43.8288″'], // New York, United States
            [++$number, 'getLongitudeDMS', CoordinateValue::FORMAT_DMS_SHORT_2, '-31.425299, -64.201743', 'W64°12′6.2748″'], // Córdoba, Argentina

        ];
    }
}
