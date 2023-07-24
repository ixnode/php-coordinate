<?php

/*
 * This file is part of the ixnode/php-coordinate project.
 *
 * (c) Björn Hempel <https://www.hempel.li/>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Ixnode\PhpCoordinate\Output;

use Ahc\Cli\Output\Table as AhcTable;

/**
 * Class Table
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-07-24)
 * @since 0.1.0 (2023-07-24) First version.
 */
class Table extends AhcTable
{
    /**
     * Renders a cli table.
     *
     * @param array<array<string, string|int|float>> $rows
     * @param array<string, string> $styles
     * @return string
     */
    public function render(array $rows, array $styles = []): string
    {
        $table = $this->normalize($rows);

        if ($table === []) {
            return '';
        }

        [$head, $rows] = $table;

        $styles = $this->normalizeStyles($styles);
        $title  = $body = $dash = [];

        [$start, $end] = $styles['head'];
        foreach ($head as $col => $size) {
            $dash[]  = str_repeat('-', $size + 2);
            $title[] = $this->mbStrPad($this->toWords($col), $size, ' ');
        }

        $title = sprintf(
            '|%s %s %s|%s',
            $start,
            implode(sprintf(' %s|%s ', $end, $start), $title),
            $end,
            PHP_EOL
        );

        $odd = true;
        foreach ($rows as $row) {
            $parts = [];

            [$start, $end] = $styles[['even', 'odd'][(int) $odd]];
            foreach ($head as $col => $size) {
                $parts[] = $this->mbStrPad($row[$col] ?? '', $size, ' ');
            }

            $odd = !$odd;
            $body[] = sprintf(
                '|%s %s %s|',
                $start,
                implode(sprintf(' %s|%s ', $end, $start), $parts),
                $end
            );
        }

        $dash  = sprintf('%s%s%s%s', '+', implode('+', $dash), '+', PHP_EOL);
        $body  = implode(PHP_EOL, $body).PHP_EOL;

        return sprintf('%s%s%s%s%s', $dash, $title, $dash, $body, $dash);
    }

    /**
     * UTF8 mb_pad alternative.
     *
     * @param string $string
     * @param int $length
     * @param string $padString
     * @return string
     */
    function mbStrPad(string $string, int $length, string $padString = ' '): string
    {
        return $string.str_repeat($padString, $length - mb_strlen($string));
    }
}
