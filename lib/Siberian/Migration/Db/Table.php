<?php
/**
 * Class Siberian_Migration_Db_Table
 *
 * Migration Class to update DB reflecting latest schema.
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.1.0
 *
 */

class Siberian_Migration_Db_Table extends Zend_Db_Table_Abstract {

    static public $debug = false;

    public $config = null;
    public $logger = null;
    public $log_info = "migration_%s_info";
    public $log_error = "migration_%s_error";

    public $protected_defaults = array(
        "CURRENT_TIMESTAMP",
    );

    protected $dbName = null;
    protected $tableName = null;
    protected $schemaPath = null;
    protected $tableEngine = "InnoDB";
    protected $tableCharset = "utf8";
    protected $tableCollate = "utf8_unicode_ci";
    protected $queries = array();

    public $localFields = array();
    public $schemaFields = array();
    public $primaryKeys = array();
    public $uniqueKeys = array();
    public $indexes = array();


    /**
     * Siberian_Migration_Db_Table constructor.
     *
     * @param string $table_name
     * @param array $config
     */
    public function __construct($table_name, $config = array()) {
        parent::__construct($config);

        $this->tableName = $table_name;
        $this->config = $this->getAdapter()->getConfig();
        $this->logger = Zend_Registry::get("logger");
        $this->dbName = $this->config['dbname'];
    }

    public function setEngine($engine) {
        $this->tableEngine = $engine;
    }

    public function getEngine() {
        return $this->tableEngine;
    }

    public function setCharset($charset) {
        $this->tableCharset = $charset;
    }

    public function getCharset() {
        return $this->tableCharset;
    }

    public function setCollate($collate) {
        $this->tableCollate = $collate;
    }

    public function getCollate() {
        return $this->tableCollate;
    }

    public function setSchemaPath($schema_path) {
        $this->schemaPath = $schema_path;
    }

    public function getSchemaPath() {
        return $this->schemaPath;
    }

    /**
     * Check whether a Table exists or not
     *
     * @param $try_create
     * @return boolean
     * @throws Exception
     */
	public function tableExists($try_create = true, $update = true) {

        try {
            $this->getAdapter()->describeTable($this->tableName);

            if($update) {
                $this->updateTable();
            }

            return true;
        }
        catch(Exception $e) {
            /** Try to create the table if it doesn't exist yet for installation purpose. */
            if($try_create AND !$this->createTable()) {
                throw new Siberian_Exception(__("Unable to create table '{$this->tableName}', with previous Exception %s.", $e->getMessage()));
            }
        }

        return false;
    }

    /**
     * Reads & import the latest schema revision for the current Table
     *
     * @throws Exception
     */
    public function readSchema() {
        if(!empty($this->schemaFields)) {
            return;
        }

        if(is_readable($this->schemaPath)) {
            $schemas = array();
            include $this->schemaPath;
            $this->schemaFields = $schemas[$this->tableName];
        } else {
            throw new Siberian_Exception("Unable to read latest schema for '{$this->tableName}', errors: ".implode("\n", $this->queries).".");
        }
    }

    /**
     * Reads & import the current database schema for the given Table
     *
     * @throws Exception
     */
    public function readDatabase() {
        if(!empty($this->localFields)) {
            return;
        }

        $this->exportDatabase(false);
    }

