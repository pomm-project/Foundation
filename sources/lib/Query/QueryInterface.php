<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Query;

use PommProject\Foundation\ResultIterator;

/**
 * QueryInterface
 *
 * Query clients interface
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 *
 *
 */
interface QueryInterface
{
    /**
     * query
     *
     * Execute a parameterized query.
     *
     * @access public
     * @param  string         $sql
     * @param  array          $parameters
     * @return ResultIterator Query result
     */
    public function query($sql, array $parameters = []);
}
