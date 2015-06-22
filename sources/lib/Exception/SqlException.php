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
    /* 00 - Successful Completion */
    const SUCCESSFUL_COMPLETION = '00000';
    /* 01 - Warning */
    const WARNING = '01000';
    const DYNAMIC_RESULT_SETS_RETURNED = '0100C';
    const IMPLICIT_ZERO_BIT_PADDING = '01008';
    const NULL_VALUE_ELIMINATED_IN_SET_FUNCTION = '01003';
    const PRIVILEGE_NOT_GRANTED = '01007';
    const PRIVILEGE_NOT_REVOKED = '01006';
    const STRING_DATA_RIGHT_TRUNCATION = '01004';
    const DEPRECATED_FEATURE = '01P01';
    /* 02 - No Data (this is also a warning class per the SQL standard) */
    const NO_DATA = '02000';
    const NO_ADDITIONAL_DYNAMIC_RESULT_SETS_RETURNED = '02001';
    /* 03 - SQL Statement Not Yet Complete */
    const SQL_STATEMENT_NOT_YET_COMPLETE = '03000';
    /* 08 - Connection Exception */
    const CONNECTION_EXCEPTION = '08000';
    const CONNECTION_DOES_NOT_EXIST = '08003';
    const CONNECTION_FAILURE = '08006';
    const SQLCLIENT_UNABLE_TO_ESTABLISH_SQLCONNECTION = '08001';
    const SQLSERVER_REJECTED_ESTABLISHMENT_OF_SQLCONNECTION = '08004';
    const TRANSACTION_RESOLUTION_UNKNOWN = '08007';
    const PROTOCOL_VIOLATION = '08P01';
    /* 09 - Triggered Action Exception */
    const TRIGGERED_ACTION_EXCEPTION = '09000';
    /* 0A - Feature Not Supported */
    const FEATURE_NOT_SUPPORTED = '0A000';
    /* 0B - Invalid Transaction Initiation */
    const INVALID_TRANSACTION_INITIATION = '0B000';
    /* 0F - Locator Exception */
    const LOCATOR_EXCEPTION = '0F000';
    const INVALID_LOCATOR_SPECIFICATION = '0F001';
    /* 0L - Invalid Grantor */
    const INVALID_GRANTOR = '0L000';
    const INVALID_GRANT_OPERATION = '0LP01';
    /* 0P - Invalid Role Specification */
    const INVALID_ROLE_SPECIFICATION = '0P000';
    /* 0Z - Diagnostics Exception */
    const DIAGNOSTICS_EXCEPTION = '0Z000';
    const STACKED_DIAGNOSTICS_ACCESSED_WITHOUT_ACTIVE_HANDLER = '0Z002';
    /* 20 - Case Not Found */
    const CASE_NOT_FOUND = '20000';
    /* 21 - Cardinality Violation */
    const CARDINALITY_VIOLATION = '21000';
    /* 22 - Data Exception */
    const DATA_EXCEPTION = '22000';
    const ARRAY_SUBSCRIPT_ERROR = '2202E';
    const CHARACTER_NOT_IN_REPERTOIRE = '22021';
    const DATETIME_FIELD_OVERFLOW = '22008';
    const DIVISION_BY_ZERO = '22012';
    const ERROR_IN_ASSIGNMENT = '22005';
    const ESCAPE_CHARACTER_CONFLICT = '2200B';
    const INDICATOR_OVERFLOW = '22022';
    const INTERVAL_FIELD_OVERFLOW = '22015';
    const INVALID_ARGUMENT_FOR_LOGARITHM = '2201E';
    const INVALID_ARGUMENT_FOR_NTILE_FUNCTION = '22014';
    const INVALID_ARGUMENT_FOR_NTH_VALUE_FUNCTION = '22016';
    const INVALID_ARGUMENT_FOR_POWER_FUNCTION = '2201F';
    const INVALID_ARGUMENT_FOR_WIDTH_BUCKET_FUNCTION = '2201G';
    const INVALID_CHARACTER_VALUE_FOR_CAST = '22018';
    const INVALID_DATETIME_FORMAT = '22007';
    const INVALID_ESCAPE_CHARACTER = '22019';
    const INVALID_ESCAPE_OCTET = '2200D';
    const INVALID_ESCAPE_SEQUENCE = '22025';
    const NONSTANDARD_USE_OF_ESCAPE_CHARACTER = '22P06';
    const INVALID_INDICATOR_PARAMETER_VALUE = '22010';
    const INVALID_PARAMETER_VALUE = '22023';
    const INVALID_REGULAR_EXPRESSION = '2201B';
    const INVALID_ROW_COUNT_IN_LIMIT_CLAUSE = '2201W';
    const INVALID_ROW_COUNT_IN_RESULT_OFFSET_CLAUSE = '2201X';
    const INVALID_TIME_ZONE_DISPLACEMENT_VALUE = '22009';
    const INVALID_USE_OF_ESCAPE_CHARACTER = '2200C';
    const MOST_SPECIFIC_TYPE_MISMATCH = '2200G';
    const NULL_VALUE_NOT_ALLOWED = '22004';
    const NULL_VALUE_NO_INDICATOR_PARAMETER = '22002';
    const NUMERIC_VALUE_OUT_OF_RANGE = '22003';
    const STRING_DATA_LENGTH_MISMATCH = '22026';
    #const STRING_DATA_RIGHT_TRUNCATION = '22001';
    const SUBSTRING_ERROR = '22011';
    const TRIM_ERROR = '22027';
    const UNTERMINATED_C_STRING = '22024';
    const ZERO_LENGTH_CHARACTER_STRING = '2200F';
    const FLOATING_POINT_EXCEPTION = '22P01';
    const INVALID_TEXT_REPRESENTATION = '22P02';
    const INVALID_BINARY_REPRESENTATION = '22P03';
    const BAD_COPY_FILE_FORMAT = '22P04';
    const UNTRANSLATABLE_CHARACTER = '22P05';
    const NOT_AN_XML_DOCUMENT = '2200L';
    const INVALID_XML_DOCUMENT = '2200M';
    const INVALID_XML_CONTENT = '2200N';
    const INVALID_XML_COMMENT = '2200S';
    const INVALID_XML_PROCESSING_INSTRUCTION = '2200T';
    /* 23 - Integrity Constraint Violation */
    const INTEGRITY_CONSTRAINT_VIOLATION = '23000';
    const RESTRICT_VIOLATION = '23001';
    const NOT_NULL_VIOLATION = '23502';
    const FOREIGN_KEY_VIOLATION = '23503';
    const UNIQUE_VIOLATION = '23505';
    const CHECK_VIOLATION = '23514';
    const EXCLUSION_VIOLATION = '23P01';
    /* 24 - Invalid Cursor State */
    const INVALID_CURSOR_STATE = '24000';
    /* 25 - Invalid Transaction State */
    const INVALID_TRANSACTION_STATE = '25000';
    const ACTIVE_SQL_TRANSACTION = '25001';
    const BRANCH_TRANSACTION_ALREADY_ACTIVE = '25002';
    const HELD_CURSOR_REQUIRES_SAME_ISOLATION_LEVEL = '25008';
    const INAPPROPRIATE_ACCESS_MODE_FOR_BRANCH_TRANSACTION = '25003';
    const INAPPROPRIATE_ISOLATION_LEVEL_FOR_BRANCH_TRANSACTION = '25004';
    const NO_ACTIVE_SQL_TRANSACTION_FOR_BRANCH_TRANSACTION = '25005';
    const READ_ONLY_SQL_TRANSACTION = '25006';
    const SCHEMA_AND_DATA_STATEMENT_MIXING_NOT_SUPPORTED = '25007';
    const NO_ACTIVE_SQL_TRANSACTION = '25P01';
    const IN_FAILED_SQL_TRANSACTION = '25P02';
    /* 26 - Invalid SQL Statement Name */
    const INVALID_SQL_STATEMENT_NAME = '26000';
    /* 27 - Triggered Data Change Violation */
    const TRIGGERED_DATA_CHANGE_VIOLATION = '27000';
    /* 28 - Invalid Authorization Specification */
    const INVALID_AUTHORIZATION_SPECIFICATION = '28000';
    const INVALID_PASSWORD = '28P01';
    /* 2B - Dependent Privilege Descriptors Still Exist */
    const DEPENDENT_PRIVILEGE_DESCRIPTORS_STILL_EXIST = '2B000';
    const DEPENDENT_OBJECTS_STILL_EXIST = '2BP01';
    /* 2D - Invalid Transaction Termination */
    const INVALID_TRANSACTION_TERMINATION = '2D000';
    /* 2F - SQL Routine Exception */
    const SQL_ROUTINE_EXCEPTION = '2F000';
    const FUNCTION_EXECUTED_NO_RETURN_STATEMENT = '2F005';
    const MODIFYING_SQL_DATA_NOT_PERMITTED = '2F002';
    const PROHIBITED_SQL_STATEMENT_ATTEMPTED = '2F003';
    const READING_SQL_DATA_NOT_PERMITTED = '2F004';
    /* 34 - Invalid Cursor Name */
    const INVALID_CURSOR_NAME = '34000';
    /* 38 - External Routine Exception */
    const EXTERNAL_ROUTINE_EXCEPTION = '38000';
    const CONTAINING_SQL_NOT_PERMITTED = '38001';
    #const MODIFYING_SQL_DATA_NOT_PERMITTED = '38002';
    #const PROHIBITED_SQL_STATEMENT_ATTEMPTED = '38003';
    #const READING_SQL_DATA_NOT_PERMITTED = '38004';
    /* 39 - External Routine Invocation Exception */
    const EXTERNAL_ROUTINE_INVOCATION_EXCEPTION = '39000';
    const INVALID_SQLSTATE_RETURNED = '39001';
    #const NULL_VALUE_NOT_ALLOWED = '39004';
    const TRIGGER_PROTOCOL_VIOLATED = '39P01';
    const SRF_PROTOCOL_VIOLATED = '39P02';
    /* 3B - Savepoint Exception */
    const SAVEPOINT_EXCEPTION = '3B000';
    const INVALID_SAVEPOINT_SPECIFICATION = '3B001';
    /* 3D - Invalid Catalog Name */
    const INVALID_CATALOG_NAME = '3D000';
    /* 3F - Invalid Schema Name */
    const INVALID_SCHEMA_NAME = '3F000';
    /* 40 - Transaction Rollback */
    const TRANSACTION_ROLLBACK = '40000';
    const TRANSACTION_INTEGRITY_CONSTRAINT_VIOLATION = '40002';
    const SERIALIZATION_FAILURE = '40001';
    const STATEMENT_COMPLETION_UNKNOWN = '40003';
    const DEADLOCK_DETECTED = '40P01';
    /* 42 - Syntax Error or Access Rule Violation */
    const SYNTAX_ERROR_OR_ACCESS_RULE_VIOLATION = '42000';
    const SYNTAX_ERROR = '42601';
    const INSUFFICIENT_PRIVILEGE = '42501';
    const CANNOT_COERCE = '42846';
    const GROUPING_ERROR = '42803';
    const WINDOWING_ERROR = '42P20';
    const INVALID_RECURSION = '42P19';
    const INVALID_FOREIGN_KEY = '42830';
    const INVALID_NAME = '42602';
    const NAME_TOO_LONG = '42622';
    const RESERVED_NAME = '42939';
    const DATATYPE_MISMATCH = '42804';
    const INDETERMINATE_DATATYPE = '42P18';
    const COLLATION_MISMATCH = '42P21';
    const INDETERMINATE_COLLATION = '42P22';
    const WRONG_OBJECT_TYPE = '42809';
    const UNDEFINED_COLUMN = '42703';
    const UNDEFINED_FUNCTION = '42883';
    const UNDEFINED_TABLE = '42P01';
    const UNDEFINED_PARAMETER = '42P02';
    const UNDEFINED_OBJECT = '42704';
    const DUPLICATE_COLUMN = '42701';
    const DUPLICATE_CURSOR = '42P03';
    const DUPLICATE_DATABASE = '42P04';
    const DUPLICATE_FUNCTION = '42723';
    const DUPLICATE_PREPARED_STATEMENT = '42P05';
    const DUPLICATE_SCHEMA = '42P06';
    const DUPLICATE_TABLE = '42P07';
    const DUPLICATE_ALIAS = '42712';
    const DUPLICATE_OBJECT = '42710';
    const AMBIGUOUS_COLUMN = '42702';
    const AMBIGUOUS_FUNCTION = '42725';
    const AMBIGUOUS_PARAMETER = '42P08';
    const AMBIGUOUS_ALIAS = '42P09';
    const INVALID_COLUMN_REFERENCE = '42P10';
    const INVALID_COLUMN_DEFINITION = '42611';
    const INVALID_CURSOR_DEFINITION = '42P11';
    const INVALID_DATABASE_DEFINITION = '42P12';
    const INVALID_FUNCTION_DEFINITION = '42P13';
    const INVALID_PREPARED_STATEMENT_DEFINITION = '42P14';
    const INVALID_SCHEMA_DEFINITION = '42P15';
    const INVALID_TABLE_DEFINITION = '42P16';
    const INVALID_OBJECT_DEFINITION = '42P17';
    /* 44 - WITH CHECK OPTION Violation */
    const WITH_CHECK_OPTION_VIOLATION = '44000';
    /* 53 - Insufficient Resources */
    const INSUFFICIENT_RESOURCES = '53000';
    const DISK_FULL = '53100';
    const OUT_OF_MEMORY = '53200';
    const TOO_MANY_CONNECTIONS = '53300';
    const CONFIGURATION_LIMIT_EXCEEDED = '53400';
    /* 54 - Program Limit Exceeded */
    const PROGRAM_LIMIT_EXCEEDED = '54000';
    const STATEMENT_TOO_COMPLEX = '54001';
    const TOO_MANY_COLUMNS = '54011';
    const TOO_MANY_ARGUMENTS = '54023';
    /* 55 - Object Not In Prerequisite State */
    const OBJECT_NOT_IN_PREREQUISITE_STATE = '55000';
    const OBJECT_IN_USE = '55006';
    const CANT_CHANGE_RUNTIME_PARAM = '55P02';
    const LOCK_NOT_AVAILABLE = '55P03';
    /* 57 - Operator Intervention */
    const OPERATOR_INTERVENTION = '57000';
    const QUERY_CANCELED = '57014';
    const ADMIN_SHUTDOWN = '57P01';
    const CRASH_SHUTDOWN = '57P02';
    const CANNOT_CONNECT_NOW = '57P03';
    const DATABASE_DROPPED = '57P04';
    /* 58 - System Error (errors external to PostgreSQL itself) */
    const SYSTEM_ERROR = '58000';
    const IO_ERROR = '58030';
    const UNDEFINED_FILE = '58P01';
    const DUPLICATE_FILE = '58P02';
    /* F0 - Configuration File Error */
    const CONFIG_FILE_ERROR = 'F0000';
    const LOCK_FILE_EXISTS = 'F0001';
    /* HV - Foreign Data Wrapper Error (SQL/MED) */
    const FDW_ERROR = 'HV000';
    const FDW_COLUMN_NAME_NOT_FOUND = 'HV005';
    const FDW_DYNAMIC_PARAMETER_VALUE_NEEDED = 'HV002';
    const FDW_FUNCTION_SEQUENCE_ERROR = 'HV010';
    const FDW_INCONSISTENT_DESCRIPTOR_INFORMATION = 'HV021';
    const FDW_INVALID_ATTRIBUTE_VALUE = 'HV024';
    const FDW_INVALID_COLUMN_NAME = 'HV007';
    const FDW_INVALID_COLUMN_NUMBER = 'HV008';
    const FDW_INVALID_DATA_TYPE = 'HV004';
    const FDW_INVALID_DATA_TYPE_DESCRIPTORS = 'HV006';
    const FDW_INVALID_DESCRIPTOR_FIELD_IDENTIFIER = 'HV091';
    const FDW_INVALID_HANDLE = 'HV00B';
    const FDW_INVALID_OPTION_INDEX = 'HV00C';
    const FDW_INVALID_OPTION_NAME = 'HV00D';
    const FDW_INVALID_STRING_LENGTH_OR_BUFFER_LENGTH = 'HV090';
    const FDW_INVALID_STRING_FORMAT = 'HV00A';
    const FDW_INVALID_USE_OF_NULL_POINTER = 'HV009';
    const FDW_TOO_MANY_HANDLES = 'HV014';
    const FDW_OUT_OF_MEMORY = 'HV001';
    const FDW_NO_SCHEMAS = 'HV00P';
    const FDW_OPTION_NAME_NOT_FOUND = 'HV00J';
    const FDW_REPLY_HANDLE = 'HV00K';
    const FDW_SCHEMA_NOT_FOUND = 'HV00Q';
    const FDW_TABLE_NOT_FOUND = 'HV00R';
    const FDW_UNABLE_TO_CREATE_EXECUTION = 'HV00L';
    const FDW_UNABLE_TO_CREATE_REPLY = 'HV00M';
    const FDW_UNABLE_TO_ESTABLISH_CONNECTION = 'HV00N';
    /* P0 - PL/pgSQL Error */
    const PLPGSQL_ERROR = 'P0000';
    const RAISE_EXCEPTION = 'P0001';
    const NO_DATA_FOUND = 'P0002';
    const TOO_MANY_ROWS = 'P0003';
    /* XX - Internal Error */
    const INTERNAL_ERROR = 'XX000';
    const DATA_CORRUPTED = 'XX001';
    const INDEX_CORRUPTED = 'XX002';

    protected $result_resource;
    protected $sql;
    protected $queryParameters;

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
        $this->queryParameters = array();
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

    public function getQuery()
    {
        return $this->sql;
    }

    public function setQueryParameters(array $parameters)
    {
        $this->queryParameters = $parameters;
        return $this;
    }

    public function getQueryParameters()
    {
        return $this->queryParameters;
    }
}