    /**
     * Export local database to php array
     *
     * @param bool $save
     * @throws Exception
     */
    public function exportDatabase($save = true) {
        
        /** Fetching columns */
        $request = "SELECT
                  `COLUMN_NAME`,
                  `COLUMN_DEFAULT`,
                  `IS_NULLABLE`,
                  `CHARACTER_SET_NAME`,
                  `COLLATION_NAME`,
                  `COLUMN_TYPE`,
                  `COLUMN_KEY`,
                  `EXTRA`
              FROM `information_schema`.`COLUMNS`
              WHERE `TABLE_SCHEMA` = '{$this->dbName}'
              AND `TABLE_NAME` = '{$this->tableName}';
         ";

        $result = $this->query($request);
        $schema = array();
        foreach($result->fetchAll() as $column) {
            $schema[] = $column;
        }

        /** Fetch foreign keys */
        $requestFk = "SELECT
                    `tb1`.`COLUMN_NAME`,
                    `tb1`.`CONSTRAINT_NAME`,
                    `tb1`.`REFERENCED_TABLE_NAME`,
                    `tb1`.`REFERENCED_COLUMN_NAME`,
                    `tb2`.`UPDATE_RULE`,
                    `tb2`.`DELETE_RULE`
                FROM `information_schema`.`KEY_COLUMN_USAGE` AS `tb1`
                INNER JOIN `information_schema`.`REFERENTIAL_CONSTRAINTS` AS `tb2`
                  ON `tb1`.`CONSTRAINT_NAME` = `tb2`.`CONSTRAINT_NAME`
                WHERE `tb1`.`TABLE_SCHEMA` = '{$this->dbName}'
                AND `tb1`.`TABLE_NAME` = '{$this->tableName}'
        ";

        $resultFk = $this->query($requestFk);
        $fks = array();
        foreach($resultFk->fetchAll() as $fk) {
            list($column, $fk_name, $ref_table, $ref_column, $on_update, $on_delete) = array_values($fk);

            $fks[$column] = array(
                "table" => $ref_table,
                "column" => $ref_column,
                "name" => $fk_name,
                "on_update" => $on_update,
                "on_delete" => $on_delete,
            );
        }

        /** Fetch indexes */
        $requestIdxs = "SHOW INDEX
                        FROM `{$this->tableName}`
                        WHERE `Key_name` NOT LIKE 'PRIMARY';";
        $resultIdxs = $this->query($requestIdxs);
        $this->indexes = array();

        foreach($resultIdxs as $index) {
            $keyname = $index["Key_name"];
            $column_name = $index["Column_name"];
            $index_type = $index["Index_type"];
            $is_null = (!empty($index["Null"]));
            $is_unique = (!$index["Non_unique"]);

            $this->indexes[$column_name] = array(
                "key_name" => $keyname,
                "index_type" => $index_type,
                "is_null" => $is_null,
                "is_unique" => $is_unique,
            );
        }

        $updateDate = date('Y-m-d');

        # Building the raw php array
        $raw_schema = "<?php
/**
 *
 * Schema definition for '{$this->tableName}'
 *
 * Last update: {$updateDate}
 *
 */

\$schemas = (!isset(\$schemas)) ? array() : \$schemas;
\$schemas['{$this->tableName}'] = array(";
        foreach($schema as $column) {
            list($name, $default, $nullable, $character_set, $collation, $col_type, $col_key, $extra) = array_values($column);

            if ($default[0] === "'" && $default[strlen($default) - 1] === "'" ) {
                $default = substr($default, 1, strlen($default) - 2);
            }

            $col_type = addslashes($col_type);
            $nullable = ($nullable == 'YES') ? "'is_null' => true," : "";
            $auto_increment = ($extra == 'auto_increment') ? "'auto_increment' => true," : "";
            $is_primary = ($col_key == 'PRI') ? "'primary' => true," : "";
            $is_unique = ($col_key == 'UNI') ? "'unique' => true," : "";
            $charset = (!empty($character_set)) ? "'charset' => '{$character_set}'," : "";
            $collation = (!empty($collation)) ? "'collation' => '{$collation}'," : "";
            $default = ($default != "") ? "'default' => '{$default}'," : "";
            $fk = "";
            if(isset($fks[$name])) {
                $fk = "
        'foreign_key' => array(";
                foreach($fks[$name] as $key => $value) {
                    $value = (is_bool($value)) ? ($value) ? 'true' : 'false' : "'{$value}'";

                    $fk .= "
            '{$key}' => {$value},";
                }
                $fk .= "
        ),";
            }

            $idxs = "";
            if(isset($this->indexes[$name])) {
                $idxs = "
        'index' => array(";
                foreach($this->indexes[$name] as $key => $value) {
                    $value = (is_bool($value)) ? ($value) ? 'true' : 'false' : "'{$value}'";

                    $idxs .= "
            '{$key}' => {$value},";
                }
                $idxs .= "
        ),";
            }

            $raw_schema .= "
    '{$name}' => array(
        'type' => '{$col_type}',
        {$auto_increment}
        {$is_primary}
        {$nullable}
        {$charset}
        {$collation}
        {$default}
        {$is_unique}
        {$fk}
        {$idxs}
    ),";
        }

        $raw_schema .= "
);";

        /** Cleaning up blank lines */
        $raw_schema = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $raw_schema);

