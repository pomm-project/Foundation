<?php
/*
 * This file is part of the Pomm package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Inspector;

use PommProject\Foundation\Where;
use PommProject\Foundation\ConvertedResultIterator;

/**
 * InspectorTrait
 *
 * Common fonctions for inspectors.
 *
 * @package     Pomm
 * @copyright   2015 Grégoire HUBERT
 * @author      Grégoire HUBERT
 * @license     X11 {@link http://opensource.org/licenses/mit-license.php}
 */
trait InspectorTrait
{

    /**
     * executeSql
     *
     * Launch query execution expanding string {condition} with the Where query
     * parameter and values.
     *
     * @param  string         $sql
     * @param  Where          $condition
     * @return ConvertedResultIterator
     */
    protected function executeSql($sql, Where $condition = null)
    {
        $condition = (new Where())->andWhere($condition);
        $sql = strtr($sql, ['{condition}' => $condition]);

        return $this
            ->getSession()
            ->getClientUsingPooler(
                'query_manager',
                null
            )
            ->query($sql, $condition->getValues())
            ;
    }
}
