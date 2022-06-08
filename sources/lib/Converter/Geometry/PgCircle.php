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

use PommProject\Foundation\Client\ClientInterface;
use PommProject\Foundation\Converter\ConverterClient;
use PommProject\Foundation\Converter\Type\Circle;
use PommProject\Foundation\Converter\TypeConverter;
use PommProject\Foundation\Converter\ConverterInterface;
use PommProject\Foundation\Exception\FoundationException;
use PommProject\Foundation\Session\Session;

/**
 * PgCircle
 *
 * Converter for PostgreSQL Circle type.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see ConverterInterface
 */
class PgCircle extends TypeConverter
{
    protected ?ConverterClient $point_converter = null;

    /**
     * getTypeClassName
     *
     * Circle class name
     *
     * @see TypeConverter
     */
    protected function getTypeClassName(): string
    {
        return Circle::class;
    }

    /**
     * toPg
     *
     * @throws FoundationException
     * @see ConverterInterface
     */
    public function toPg(mixed $data, string $type, Session $session): string
    {
        if ($data === null) {
            return sprintf("NULL::%s", $type);
        }

        $data = $this->checkData($data);

        return
            sprintf(
                "%s(%s,%s)",
                $type,
                $this
                    ->getPointConverter($session)
                    ->toPg($data->center, 'point'),
                $data->radius
            );
    }

    /**
     * getPointConverter
     *
     * Cache the point converter.
     *
     * @access protected
     * @param Session $session
     * @return ConverterClient
     * @throws FoundationException
     */
    protected function getPointConverter(Session $session): ConverterClient
    {
        if ($this->point_converter === null) {
            /** @var ConverterClient $converterClient */
            $converterClient = $session->getClientUsingPooler('converter', 'point');
            $this->point_converter = $converterClient;
        }

        return $this->point_converter;
    }
}
