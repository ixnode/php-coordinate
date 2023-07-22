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

use Ixnode\PhpCoordinate\Base\BaseCoordinateValue;
use Ixnode\PhpException\Case\CaseUnsupportedException;

/**
 * Class CoordinateValueLatitude
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-07-03)
 * @since 0.1.0 (2023-07-03) First version.
 */
class CoordinateValueLatitude extends BaseCoordinateValue
{
    /**
     * @param float $decimalDegree
     * @throws CaseUnsupportedException
     */
    public function __construct(protected float $decimalDegree)
    {
        parent::__construct($decimalDegree);
    }
}
