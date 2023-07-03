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
             * getLatitude (parser check)
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
             * getLongitude (parser check)
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
             * getLatitudeDMS (converter check)
             */
            [++$number, 'getLatitudeDMS', null, '51.0504, 13.7373', '51°3′1.44″N'], // Dresden, Germany
            [++$number, 'getLatitudeDMS', null, '-33.940525, 18.414006', '33°56′25.89″S'], // Kapstadt, South Africa
            [++$number, 'getLatitudeDMS', null, '40.690069, -74.045508', '40°41′24.2484″N'], // New York, United States
            [++$number, 'getLatitudeDMS', null, '-31.425299, -64.201743', '31°25′31.0764″S'], // Córdoba, Argentina

            /**
             * getLongitudeDMS (converter check)
             */
            [++$number, 'getLongitudeDMS', null, '51.0504, 13.7373', '13°44′14.28″E'], // Dresden, Germany
            [++$number, 'getLongitudeDMS', null, '-33.940525, 18.414006', '18°24′50.4216″E'], // Kapstadt, South Africa
            [++$number, 'getLongitudeDMS', null, '40.690069, -74.045508', '74°2′43.8288″W'], // New York, United States
            [++$number, 'getLongitudeDMS', null, '-31.425299, -64.201743', '64°12′6.2748″W'], // Córdoba, Argentina

        ];
    }
}
