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
 * TypeInspector
 *
 * Type inspector.
 *
 * @package     Pomm
 * @copyright   2015 Grégoire HUBERT
 * @author      Grégoire HUBERT
 * @license     X11 {@link http://opensource.org/licenses/mit-license.php}
 *
 * @see Client
 */
class TypeInspector extends Client
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
        return 'type';
    }

    /**
     * getTypes
     *
     * Return types informations. This method returns all type defined in the
     * database including system types.
     *
     * @param   Where $where
     * @return  ConvertedResultIterator
     */
    public function getTypes(Where $where = null)
    {
        $sql = <<<SQL
select
  t.typname as "name",
  ns.nspname as "schema",
  t.oid as "oid",
  case
    when t.typcategory = 'A' then 'array'
    when t.typcategory = 'B' then 'boolean'
    when t.typcategory = 'C' then 'composite'
    when t.typcategory = 'D' then 'date/time'
    when t.typcategory = 'E' then 'enumerated'
    when t.typcategory = 'G' then 'geometric'
    when t.typcategory = 'I' then 'network'
    when t.typcategory = 'N' then 'number'
    when t.typcategory = 'P' then 'pseudo type'
    when t.typcategory = 'S' then 'string'
    when t.typcategory = 'T' then 'timespan'
    when t.typcategory = 'U' then 'user defined'
    when t.typcategory = 'V' then 'bit string'
    else 'unknown'
  end as "category",
  t.typowner as "owner",
  d.description as "description"
from
  pg_catalog.pg_type t
  join pg_catalog.pg_namespace ns on ns.oid = t.typnamespace
  left join pg_catalog.pg_description d on d.objoid = t.oid
where
    {condition}
SQL;

        return $this->executeSql(
            $sql,
            Where::create()->andWhere($where)
        );
    }

    /**
     * getUserTypes
     *
     * Return non system types.
     *
     * @param   Where $where
     * @return  ConvertedResultIterator
     */
    public function getUserTypes(Where $where = null)
    {
        $where = Where::create("ns.nspname !~ $*", ['^pg_'])
            ->andWhere('ns.nspname != $*', ['information_schema'])
            ->andWhere($where)
            ;

        return $this->getTypes($where);
    }

    /**
     * getTypesInSchema
     *
     * Return types defined in the given schema.
     *
     * @param   string $schema
     * @return  ConvertedResultIterator
     */
    public function getTypesInSchema($schema, Where $where = null)
    {
        $schema_where = Where::create("ns.nspname = $*", [$schema]);
        $where =
            $where === null
            ? $schema_where
            : $where->andWhere($schema_where)
            ;

        return $this->getTypes($where);
    }
}
