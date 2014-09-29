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
    protected function getLongBinary()
    {
        return <<<_
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
_;
    }
    public function testFromPg()
    {
        $binary = chr(0).chr(27).chr(92).chr(39).chr(32).chr(13);
        $output = $this->newTestedInstance()->fromPg('\x001b5c27200d', 'bytea', $this->getSession());
        $this
            ->string($output)
            ->string(base64_encode($output))
            ->isEqualTo(base64_encode($binary))
            ->variable($this->newTestedInstance()->fromPg('NULL', 'bytea', $this->getSession()))
            ->isNull()
            ;
    }

    public function testToPg()
    {
        $binary = chr(0).chr(27).chr(92).chr(39).chr(32).chr(13);
        $output = '\x001b5c27200d';

        $this
            ->string($this->newTestedInstance()->toPg($binary, 'bytea', $this->getSession()))
            ->isEqualTo(sprintf("bytea '%s'", $output))
            ->string($this->newTestedInstance()->toPg(null, 'bytea', $this->getSession()))
            ->isEqualTo('NULL::bytea')
            ;
    }
}



