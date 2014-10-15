<?php
/*
 * This file is part of PommProject's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Converter\Geometry;

use PommProject\Foundation\Exception\ConverterException;
use PommProject\Foundation\Converter\ConverterInterface;
use PommProject\Foundation\Converter\Type\Circle;
use PommProject\Foundation\Session;

/**
 * PgCircle
 *
 * Converter for Postgresql Circle type.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see ConverterInterface
 */
class PgCircle implements ConverterInterface
{
    protected $circle_class_name;

    /**
     * __construct
     *
     * The parameter is the class of the circle that will be instantiated.
     *
     * @access public
     * @param  string $circle_class_name
     * @return void
     */
    public function __construct($circle_class_name = null)
    {
        $this->circle_class_name =
            $circle_class_name === null
            ? '\PommProject\Foundation\Converter\Type\Circle'
            : $circle_class_name
            ;
    }

    public function fromPg($data, $type, Session $session)
    {
        if ($data === null) {
            return null;
        }

        $class_name = $this->circle_class_name;

        try {
            return new $class_name($data);
        } catch (\InvalidArgumentException $e) {
            throw new ConverterException(
                sprintf("Unable to create a Circle instance."),
                null,
                $e
            );
        }
    }

    public function toPg($data, $type, Session $session)
    {
        if ($data === null) {
            return sprintf("NULL::%s", $type);
        }

        if (!$data instanceOf Circle) {
            $data = $this->fromPg($data, $type, $session);
        }

        return sprintf(
            "circle(%s,%s)",
            $session
                ->getClientUsingPooler('converter', 'point')
                ->toPg($data->center)
                ,
            $data->radius
        );
    }
}
