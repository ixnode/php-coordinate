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

namespace Ixnode\PhpCoordinate\Command;

use Ahc\Cli\Input\Command;
use Ahc\Cli\Output\Color;
use Ahc\Cli\Output\Writer;
use Exception;
use Ixnode\PhpContainer\File;
use Ixnode\PhpCoordinate\Coordinate;
use Ixnode\PhpCoordinate\Output\Table;
use Ixnode\PhpCoordinate\Utils\Image2Ascii;
use Ixnode\PhpException\Case\CaseUnsupportedException;
use Ixnode\PhpException\Parser\ParserException;

/**
 * Class CoordinateCommand
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-07-24)
 * @since 0.1.0 (2023-07-24) First version.
 * @property string|null $coordinateSource
 * @property string|null $coordinateTarget
 */
class CoordinateCommand extends Command
{
    private const SUCCESS = 0;

    private const INVALID = 2;

    private const KEY_KEY = 'key';

    private const KEY_LATITUDE = 'latitude';

    private const KEY_LONGITUDE = 'longitude';

    private const KEY_VALUE = 'value';

    private const CONFIG_TABLE = [
        'head' => 'boldGreen',
        'odd'  => 'bold',
        'even' => 'bold',
    ];

    private Writer $writer;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct('coordinate:show', 'Shows information about given coordinate.');