        $schema_path = realpath(APPLICATION_PATH."/../");
        $version = Siberian_Version::VERSION;

        if(!file_exists($schema_path."/var/schema/{$version}")) {
            mkdir($schema_path."/var/schema/{$version}", 0777, true);
        }

        if($save) {
            $schema_path .= "/var/schema/{$version}/{$this->tableName}.php";
            echo "Exporting {$this->tableName} to : {$schema_path}\n";
            file_put_contents($schema_path, $raw_schema);
        } else {
            if(!file_exists($schema_path."/var/tmp/{$version}")) {
                mkdir($schema_path."/var/tmp/{$version}", 0777, true);
            }
            $schema_path .= "/var/tmp/{$version}/{$this->tableName}.php";
            file_put_contents($schema_path, $raw_schema);

            if(is_readable($schema_path)) {
                $schemas = array();
                include $schema_path;
                $this->localFields = $schemas[$this->tableName];

                /** Cleaning tmp file */
                unlink($schema_path);
            } else {
                throw new Siberian_Exception("Unable to save schema from database for '{$this->tableName}'.");
            }

        }

    }

    /**
     * This method compare local database against latest schema, and update column definition
     *
     * @todo update me.
     *
     * @throws Exception
     */
    public function updateTable() {
        $this->readDatabase();
        $this->readSchema();

        $migration_log = sprintf($this->log_info, Siberian_Version::VERSION);

        $fix_utc_dates = false;

        /** Walks against the latest schema adding columns */
        foreach($this->schemaFields as $column_name => $options) {
            if(!isset($this->localFields[$column_name])) {
#                $this->logger->info("Updating table: '{$this->tableName}' column: '{$column_name}'", $migration_log, true);
                $request = $this->parseAlter($column_name);
                $this->execSafe($request);

                if(preg_match("/^(created|updated)_at_utc$/", $column_name)) {
                    $fix_utc_dates = true;
                }
            }
        }

        if($fix_utc_dates) {
            $cols = array();
            if(isset($this->localFields["created_at"])) $cols[] = "created_at";
            if(isset($this->localFields["updated_at"])) $cols[] = "updated_at";
            if(count($cols) > 0) {
                $requestDates = "SELECT ".join($cols, ", ")." FROM `{$this->tableName}` WHERE ".join(array_map(function($col) { return $col."_utc=0"; }, $cols), " OR ");
                $resultDates = $this->query($requestDates)->fetchAll();
                foreach($resultDates as $row) {
                    foreach($cols as $col) {
                        $col_utc = $col."_utc";
                        if(isset($row[$col]) && @intval($row[$col_utc]) < 1) {
                            $date = new Zend_Date($row[$col]);
                            $timestamp = $date->getTimestamp();
                            $this->query("UPDATE `{$this->tableName}` SET `{$col_utc}`=$timestamp WHERE `$col`='{$row[$col]}';");
                        }
                    }
                }
            }
        }
    }

    /**
     * This method compare local database against latest schema, and update keys
     *
     * @throws Exception
     */
    public function updateForeignKeys() {
        $this->readDatabase();
        $this->readSchema();

        $migration_log = sprintf($this->log_info, Siberian_Version::VERSION);

        foreach($this->schemaFields as $column_name => $options) {
            if(isset($options['foreign_key']) && !isset($this->localFields[$column_name]['foreign_key'])) {
                //$this->logger->info("Updating table: '{$this->tableName}' column: '{$column_name}' foreign key", $migration_log, true);
                $request = $this->parseFk($column_name);
                $this->execSafe($request);
            }
        }
    }

    /**
     * Parsing MySQL line
     *
     * @param $column_name
     * @return bool
     * @throws Exception
     */
    public function parseAlter($column_name) {
        /** Column */
        $col = $this->schemaFields[$column_name];

        /** Assigning values */
        $col_default = isset($col['default']) ? $col['default'] : "";
        $col_collation = isset($col['collation']) ? $col['collation'] : "";

        if(!in_array($col_default, $this->protected_defaults) && ($col_default != "")) { /** Protect if value */
            $col_default = "'{$col_default}'";
        }

        $type           = $col['type'];
        $default        = ($col_default != "") ? "DEFAULT {$col_default}" : "";
        $collate        = ($col_collation != "") ? "COLLATE {$col_collation}" : "";
        $null           = (isset($col['is_null'])) ? "" : "NOT NULL";
        $auto_increment = (isset($col['auto_increment'])) ? "AUTO_INCREMENT" : "";

        /** Default NULL is is_null & no default value */
        if(empty($default) && isset($col['is_null'])) {
            $default = "DEFAULT NULL";
        }

        /** This method could lead to mistakes, but the column order doesn't really matter so let's do it. */
        $keys = array_keys($this->schemaFields);
        $after_index = array_search($column_name, $keys) - 1;
        $after = "";
        if(isset($keys[$after_index])) {
            $column = $keys[$after_index];
            $after = "AFTER `{$column}`";
        }
        $index = "";
        if(isset($col['index'])) {
            $upper_name = strtoupper($column_name);
            $index = ", ADD INDEX `INDEX_{$upper_name}` (`{$column_name}`)";
        }

        /** Index/Unique on multiple columns */
        # ALTER TABLE `siberiancms_mae`.`application` ADD UNIQUE `UNIQUE_TADAM` (`domain`, `subdomain`);

        $alter = "ALTER TABLE `{$this->tableName}` ADD `{$column_name}` {$type} {$collate} {$null} {$default} {$auto_increment} {$after} {$index};";

        return $alter;
    }

    /**
     * Parsing MySQL foreign key line
     *
     * @param $column_name
     * @return bool
     * @throws Exception
     */
    public function parseFk($column_name) {
        /** Column */
        $col = $this->schemaFields[$column_name]['foreign_key'];

        /** Assigning values */
        $table = $col['table'];
        $column = $col['column'];
        $on_delete = $col['on_delete'];
        $on_update = $col['on_update'];
        $fk_name = $col['name'];

        $fk =  "ALTER TABLE `{$this->tableName}`
                ADD CONSTRAINT `{$fk_name}` FOREIGN KEY (`{$column_name}`) REFERENCES `{$table}` (`{$column}`)
                ON DELETE {$on_delete}
                ON UPDATE {$on_update};";

        return $fk;
    }

    /**
     * @param $column_name
     * @return string
     */
    public function parseColumn($column_name) {
        /** Column */
        $col = $this->schemaFields[$column_name];

        /** Assigning values */
        $col_default = isset($col['default']) ? $col['default'] : "";
        $col_collation = isset($col['collation']) ? $col['collation'] : "";

        if(!in_array($col_default, $this->protected_defaults) && ($col_default != "")) { /** Protect if value */
            $col_default = "'{$col_default}'";
        }

        $type           =  $col['type'];
        $default        = ($col_default != "") ? " DEFAULT {$col_default}" : "";
        $collate        = ($col_collation != "") ? " COLLATE {$col_collation}" : "";
        $null           = (isset($col['is_null'])) ? "" : " NOT NULL";
        $auto_increment = (isset($col['auto_increment'])) ? " AUTO_INCREMENT" : "";

        /** Default NULL is is_null & no default value */
        if($default == "" && isset($col['is_null'])) {
            $default = " DEFAULT NULL";
        }

        /** Saving primary key(s) for further parsing */
        if(isset($col["primary"])) {
            $this->primaryKeys[] = $column_name;
        }

        /** Saving unique key(s) for further parsing */
        if(!empty($col["unique"])) {
            $this->uniqueKeys[] = $column_name;
        }

        $column = "\t`{$column_name}` {$type}{$collate}{$null}{$default}{$auto_increment}";

        return $column;
    }

    public function parsePrimaryKeys() {
        if(empty($this->primaryKeys)) {
            return "";
        }

        $keys = array();
        foreach($this->primaryKeys as $primary_key) {
            $keys[] = "`{$primary_key}`";
        }
        $keys = implode(",", $keys);

        $primary = "\tPRIMARY KEY ({$keys})";

        return $primary;
    }

    public function parseIndexes() {
        if(empty($this->indexes)) {
            return array();
        }

        $idxs = array();
        foreach($this->indexes as $index_name => $columns) {

            $indexes = array();
            $index_type = "";
            $index_unique = false;

            foreach($columns as $column) {
                $column_name = $column["column_name"];
                if(empty($index_type) && !empty($column["index_type"])) {
                    $index_type = $column["index_type"];
                }

                if($column["is_unique"] === true) {
                    $index_unique = true;
                }

                $indexes[] = "`{$column_name}`";
            }

            $index_type = (!empty($index_type) && strtoupper($index_type) != "BTREE") ? "USING {$index_type}" : "";

            $key = ($index_unique) ? "UNIQUE KEY" : "KEY";

            $cols = implode(',', $indexes);
            $idxs[] = "\t{$key} `{$index_name}` ({$cols}) {$index_type}";
        }

        return $idxs;
    }

    public function createTable() {
        //$this->readDatabase();
        $this->readSchema();

        $create = "CREATE TABLE `{$this->tableName}` (\n";

        $lines = array();

        /** Parse all columns */
        foreach($this->schemaFields as $column_name => $options) {
            $lines[] = $this->parseColumn($column_name);
        }

        /** Parse PRIMARY key(s) */
        $lines[] = $this->parsePrimaryKeys();

        /** Parse INDEXES */
        $lines = array_merge($lines, $this->parseIndexes());

        $create .= implode(",\n", array_filter($lines));

        $create .= "\n) ENGINE={$this->tableEngine} DEFAULT CHARSET={$this->tableCharset} COLLATE={$this->tableCollate};";
        
        try {
            $this->execSafe($create);
        } catch(Exception $e) {
            return false;
        }

        return true;
    }

    public function start() {
        $this->getAdapter()->beginTransaction();
    }

    public function commit() {
        $this->getAdapter()->commit();
    }

    public function revert() {
        $this->getAdapter()->rollBack();
    }

    public function query($sql) {
        $this->queries[] = $sql;

        return $this->getAdapter()->query($sql);
    }

    public function execSafe($query) {

        try {
            $this->start();
            $this->query("SET foreign_key_checks = 0;");
            $this->query($query);
            $this->query("SET foreign_key_checks = 1;");
            $this->commit();

        } catch(Exception $e) {

            $this->revert();
            $this->query("SET foreign_key_checks = 1;");

            $message_error = "#99009: execSafe error on: '{$this->tableName}' request: '{$query}' execSafe error on: '{$this->tableName}' request: '{$query}'";

            $migration_log = sprintf($this->log_error, Siberian_Version::VERSION);
            $this->logger->info($message_error, $migration_log, false);
            throw new Siberian_Exception($message_error);
        }
    }

    static public function setDebug($debug) {
        self::$debug = $debug;
    }

    static public function getDebug() {
        return self::$debug;
    }

}