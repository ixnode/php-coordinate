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

use Ixnode\PhpCoordinate\CoordinateParser;
use Ixnode\PhpException\Case\CaseUnsupportedException;
use PHPUnit\Framework\TestCase;

/**
 * Class CoordinateTest
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-07-03)
 * @since 0.1.0 (2023-07-03) First version.
 * @link CoordinateParser
 */
final class CoordinateParserTest extends TestCase
{
    private const GOOGLE_MAPS_SPOT_LINK_1 = 'https://www.google.com/maps/place/V%C3%B6lkerschlachtdenkmal,+04277+Leipzig/@51.3123709,12.4132924,17z/data=!3m1!4b1!4m6!3m5!1s0x47a6f9a9d013ca23:0x277b49a142da988c!8m2!3d51.3123709!4d12.4132924!16s%2Fg%2F12ls2f87w?entry=ttu';
    private const GOOGLE_MAPS_REDIRECT_LINK_1 = 'https://maps.app.goo.gl/PHq5axBaDdgRWj4T6';

    /**
     * Test wrapper.
     *
     * @dataProvider dataProvider
     *
     * @test
     * @testdox $number) Test Coordinate: $method from "$given"
     * @param int $number
     * @param string $given
     * @param array<int, float> $expected
     * @throws CaseUnsupportedException
     */
    public function wrapper(int $number, string $given, array $expected): void
    {
        /* Arrange */

        /* Act */
        $coordinateParser = new CoordinateParser($given);
        $parsed = $coordinateParser->getParsed();

        /* Assert */
        $this->assertIsNumeric($number); // To avoid phpmd warning.
        $this->assertSame($expected, $parsed);
    }

