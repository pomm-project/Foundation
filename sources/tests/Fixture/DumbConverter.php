<?php
/*
 * This file is part of Pomm's Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Fixture;

use PommProject\Foundation\Converter\ConverterInterface;
use PommProject\Foundation\Session\Session;

class DumbConverter implements ConverterInterface
{
    public function toPg(mixed $data, string $type, Session $session): string
    {
        return $data;
    }

    public function fromPg(?string $data, string $type, Session $session): ?string
    {
        return $data;
    }

    public function toPgStandardFormat(mixed $data, string $type, Session $session): ?string
    {
        return $data;
    }
}
