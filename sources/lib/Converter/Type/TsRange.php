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
 * TsRange
 *
 * Type for range of timestamps.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class TsRange extends BaseRange
{
    /**
     * getRegexp
     *
     * @see BaseRange
     */
    protected function getRegexp(): string
    {
        return '/(empty)|([\[\(])"?([0-9 :+\.-]+|-?infinity)?"?, *"?([0-9 :+\.-]+|-?infinity)?"?([\]\)])/';
    }

    /**
     * getSubElement
     *
     * @see BaseRange
     */
    protected function getSubElement(string $element): string|\DateTime|null
    {
        if ($element === BaseRange::INFINITY_MIN) {

            return BaseRange::INFINITY_MIN;
        } elseif ($element === BaseRange::INFINITY_MAX) {

            return BaseRange::INFINITY_MAX;
        } elseif ($element === '') {

            return null;
        }

        return new \DateTime($element);
    }
}
