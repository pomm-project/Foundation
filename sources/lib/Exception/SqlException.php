<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Exception;

use PgSql\Result;

/**
 * SqlException
 *
 * Errors from the rdbms with the result resource.
 *
 * @link      http://www.postgresql.org/docs/9.0/static/errcodes-appendix.html
 * @package   Foundation
 * @uses      FoundationException
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author    Grégoire HUBERT <hubert.greg@gmail.com>
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class SqlException extends FoundationException
{
    /* 00 - Successful Completion */
    final const SUCCESSFUL_COMPLETION = '00000';
    /* 01 - Warning */
    final const WARNING = '01000';
    final const DYNAMIC_RESULT_SETS_RETURNED = '0100C';
    final const IMPLICIT_ZERO_BIT_PADDING = '01008';
    final const NULL_VALUE_ELIMINATED_IN_SET_FUNCTION = '01003';
    final const PRIVILEGE_NOT_GRANTED = '01007';
    final const PRIVILEGE_NOT_REVOKED = '01006';
    final const STRING_DATA_RIGHT_TRUNCATION = '01004';
    final const DEPRECATED_FEATURE = '01P01';
    /* 02 - No Data (this is also a warning class per the SQL standard) */
    final const NO_DATA = '02000';
    final const NO_ADDITIONAL_DYNAMIC_RESULT_SETS_RETURNED = '02001';
    /* 03 - SQL Statement Not Yet Complete */
    final const SQL_STATEMENT_NOT_YET_COMPLETE = '03000';
    /* 08 - Connection Exception */
    final const CONNECTION_EXCEPTION = '08000';
    final const CONNECTION_DOES_NOT_EXIST = '08003';
    final const CONNECTION_FAILURE = '08006';
    final const SQLCLIENT_UNABLE_TO_ESTABLISH_SQLCONNECTION = '08001';
    final const SQLSERVER_REJECTED_ESTABLISHMENT_OF_SQLCONNECTION = '08004';
    final const TRANSACTION_RESOLUTION_UNKNOWN = '08007';
    final const PROTOCOL_VIOLATION = '08P01';
    /* 09 - Triggered Action Exception */
    final const TRIGGERED_ACTION_EXCEPTION = '09000';
    /* 0A - Feature Not Supported */
    final const FEATURE_NOT_SUPPORTED = '0A000';
    /* 0B - Invalid Transaction Initiation */
    final const INVALID_TRANSACTION_INITIATION = '0B000';
    /* 0F - Locator Exception */
    final const LOCATOR_EXCEPTION = '0F000';
    final const INVALID_LOCATOR_SPECIFICATION = '0F001';
    /* 0L - Invalid Grantor */
    final const INVALID_GRANTOR = '0L000';
    final const INVALID_GRANT_OPERATION = '0LP01';
    /* 0P - Invalid Role Specification */
    final const INVALID_ROLE_SPECIFICATION = '0P000';
    /* 0Z - Diagnostics Exception */
    final const DIAGNOSTICS_EXCEPTION = '0Z000';
    final const STACKED_DIAGNOSTICS_ACCESSED_WITHOUT_ACTIVE_HANDLER = '0Z002';
    /* 20 - Case Not Found */
    final const CASE_NOT_FOUND = '20000';
    /* 21 - Cardinality Violation */
    final const CARDINALITY_VIOLATION = '21000';
    /* 22 - Data Exception */
    final const DATA_EXCEPTION = '22000';
    final const ARRAY_SUBSCRIPT_ERROR = '2202E';
    final const CHARACTER_NOT_IN_REPERTOIRE = '22021';
    final const DATETIME_FIELD_OVERFLOW = '22008';
    final const DIVISION_BY_ZERO = '22012';
    final const ERROR_IN_ASSIGNMENT = '22005';
    final const ESCAPE_CHARACTER_CONFLICT = '2200B';
    final const INDICATOR_OVERFLOW = '22022';
    final const INTERVAL_FIELD_OVERFLOW = '22015';
    final const INVALID_ARGUMENT_FOR_LOGARITHM = '2201E';
    final const INVALID_ARGUMENT_FOR_NTILE_FUNCTION = '22014';
    final const INVALID_ARGUMENT_FOR_NTH_VALUE_FUNCTION = '22016';
    final const INVALID_ARGUMENT_FOR_POWER_FUNCTION = '2201F';
    final const INVALID_ARGUMENT_FOR_WIDTH_BUCKET_FUNCTION = '2201G';
    final const INVALID_CHARACTER_VALUE_FOR_CAST = '22018';
    final const INVALID_DATETIME_FORMAT = '22007';
    final const INVALID_ESCAPE_CHARACTER = '22019';
    final const INVALID_ESCAPE_OCTET = '2200D';
    final const INVALID_ESCAPE_SEQUENCE = '22025';
    final const NONSTANDARD_USE_OF_ESCAPE_CHARACTER = '22P06';
    final const INVALID_INDICATOR_PARAMETER_VALUE = '22010';
    final const INVALID_PARAMETER_VALUE = '22023';
    final const INVALID_REGULAR_EXPRESSION = '2201B';
    final const INVALID_ROW_COUNT_IN_LIMIT_CLAUSE = '2201W';
    final const INVALID_ROW_COUNT_IN_RESULT_OFFSET_CLAUSE = '2201X';
    final const INVALID_TIME_ZONE_DISPLACEMENT_VALUE = '22009';
    final const INVALID_USE_OF_ESCAPE_CHARACTER = '2200C';
    final const MOST_SPECIFIC_TYPE_MISMATCH = '2200G';
    final const NULL_VALUE_NOT_ALLOWED = '22004';
    final const NULL_VALUE_NO_INDICATOR_PARAMETER = '22002';
    final const NUMERIC_VALUE_OUT_OF_RANGE = '22003';
    final const STRING_DATA_LENGTH_MISMATCH = '22026';
    #const STRING_DATA_RIGHT_TRUNCATION = '22001';
    final const SUBSTRING_ERROR = '22011';
    final const TRIM_ERROR = '22027';
    final const UNTERMINATED_C_STRING = '22024';
    final const ZERO_LENGTH_CHARACTER_STRING = '2200F';
    final const FLOATING_POINT_EXCEPTION = '22P01';
    final const INVALID_TEXT_REPRESENTATION = '22P02';
    final const INVALID_BINARY_REPRESENTATION = '22P03';
    final const BAD_COPY_FILE_FORMAT = '22P04';
    final const UNTRANSLATABLE_CHARACTER = '22P05';
    final const NOT_AN_XML_DOCUMENT = '2200L';
    final const INVALID_XML_DOCUMENT = '2200M';
    final const INVALID_XML_CONTENT = '2200N';
    final const INVALID_XML_COMMENT = '2200S';
    final const INVALID_XML_PROCESSING_INSTRUCTION = '2200T';
    /* 23 - Integrity Constraint Violation */
    final const INTEGRITY_CONSTRAINT_VIOLATION = '23000';
    final const RESTRICT_VIOLATION = '23001';
    final const NOT_NULL_VIOLATION = '23502';
    final const FOREIGN_KEY_VIOLATION = '23503';
    final const UNIQUE_VIOLATION = '23505';
    final const CHECK_VIOLATION = '23514';
    final const EXCLUSION_VIOLATION = '23P01';
    /* 24 - Invalid Cursor State */
    final const INVALID_CURSOR_STATE = '24000';
    /* 25 - Invalid Transaction State */
    final const INVALID_TRANSACTION_STATE = '25000';
    final const ACTIVE_SQL_TRANSACTION = '25001';
    final const BRANCH_TRANSACTION_ALREADY_ACTIVE = '25002';
    final const HELD_CURSOR_REQUIRES_SAME_ISOLATION_LEVEL = '25008';
    final const INAPPROPRIATE_ACCESS_MODE_FOR_BRANCH_TRANSACTION = '25003';
    final const INAPPROPRIATE_ISOLATION_LEVEL_FOR_BRANCH_TRANSACTION = '25004';
    final const NO_ACTIVE_SQL_TRANSACTION_FOR_BRANCH_TRANSACTION = '25005';
    final const READ_ONLY_SQL_TRANSACTION = '25006';
    final const SCHEMA_AND_DATA_STATEMENT_MIXING_NOT_SUPPORTED = '25007';
    final const NO_ACTIVE_SQL_TRANSACTION = '25P01';
    final const IN_FAILED_SQL_TRANSACTION = '25P02';
    /* 26 - Invalid SQL Statement Name */
    final const INVALID_SQL_STATEMENT_NAME = '26000';
    /* 27 - Triggered Data Change Violation */
    final const TRIGGERED_DATA_CHANGE_VIOLATION = '27000';
    /* 28 - Invalid Authorization Specification */
    final const INVALID_AUTHORIZATION_SPECIFICATION = '28000';
    final const INVALID_PASSWORD = '28P01';
    /* 2B - Dependent Privilege Descriptors Still Exist */
    final const DEPENDENT_PRIVILEGE_DESCRIPTORS_STILL_EXIST = '2B000';
    final const DEPENDENT_OBJECTS_STILL_EXIST = '2BP01';
    /* 2D - Invalid Transaction Termination */
    final const INVALID_TRANSACTION_TERMINATION = '2D000';
    /* 2F - SQL Routine Exception */
    final const SQL_ROUTINE_EXCEPTION = '2F000';
    final const FUNCTION_EXECUTED_NO_RETURN_STATEMENT = '2F005';
    final const MODIFYING_SQL_DATA_NOT_PERMITTED = '2F002';
    final const PROHIBITED_SQL_STATEMENT_ATTEMPTED = '2F003';
    final const READING_SQL_DATA_NOT_PERMITTED = '2F004';
    /* 34 - Invalid Cursor Name */
    final const INVALID_CURSOR_NAME = '34000';
    /* 38 - External Routine Exception */
    final const EXTERNAL_ROUTINE_EXCEPTION = '38000';
    final const CONTAINING_SQL_NOT_PERMITTED = '38001';
    #const MODIFYING_SQL_DATA_NOT_PERMITTED = '38002';
    #const PROHIBITED_SQL_STATEMENT_ATTEMPTED = '38003';
    #const READING_SQL_DATA_NOT_PERMITTED = '38004';
    /* 39 - External Routine Invocation Exception */
    final const EXTERNAL_ROUTINE_INVOCATION_EXCEPTION = '39000';
    final const INVALID_SQLSTATE_RETURNED = '39001';
    #const NULL_VALUE_NOT_ALLOWED = '39004';
    final const TRIGGER_PROTOCOL_VIOLATED = '39P01';
    final const SRF_PROTOCOL_VIOLATED = '39P02';
    /* 3B - Savepoint Exception */
    final const SAVEPOINT_EXCEPTION = '3B000';
    final const INVALID_SAVEPOINT_SPECIFICATION = '3B001';
    /* 3D - Invalid Catalog Name */
    final const INVALID_CATALOG_NAME = '3D000';
    /* 3F - Invalid Schema Name */
    final const INVALID_SCHEMA_NAME = '3F000';
    /* 40 - Transaction Rollback */
    final const TRANSACTION_ROLLBACK = '40000';
    final const TRANSACTION_INTEGRITY_CONSTRAINT_VIOLATION = '40002';
    final const SERIALIZATION_FAILURE = '40001';
    final const STATEMENT_COMPLETION_UNKNOWN = '40003';
    final const DEADLOCK_DETECTED = '40P01';
    /* 42 - Syntax Error or Access Rule Violation */
    final const SYNTAX_ERROR_OR_ACCESS_RULE_VIOLATION = '42000';
    final const SYNTAX_ERROR = '42601';
    final const INSUFFICIENT_PRIVILEGE = '42501';
    final const CANNOT_COERCE = '42846';
    final const GROUPING_ERROR = '42803';
    final const WINDOWING_ERROR = '42P20';
    final const INVALID_RECURSION = '42P19';
    final const INVALID_FOREIGN_KEY = '42830';
    final const INVALID_NAME = '42602';
    final const NAME_TOO_LONG = '42622';
    final const RESERVED_NAME = '42939';
    final const DATATYPE_MISMATCH = '42804';
    final const INDETERMINATE_DATATYPE = '42P18';
    final const COLLATION_MISMATCH = '42P21';
    final const INDETERMINATE_COLLATION = '42P22';
    final const WRONG_OBJECT_TYPE = '42809';
    final const UNDEFINED_COLUMN = '42703';
    final const UNDEFINED_FUNCTION = '42883';
    final const UNDEFINED_TABLE = '42P01';
    final const UNDEFINED_PARAMETER = '42P02';
    final const UNDEFINED_OBJECT = '42704';
    final const DUPLICATE_COLUMN = '42701';
    final const DUPLICATE_CURSOR = '42P03';
    final const DUPLICATE_DATABASE = '42P04';
    final const DUPLICATE_FUNCTION = '42723';
    final const DUPLICATE_PREPARED_STATEMENT = '42P05';
    final const DUPLICATE_SCHEMA = '42P06';
    final const DUPLICATE_TABLE = '42P07';
    final const DUPLICATE_ALIAS = '42712';
    final const DUPLICATE_OBJECT = '42710';
    final const AMBIGUOUS_COLUMN = '42702';
    final const AMBIGUOUS_FUNCTION = '42725';
    final const AMBIGUOUS_PARAMETER = '42P08';
    final const AMBIGUOUS_ALIAS = '42P09';
    final const INVALID_COLUMN_REFERENCE = '42P10';
    final const INVALID_COLUMN_DEFINITION = '42611';
    final const INVALID_CURSOR_DEFINITION = '42P11';
    final const INVALID_DATABASE_DEFINITION = '42P12';
    final const INVALID_FUNCTION_DEFINITION = '42P13';
    final const INVALID_PREPARED_STATEMENT_DEFINITION = '42P14';
    final const INVALID_SCHEMA_DEFINITION = '42P15';
    final const INVALID_TABLE_DEFINITION = '42P16';
    final const INVALID_OBJECT_DEFINITION = '42P17';
    /* 44 - WITH CHECK OPTION Violation */
    final const WITH_CHECK_OPTION_VIOLATION = '44000';
    /* 53 - Insufficient Resources */
    final const INSUFFICIENT_RESOURCES = '53000';
    final const DISK_FULL = '53100';
    final const OUT_OF_MEMORY = '53200';
    final const TOO_MANY_CONNECTIONS = '53300';
    final const CONFIGURATION_LIMIT_EXCEEDED = '53400';
    /* 54 - Program Limit Exceeded */
    final const PROGRAM_LIMIT_EXCEEDED = '54000';
    final const STATEMENT_TOO_COMPLEX = '54001';
    final const TOO_MANY_COLUMNS = '54011';
    final const TOO_MANY_ARGUMENTS = '54023';
    /* 55 - Object Not In Prerequisite State */
    final const OBJECT_NOT_IN_PREREQUISITE_STATE = '55000';
    final const OBJECT_IN_USE = '55006';
    final const CANT_CHANGE_RUNTIME_PARAM = '55P02';
    final const LOCK_NOT_AVAILABLE = '55P03';
    /* 57 - Operator Intervention */
    final const OPERATOR_INTERVENTION = '57000';
    final const QUERY_CANCELED = '57014';
    final const ADMIN_SHUTDOWN = '57P01';
    final const CRASH_SHUTDOWN = '57P02';
    final const CANNOT_CONNECT_NOW = '57P03';
    final const DATABASE_DROPPED = '57P04';
    /* 58 - System Error (errors external to PostgreSQL itself) */
    final const SYSTEM_ERROR = '58000';
    final const IO_ERROR = '58030';
    final const UNDEFINED_FILE = '58P01';
    final const DUPLICATE_FILE = '58P02';
    /* F0 - Configuration File Error */
    final const CONFIG_FILE_ERROR = 'F0000';
    final const LOCK_FILE_EXISTS = 'F0001';
    /* HV - Foreign Data Wrapper Error (SQL/MED) */
    final const FDW_ERROR = 'HV000';
    final const FDW_COLUMN_NAME_NOT_FOUND = 'HV005';
    final const FDW_DYNAMIC_PARAMETER_VALUE_NEEDED = 'HV002';
    final const FDW_FUNCTION_SEQUENCE_ERROR = 'HV010';
    final const FDW_INCONSISTENT_DESCRIPTOR_INFORMATION = 'HV021';
    final const FDW_INVALID_ATTRIBUTE_VALUE = 'HV024';
    final const FDW_INVALID_COLUMN_NAME = 'HV007';
    final const FDW_INVALID_COLUMN_NUMBER = 'HV008';
    final const FDW_INVALID_DATA_TYPE = 'HV004';
    final const FDW_INVALID_DATA_TYPE_DESCRIPTORS = 'HV006';
    final const FDW_INVALID_DESCRIPTOR_FIELD_IDENTIFIER = 'HV091';
    final const FDW_INVALID_HANDLE = 'HV00B';
    final const FDW_INVALID_OPTION_INDEX = 'HV00C';
    final const FDW_INVALID_OPTION_NAME = 'HV00D';
    final const FDW_INVALID_STRING_LENGTH_OR_BUFFER_LENGTH = 'HV090';
    final const FDW_INVALID_STRING_FORMAT = 'HV00A';
    final const FDW_INVALID_USE_OF_NULL_POINTER = 'HV009';
    final const FDW_TOO_MANY_HANDLES = 'HV014';
    final const FDW_OUT_OF_MEMORY = 'HV001';
    final const FDW_NO_SCHEMAS = 'HV00P';
    final const FDW_OPTION_NAME_NOT_FOUND = 'HV00J';
    final const FDW_REPLY_HANDLE = 'HV00K';
    final const FDW_SCHEMA_NOT_FOUND = 'HV00Q';
    final const FDW_TABLE_NOT_FOUND = 'HV00R';
    final const FDW_UNABLE_TO_CREATE_EXECUTION = 'HV00L';
    final const FDW_UNABLE_TO_CREATE_REPLY = 'HV00M';
    final const FDW_UNABLE_TO_ESTABLISH_CONNECTION = 'HV00N';
    /* P0 - PL/pgSQL Error */
    final const PLPGSQL_ERROR = 'P0000';
    final const RAISE_EXCEPTION = 'P0001';
    final const NO_DATA_FOUND = 'P0002';
    final const TOO_MANY_ROWS = 'P0003';
    /* XX - Internal Error */
    final const INTERNAL_ERROR = 'XX000';
    final const DATA_CORRUPTED = 'XX001';
    final const INDEX_CORRUPTED = 'XX002';
    protected array $query_parameters = [];

    /**
     * __construct
     *
     * @access public
     * @param Result $result
     * @param string $sql
     * @param int $code
     * @param \Exception|null $e
     */
    public function __construct(protected Result $result, protected string $sql, int $code = 0, \Exception $e = null)
    {
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
    public function getSQLErrorState(): string
    {
        return pg_result_error_field($this->result, \PGSQL_DIAG_SQLSTATE);
    }

    /**
     * getSQLErrorSeverity
     *
     * Returns the severity level of the error.
     *
     * @access public
     * @return string
     */
    public function getSQLErrorSeverity(): string
    {
        return pg_result_error_field($this->result, \PGSQL_DIAG_SEVERITY);
    }

    /**
     * getSqlErrorMessage
     *
     * Returns the error message sent by the server.
     *
     * @access public
     * @return string
     */

    public function getSqlErrorMessage(): string
    {
        return pg_result_error($this->result);
    }

    /**
     * getSQLDetailedErrorMessage
     *
     * @access public
     * @return string
     */
    public function getSQLDetailedErrorMessage(): string
    {
        return sprintf("«%s»\n%s\n(%s)", pg_result_error_field($this->result, \PGSQL_DIAG_MESSAGE_PRIMARY), pg_result_error_field($this->result, \PGSQL_DIAG_MESSAGE_DETAIL), pg_result_error_field($this->result, \PGSQL_DIAG_MESSAGE_HINT));
    }

    /**
     * getQuery
     *
     * Return the associated query.
     *
     * @access public
     * @return string
     */
    public function getQuery(): string
    {
        return $this->sql;
    }

    /**
     * setQueryParameters
     *
     * Set the query parameters sent with the query.
     *
     * @access public
     * @param  array    $parameters
     * @return SqlException $this
     */
    public function setQueryParameters(array $parameters): SqlException
    {
        $this->query_parameters = $parameters;

        return $this;
    }

    /**
     * getQueryParameters
     *
     * Return the query parameters sent with the query.
     *
     * @access public
     * @return array
     */
    public function getQueryParameters(): array
    {
        return $this->query_parameters;
    }
}
