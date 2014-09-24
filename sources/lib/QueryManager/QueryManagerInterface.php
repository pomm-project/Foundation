<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\QueryManager;

use PommProject\Foundation\Session;

/**
 * QueryManagerInterface
 *
 * Interface for QueryManager.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 *
 *
 */
interface QueryManagerInterface
{
    public function initialize(Session $session);

   /**
     * query
     *
     * This method sends a SQL query to the server and returns a response back.
     *
     * @access public
     * @param  string         $sql
     * @param  array          $values
     * @return ResultIterator
     */
    public function query($sql, $values = []);
}
