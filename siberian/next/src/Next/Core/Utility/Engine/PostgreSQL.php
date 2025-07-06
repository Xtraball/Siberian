<?php

declare(strict_types=1);

namespace App\Next\Core\Utility\Engine;

use Doctrine\DBAL\Exception as DBALException;

class PostgreSQL extends AbstractEngine
{
    /**
     * @param string $table_name
     * @param array $config
     * @param $connection
     * @throws SchemaException
     */
    public function __construct(string $table_name, array $config, $connection)
    {
        parent::__construct($table_name, $config, $connection);
        $this->ensureUuidOsspExtension();
    }

    /**
     * @return void
     * @throws SchemaException|EngineException
     */
    public function updateTable(): void
    {
        $this->readSchema();

        if (!$this->tableExists()) {
            $this->createTable();
        } else {
            $this->readDatabase();
            $this->handleIdOrUuidColumn();

            foreach ($this->schemaFields as $column_name => $options) {
                if (!isset($this->localFields[$column_name])) {
                    $this->addColumn($column_name, 'after');
                } elseif ($this->force) {
                    $this->alterColumn($column_name);
                }
            }

            $this->updateForeignKeys();
        }
    }

    /**
     * @return void
     * @throws SchemaException|EngineException
     */
    public function createTable(): void
    {
        $this->readSchema();
        $create = "CREATE TABLE \"{$this->_name}\" (\n";

        $lines = [];
        foreach ($this->schemaFields as $column_name => $options) {
            $lines[] = $this->parseColumn($column_name);
        }

        // Add ID or UUID column if not defined in schema
        if (!$this->hasIdOrUuidColumn()) {
            $lines[] = $this->parseIdOrUuidColumn();
        }

        $create .= implode(",\n", array_filter($lines));
        $create .= "\n);";

        $this->execSafe($create);
        $this->updateForeignKeys();
    }

    /**
     * @param string $column_name
     * @return void
     * @throws SchemaException
     */
    private function alterColumn(string $column_name): void
    {
        $newColumnDefinition = $this->schemaFields[$column_name];
        $existingColumnDefinition = $this->localFields[$column_name];

        // Extract type and length from the type string
        list($existingType, $existingLength) = $this->parseTypeAndLength($existingColumnDefinition['type']);
        list($newType, $newLength) = $this->parseTypeAndLength($newColumnDefinition['type']);

        // Check for data type compatibility
        if (!$this->isDataTypeCompatible($existingColumnDefinition['type'], $newColumnDefinition['type'])) {
            throw new SchemaException("Changing column type from {$existingColumnDefinition['type']} to {$newColumnDefinition['type']} is not allowed as it may lead to data loss.");
        }

        // Check for column length changes for types like varchar
        if ($newLength !== null && $existingLength !== null) {
            if ($newLength < $existingLength) {
                throw new SchemaException("Reducing column length from {$existingLength} to {$newLength} is not allowed as it may lead to data loss.");
            } elseif ($newLength > $existingLength) {
                $this->logWarning("Increasing column length for column {$column_name} from {$existingLength} to {$newLength}.");
            }
        }

        // Check for default value changes
        if (isset($newColumnDefinition['default']) && isset($existingColumnDefinition['default'])) {
            if ($newColumnDefinition['default'] !== $existingColumnDefinition['default']) {
                $this->logWarning("Default value for column {$column_name} is changing from {$existingColumnDefinition['default']} to {$newColumnDefinition['default']}.");
            }
        }

        // Check for nullability changes
        if (isset($newColumnDefinition['is_null']) && isset($existingColumnDefinition['is_null'])) {
            if ($newColumnDefinition['is_null'] !== $existingColumnDefinition['is_null']) {
                $this->logWarning("Nullability for column {$column_name} is changing from " . ($existingColumnDefinition['is_null'] ? 'NULL' : 'NOT NULL') . " to " . ($newColumnDefinition['is_null'] ? 'NULL' : 'NOT NULL') . ".");
            }
        }

        $columnDefinition = $this->parseColumn($column_name);
        $alter = "ALTER TABLE \"{$this->_name}\" ALTER COLUMN {$columnDefinition};";
        $this->execSafe($alter);
    }

    /**
     * @param string $typeString
     * @return array
     */
    private function parseTypeAndLength(string $typeString): array
    {
        $length = null;
        $type = $typeString;

        // Check if the type string contains a length (e.g., varchar(255))
        if (preg_match('/^([a-z]+)\((\d+)\)$/i', $typeString, $matches)) {
            $type = $matches[1];
            $length = (int)$matches[2];
        }

        return [$type, $length];
    }

    /**
     * @param string $existingType
     * @param string $newType
     * @return bool
     */
    private function isDataTypeCompatible(string $existingType, string $newType): bool
    {
        // Define compatible data types
        $compatibleTypes = [
            'int' => ['bigint', 'smallint'],
            'varchar' => ['text'],
            // Add more compatibility rules as needed
        ];

        if ($existingType === $newType) {
            return true;
        }

        return isset($compatibleTypes[$existingType]) && in_array($newType, $compatibleTypes[$existingType], true);
    }