        $this
            ->argument('coordinate-source', 'The source coordinate string to parse.')
            ->argument('coordinate-target', 'The target coordinate string to parse.')
        ;
    }

    /**
     * Prints a cli table.
     *
     * @param array<array<string, string|int|float>> $rows
     * @param array<string, string> $styles
     * @return Writer
     */
    public function table(array $rows, array $styles = []): Writer
    {
        $table = (new Table())->render($rows, $styles);

        return $this->writer()->colors($table);
    }

    /**
     * Prints some coordinate information.
     *
     * @param string $coordinateGiven
     * @param Coordinate $coordinate
     * @param string $title
     * @return void
     * @throws CaseUnsupportedException
     */
    private function printData(string $coordinateGiven, Coordinate $coordinate, string $title): void
    {
        $this->writer->write(PHP_EOL);
        $this->writer->write(sprintf('%s (%s):%s', $title, $coordinateGiven, PHP_EOL));
        $this->writer->write(PHP_EOL);
        $this->table([
            [
                self::KEY_VALUE => 'Decimal',
                self::KEY_LATITUDE => $coordinate->getLatitudeDecimal(),
                self::KEY_LONGITUDE => $coordinate->getLongitudeDecimal(),
            ],
            [
                self::KEY_VALUE => 'DMS',
                self::KEY_LATITUDE => $coordinate->getLatitudeDMS(),
                self::KEY_LONGITUDE => $coordinate->getLongitudeDMS(),
            ],
        ], self::CONFIG_TABLE);
    }

    /**
     * Prints some coordinate information.
     *
     * @param Coordinate $coordinateSource
     * @param Coordinate $coordinateTarget
     * @param string $title
     * @return void
     * @throws CaseUnsupportedException
     */
    private function printDistance(Coordinate $coordinateSource, Coordinate $coordinateTarget, string $title): void
    {
        $this->writer->write(PHP_EOL);
        $this->writer->write(sprintf('%s:%s', $title, PHP_EOL));
        $this->writer->write(PHP_EOL);
        $this->table([
            [
                self::KEY_KEY => 'Distance',
                self::KEY_VALUE => sprintf('%.3f km', $coordinateSource->getDistance($coordinateTarget, Coordinate::RETURN_KILOMETERS)),
            ],
            [
                self::KEY_KEY => 'Degree',
                self::KEY_VALUE => sprintf('%.3f°', $coordinateSource->getDegree($coordinateTarget)),
            ],
            [
                self::KEY_KEY => 'Direction',
                self::KEY_VALUE => sprintf('%s', $coordinateSource->getDirection($coordinateTarget)),
            ],
        ], self::CONFIG_TABLE);
    }

    /**
     * Prints error message.
     *
     * @param string $message
     * @return void
     * @throws Exception
     */
    private function printError(string $message): void
    {
        $color = new Color();

        $this->writer->write(sprintf('%s%s', $color->error($message), PHP_EOL));
    }

    /**
     * Gets the cardinal direction.
     *
     * @return array<int, string>
     */
    private function getCardinalDirection(): array
    {
        $string = [];

        $string[] = '+------------------------------------+';
        $string[] = '|         Cardinal direction         |';
        $string[] = '+------------------------------------|';
        $string[] = '|                                    |';
        $string[] = '|                  0°                |';
        $string[] = '|                                    |';
        $string[] = '|                 ___                |';
        $string[] = '|    -45°         ───        45°     |';
        $string[] = '|          //      N      \\\\         |';
        $string[] = '|             NW       NE            |';
        $string[] = '|                  |                 |';
        $string[] = '|  -90°  || W    --+--    E ||  90°  |';
        $string[] = '|                  |                 |';
        $string[] = '|             SW       SE            |';
        $string[] = '|          \\\\      S      //         |';
        $string[] = '|    -135°        ───        135°    |';
        $string[] = '|                 ‾‾‾                |';
        $string[] = '|                                    |';
        $string[] = '|                 180°               |';
        $string[] = '|                                    |';
        $string[] = '+------------------------------------+';

        return $string;
    }

    /**
     * Gets the longitude and latitude description.
     *
     * @return array<int, string>
     */
    private function getLongitudeLatitude(): array
    {
        $string = [];

        $string[] = '+------------------------------------------+';
        $string[] = '|           Longitude / Langitude          |';
        $string[] = '+------------------------------------------|';
        $string[] = '|              y                           |';
        $string[] = '|                                          |';
        $string[] = '|          90° ⯅                           |';
        $string[] = '|              |                           |';
        $string[] = '|            L |                           |';
        $string[] = '|            a |                           |';
        $string[] = '|            t |                           |';
        $string[] = '|            i |                           |';
        $string[] = '|            t |                           |';
        $string[] = '|            u |                           |';
        $string[] = '|            d |                           |';
        $string[] = '|            e | Greenwich                 |';
        $string[] = '|      ⯇-------+---------------------⯈  x  |';
        $string[] = '| -180°        | Longitude            180° |';
        $string[] = '|              |                           |';
        $string[] = '|              |                           |';
        $string[] = '|         -90° ⯆                           |';
        $string[] = '+------------------------------------------+';

        return $string;
    }

    /**
     * Merge strings.
     *
     * @param array<int, string> $strings1
     * @param array<int, string> $strings2
     * @return array<int, string>
     */
    private function mergeStrings(array $strings1, array $strings2): array
    {
        $string = [];

        foreach ($strings1 as $number => $string1) {
            $string2 = array_key_exists($number, $strings2) ? $strings2[$number] : '';
            $string[] = $string1.'   '.$string2;
        }

        return $string;
    }

    /**
     * Executes the ParserCommand.
     *
     * @return int
     * @throws ParserException
     * @throws Exception
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function execute(): int
    {
        $this->writer = $this->writer();

        $coordinateSource = $this->coordinateSource;
        $coordinateTarget = $this->coordinateTarget;

        if (is_null($coordinateSource)) {
            $this->printError('No source coordinate given.');
            return self::INVALID;
        }

        if (is_null($coordinateTarget)) {
            $this->printError('No target coordinate given.');
            return self::INVALID;
        }

        $coordinateSourceEntity = new Coordinate($coordinateSource);
        $coordinateTargetEntity = new Coordinate($coordinateTarget);

        $this->printData($coordinateSource, $coordinateSourceEntity, 'Source coordinate');
        $this->printData($coordinateTarget, $coordinateTargetEntity, 'Target coordinate');
        $this->printDistance($coordinateSourceEntity, $coordinateTargetEntity, 'Distance');
        $this->writer->write(PHP_EOL);

        $path = 'docs/image/world-map.png';

        $file = new File($path);

        if (!$file->exist()) {
            $path = sprintf('vendor/ixnode/php-coordinate/%s', $path);
            $file = new File($path);
        }

        $image = new Image2Ascii($file);

        $this->writer->write($image->getAscii(120, [
            '#ff0000' => $coordinateTargetEntity,
            '#00ff00' => $coordinateSourceEntity,
        ]));
        $this->writer->write(PHP_EOL);

        $this->writer->write(PHP_EOL);
        $this->writer->write(
            implode(
                PHP_EOL,
                $this->mergeStrings(
                    $this->getCardinalDirection(),
                    $this->getLongitudeLatitude()
                )
            )
        );
        $this->writer->write(PHP_EOL);
        $this->writer->write(PHP_EOL);

        return self::SUCCESS;
    }
}
