<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Exception;

/**
 * SqlException
 *
 * Errors from the rdbms with the result resource.
 *
 * @link http://www.postgresql.org/docs/9.0/static/errcodes-appendix.html
 * @package Foundation
 * @uses FoundationException
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT <hubert.greg@gmail.com>
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class SqlException extends FoundationException
{
    protected $result_resource;
    protected $sql;

    /**
     * __construct
     *
     * @access public
     * @param  resource   $result_resource
     * @param  string     $sql
     * @param  string     $code
     * @param  \Exception $e
     */
    public function __construct($result_resource, $sql, $code = null, \Exception $e = null)
    {
        $this->result_resource = $result_resource;
        $this->sql = $sql;
        parent::__construct(
            sprintf(
                "\nSQL error state '%s' [%s]\n====\n%s\n====\n«%s».",
                $this->getSQLErrorState(),
                $this->getSQLErrorSeverity(),
                $this->getSqlErrorMessage(),
                $sql
            ),
            $code,
            $e
        );
    }

    /**
     * getSQLErrorState
     *
     * Returns the SQLSTATE of the last SQL error.
     *
     * @link http://www.postgresql.org/docs/9.0/interactive/errcodes-appendix.html
     * @access public
     * @return string
     */
    public function getSQLErrorState()
    {
        return pg_result_error_field($this->result_resource, \PGSQL_DIAG_SQLSTATE);
    }

    /**
     * getSQLErrorSeverity
     *
     * Returns the severity level of the error.
     *
     * @access public
     * @return string
     */
    public function getSQLErrorSeverity()
    {
        return pg_result_error_field($this->result_resource, \PGSQL_DIAG_SEVERITY);
    }

    /**
     * getSqlErrorMessage
     *
     * Returns the error message sent by the server.
     *
     * @access public
     * @return string
     */

    public function getSqlErrorMessage()
    {
        return pg_result_error($this->result_resource);
    }

    /**
     * getSQLDetailedErrorMessage
     *
     * @access public
     * @return string
     */
    public function getSQLDetailedErrorMessage()
    {
        return sprintf("«%s»\n%s\n(%s)", pg_result_error_field($this->result_resource, \PGSQL_DIAG_MESSAGE_PRIMARY), pg_result_error_field($this->result_resource, \PGSQL_DIAG_MESSAGE_DETAIL), pg_result_error_field($this->result_resource, \PGSQL_DIAG_MESSAGE_HINT));
    }
}