    /**
     * @param string $message
     * @return void
     */
    private function logWarning(string $message): void
    {
        // Implement your logging mechanism here
        error_log("WARNING: " . $message);
    }

    /**
     * @param string $column_name
     * @param string $position
     * @return void
     * @throws SchemaException
     */
    public function addColumn(string $column_name, string $position): void
    {
        $columnDefinition = $this->parseColumn($column_name);

        $alter = "ALTER TABLE \"{$this->_name}\" ADD COLUMN {$columnDefinition};";
        $this->execSafe($alter);
    }

    private function parseColumn(string $column_name): string
    {
        $col = $this->schemaFields[$column_name];
        $type = $col['type'];
        $null = isset($col['is_null']) ? '' : 'NOT NULL';
        $default = isset($col['default']) ? $this->formatDefaultValue($col['default']) : '';

        if ($type === 'uuid' && empty($default)) {
            $default = "DEFAULT uuid_generate_v4()";
        }

        return "\"{$column_name}\" {$type} {$null} {$default}";
    }

    private function formatDefaultValue(string $value): string
    {
        // Check if the default value is a function call
        $functionCalls = ['uuid_generate_v4()', 'CURRENT_TIMESTAMP', 'NOW()'];
        if (in_array($value, $functionCalls, true)) {
            return "DEFAULT {$value}";
        }

        // For other default values, escape them as strings
        return "DEFAULT '{$value}'";
    }

    /**
     * @return void
     * @throws SchemaException
     */
    private function updateForeignKeys(): void
    {
        foreach ($this->schemaFields as $column_name => $options) {
            if (isset($options['foreign_key']) && !isset($this->localFields[$column_name]['foreign_key'])) {
                $this->addForeignKey($column_name, $options['foreign_key']);
            }
        }
    }

    /**
     * @param string $column_name
     * @param array $foreignKey
     * @return void
     * @throws SchemaException
     */
    private function addForeignKey(string $column_name, array $foreignKey): void
    {
        $fkName = $foreignKey['name'];
        $refTable = $foreignKey['table'];
        $refColumn = $foreignKey['column'];
        $onDelete = $foreignKey['on_delete'];
        $onUpdate = $foreignKey['on_update'];

        $alter = "ALTER TABLE \"{$this->_name}\" ADD CONSTRAINT \"{$fkName}\" FOREIGN KEY (\"{$column_name}\") REFERENCES \"{$refTable}\" (\"{$refColumn}\") ON DELETE {$onDelete} ON UPDATE {$onUpdate};";
        $this->execSafe($alter);
    }

    /**
     * @param string $query
     * @return void
     * @throws SchemaException
     */
    private function execSafe(string $query): void
    {
        try {
            $this->connection->executeStatement($query);
        } catch (DBALException $e) {
            throw new SchemaException("Error executing query: {$query}", 0, $e);
        }
    }

    private function readDatabase(): void
    {
        $query = "
        SELECT
            column_name,
            data_type,
            character_maximum_length,
            column_default,
            is_nullable
        FROM
            information_schema.columns
        WHERE
            table_name = '{$this->_name}';
    ";

        $result = $this->connection->executeQuery($query)->fetchAllAssociative();

        foreach ($result as $column) {
            $this->localFields[$column['column_name']] = [
                'type' => $this->formatColumnType($column),
                'default' => $column['column_default'],
                'is_null' => $column['is_nullable'] === 'YES',
            ];
        }
    }

    /**
     * @param array $column
     * @return string
     */
    private function formatColumnType(array $column): string
    {
        $type = $column['data_type'];

        // Append length if applicable
        if ($column['character_maximum_length'] !== null) {
            $type .= "({$column['character_maximum_length']})";
        }

        return $type;
    }

    /**
     * @return bool
     * @throws SchemaException
     */
    private function tableExists(): bool
    {
        try {
            $schemaManager = $this->connection->createSchemaManager();
            return $schemaManager->tablesExist([$this->_name]);
        } catch (DBALException $e) {
            throw new SchemaException("Error checking if table exists: {$this->_name}", 0, $e);
        }
    }

    /**
     * @return void
     * @throws SchemaException
     */
    private function handleIdOrUuidColumn(): void
    {
        if (!$this->hasIdOrUuidColumn()) {
            $this->addColumn('uuid', 'first');
        }
    }

    private function hasIdOrUuidColumn(): bool
    {
        return isset($this->localFields['id']) || isset($this->localFields['uuid']) || isset($this->schemaFields['id']) || isset($this->schemaFields['uuid']);
    }

    private function parseIdOrUuidColumn(): string
    {
        return '"uuid" uuid NOT NULL DEFAULT uuid_generate_v4() PRIMARY KEY';
    }

    /**
     * @return void
     * @throws SchemaException
     */
    private function ensureUuidOsspExtension(): void
    {
        try {
            $this->connection->executeStatement("CREATE EXTENSION IF NOT EXISTS \"uuid-ossp\";");
        } catch (DBALException $e) {
            throw new SchemaException("Error ensuring uuid-ossp extension is enabled.", 0, $e);
        }
    }
}