    /**
     * Data provider.
     *
     * @return array<int, array<int, string|int|float|null|array<int, float>>>
     */
    public function dataProvider(): array
    {
        $number = 0;

        return [

            /**
             * basic decimal degree tests
             */
            [++$number, '51.0504,13.7373', [51.0504, 13.7373]], // Dresden, Germany
            [++$number, '51,0504,13,7373', [51.0504, 13.7373]], // Dresden, Germany
            [++$number, '51.0504, 13.7373', [51.0504, 13.7373]], // Dresden, Germany
            [++$number, '51.0504,  13.7373', [51.0504, 13.7373]], // Dresden, Germany
            [++$number, '51.0504,  13.7373 ', [51.0504, 13.7373]], // Dresden, Germany
            [++$number, ' 51.0504,  13.7373', [51.0504, 13.7373]], // Dresden, Germany
            [++$number, ' 51,0504,  13,7373', [51.0504, 13.7373]], // Dresden, Germany
            [++$number, '    51.0504,  13.7373      ', [51.0504, 13.7373]], // Dresden, Germany
            [++$number, '51.0504 13.7373', [51.0504, 13.7373]], // Dresden, Germany
            [++$number, '51.0504:13.7373', [51.0504, 13.7373]], // Dresden, Germany
            [++$number, 'POINT(51.0504,13.7373)', [51.0504, 13.7373]], // Dresden, Germany
            [++$number, 'POINT(51.0504, 13.7373)', [51.0504, 13.7373]], // Dresden, Germany
            [++$number, 'POINT(51.0504,  13.7373)', [51.0504, 13.7373]], // Dresden, Germany
            [++$number, 'POINT(51.0504 13.7373)', [51.0504, 13.7373]], // Dresden, Germany
            [++$number, '-33.940525, 18.414006', [-33.940525, 18.414006]], // Kapstadt, South Africa
            [++$number, '-33,940525, 18,414006', [-33.940525, 18.414006]], // Kapstadt, South Africa
            [++$number, '40.690069, -74.045508', [40.690069, -74.045508]], // New York, United States
            [++$number, '-31.425299, -64.201743', [-31.425299, -64.201743]], // Córdoba, Argentina
            [++$number, '-31,425299, -64,201743', [-31.425299, -64.201743]], // Córdoba, Argentina

            /**
             * basic DMS tests (v1)
             */
            [++$number, '51°3′1.44″N,13°44′14.28″E', [51.0504, 13.7373]], // Dresden, Germany
            [++$number, '51°3′1.44″N, 13°44′14.28″E', [51.0504, 13.7373]], // Dresden, Germany
            [++$number, '51°3′1.44″N,  13°44′14.28″E', [51.0504, 13.7373]], // Dresden, Germany
            [++$number, '51°3′1.44″N 13°44′14.28″E', [51.0504, 13.7373]], // Dresden, Germany
            [++$number, '51°3′1.44″N:13°44′14.28″E', [51.0504, 13.7373]], // Dresden, Germany
            [++$number, 'POINT(51°3′1.44″N,13°44′14.28″E)', [51.0504, 13.7373]], // Dresden, Germany
            [++$number, 'POINT(51°3′1.44″N, 13°44′14.28″E)', [51.0504, 13.7373]], // Dresden, Germany
            [++$number, 'POINT(51°3′1.44″N,  13°44′14.28″E)', [51.0504, 13.7373]], // Dresden, Germany
            [++$number, 'POINT(51°3′1.44″N:13°44′14.28″E)', [51.0504, 13.7373]], // Dresden, Germany
            [++$number, '33°56′25.89″S, 18°24′50.42″E', [-33.940525, 18.414006]], // Kapstadt, South Africa
            [++$number, '40°41′24.2484″N, 74°2′43.8288″W', [40.690069, -74.045508]], // New York, United States
            [++$number, '31°25′31.0764″S, 64°12′6.2748″W', [-31.425299, -64.201743]], // Córdoba, Argentina

            /**
             * basic DMS tests (v2)
             */
            [++$number, 'N51°3′1.44″,E13°44′14.28″', [51.0504, 13.7373]], // Dresden, Germany
            [++$number, '51°3′1.44″N, 13°44′14.28″E', [51.0504, 13.7373]], // Dresden, Germany
            [++$number, '51°3′1.44″N,  13°44′14.28″E', [51.0504, 13.7373]], // Dresden, Germany
            [++$number, '51°3′1.44″N 13°44′14.28″E', [51.0504, 13.7373]], // Dresden, Germany
            [++$number, '51°3′1.44″N:13°44′14.28″E', [51.0504, 13.7373]], // Dresden, Germany
            [++$number, 'POINT(51°3′1.44″N,13°44′14.28″E)', [51.0504, 13.7373]], // Dresden, Germany
            [++$number, 'POINT(51°3′1.44″N, 13°44′14.28″E)', [51.0504, 13.7373]], // Dresden, Germany
            [++$number, 'POINT(51°3′1.44″N,  13°44′14.28″E)', [51.0504, 13.7373]], // Dresden, Germany
            [++$number, 'POINT(51°3′1.44″N:13°44′14.28″E)', [51.0504, 13.7373]], // Dresden, Germany
            [++$number, '33°56′25.89″S, 18°24′50.42″E', [-33.940525, 18.414006]], // Kapstadt, South Africa
            [++$number, '40°41′24.2484″N, 74°2′43.8288″W', [40.690069, -74.045508]], // New York, United States
            [++$number, '31°25′31.0764″S, 64°12′6.2748″W', [-31.425299, -64.201743]], // Córdoba, Argentina

            /**
             * complex link tests (Google Maps Links)
             */
            [++$number, self::GOOGLE_MAPS_SPOT_LINK_1, [51.31237, 12.413292]], // Leipzig, Germany
            [++$number, self::GOOGLE_MAPS_REDIRECT_LINK_1, [54.073048, 18.992402]], // Malbork, Poland

            /**
             * datetime parser tests
             */
            [++$number, 'Europe/Berlin', [52.5, 13.36666]], // Berlin, Germany
            [++$number, 'Europe/Oslo', [59.91666, 10.75]], // Oslo, Norway
            [++$number, 'Asia/Tokyo', [35.65444, 139.74472]], // Tokyo, Asia
            [++$number, 'America/New_York', [40.71416, -74.00638]], // New York, America
            [++$number, 'America/Argentina/Cordoba', [-31.4, -64.18333]], // Cordoba, South-America

        ];
    }
}
