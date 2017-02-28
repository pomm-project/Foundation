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

use PommProject\Foundation\Exception\FoundationException;
use PommProject\Foundation\ConvertedResultIterator;
use PommProject\Foundation\Client\Client;
use PommProject\Foundation\Where;

/**
 * SchemaInspector
 *
 * Schema inspector client.
 *
 * @package     Pomm
 * @copyright   2015 Grégoire HUBERT
 * @author      Grégoire HUBERT
 * @license     X11 {@link http://opensource.org/licenses/mit-license.php}
 *
 * @see Client
 */
class SchemaInspector extends Client
{
    use InspectorTrait;

    /**
     * getClientType
     *
     * @see ClientInterface
     */
    public function getClientType()
    {
        return 'inspector';
    }

    /**
     * getClientIdentifier
     *
     * @see ClientInterface
     */
    public function getClientIdentifier()
    {
        return 'schema';
    }

    /**
     * getSchemas
     *
     * Return a list of available schemas in the current database. An
     * additional criteria can be given to filter the results.
     * Criterias can be a combination of:
     * * n schema
     * * d description
     * * o owner
     *
     * If no criteria is provided, all schemas will be returned including
     * system schemas (temporary schema, toast schemas etc.)
     *
     * @param   Where $where
     * @return  ConvertedResultIterator
     */
    public function getSchemas(Where $where = null)
    {
        $sql = <<<SQL
select
    n.nspname     as "name",
    n.oid         as "oid",
    d.description as "comment",
    count(c)      as "relations",
    o.rolname     as "owner"
from pg_catalog.pg_namespace n
    left join pg_catalog.pg_description d on n.oid = d.objoid
    left join pg_catalog.pg_class c on
        c.relnamespace = n.oid and c.relkind in ('r', 'v')
    join pg_catalog.pg_roles o on n.nspowner = o.oid
where {condition}
group by 1, 2, 3, 5
order by 1 asc;
SQL;
        $condition = Where::create()
            ->andWhere($where)
            ;

        return $this->executeSql($sql, $condition);
    }

    /**
     * getUserSchemas
     *
     * Return a list of user schema (not pg_* nor information_schema).
     *
     * @param   Where $where
     * @return  ConvertedResultIterator
     */
    public function getUserSchemas(Where $where = null)
    {
        $condition = Where::create()
            ->andWhere($where)
            ->andwhere("n.nspname !~ $*", ['^pg_'])
            ->andWhere("n.nspname <> $*", ['information_schema'])
            ;

        return $this->getSchemas($condition);
    }
}
