<?php
/*
 * This file is part of the PommProject/Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit\Converter;

use PommProject\Foundation\Test\Unit\Converter\BaseConverter;

class PgBytea extends BaseConverter
{
    public function testFromPg()
    {
        $session = $this->buildSession();
        $binary = chr(0).chr(27).chr(92).chr(39).chr(32).chr(13);
        $output = $this->newTestedInstance()->fromPg('\x001b5c27200d', 'bytea', $session);
        $this
            ->string($output)
            ->string(base64_encode($output))
            ->isEqualTo(base64_encode($binary))
            ->variable($this->newTestedInstance()->fromPg(null, 'bytea', $session))
            ->isNull()
            ;
    }

    public function testToPg()
    {
        $binary = chr(0).chr(27).chr(92).chr(39).chr(32).chr(13);
        $output = '\x001b5c27200d';
        $session = $this->buildSession();

        $this
            ->string($this->newTestedInstance()->toPg($binary, 'bytea', $session))
            ->isEqualTo(sprintf("bytea '%s'", $output))
            ->string($this->newTestedInstance()->toPg(null, 'bytea', $session))
            ->isEqualTo('NULL::bytea')
            ;
    }

    public function testToCsv()
    {
        $binary = chr(0).chr(27).chr(92).chr(39).chr(32).chr(13);
        $output = '\x001b5c27200d';
        $session = $this->buildSession();

        $this
            ->string($this->newTestedInstance()->toCsv($binary, 'bytea', $session))
            ->isEqualTo(sprintf('"%s"', $output))
            ->variable($this->newTestedInstance()->toCsv(null, 'bytea', $session))
            ->isNull()
            ;
    }
}



