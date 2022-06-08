<?php
/*
 * This file is part of PommProject's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Converter\Type;

/**
 * NumRange
 *
 * Type for numerical ranges.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class NumRange extends BaseRange
{
    /**
     * getRegexp
     *
     * @see BaseRange
     */
    protected function getRegexp(): string
    {
        return '/(empty)|([\[\(])(-?[0-9\.]+)?, *(-?[0-9\.]+)?([\]\)])/';
    }

    /**
     * getSubElement
     *
     * @see BaseRange
     */
    protected function getSubElement(string $element): float|int|null
    {
        /** @phpstan-ignore-next-line */
        return $element !== '' ? $element + 0 : null;
    }
}
