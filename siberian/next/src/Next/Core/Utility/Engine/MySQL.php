<?php

declare(strict_types=1);

namespace App\Next\Core\Utility\Engine;

use Doctrine\DBAL\Exception as DBALException;

class MySQL extends AbstractEngine
{
    /**
     * @return void
     * @throws EngineException
     * @throws SchemaException
     */
    public function updateTable(): void
    {
        $this->readSchema();
        $this->readDatabase();

        foreach ($this->schemaFields as $column_name => $options) {
            if (!isset($this->localFields[$column_name])) {
                $this->addColumn($column_name, 'after');
            } elseif ($this->force) {
                $this->alterColumn($column_name);
            }
        }
    }

    /**
     * @return void
     * @throws EngineException
     * @throws SchemaException
     */
    public function createTable(): void
    {
        $this->readSchema();
        $create = "CREATE TABLE `{$this->_name}` (\n";

        $lines = [];
        foreach ($this->schemaFields as $column_name => $options) {
            $lines[] = $this->parseColumn($column_name);
        }

        $create .= implode(",\n", array_filter($lines));
        $create .= "\n) ENGINE={$this->tableEngine} DEFAULT CHARSET={$this->charset};";

        $this->execSafe($create);
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
        $after = $position === 'after' ? 'AFTER `some_column`' : 'FIRST';
        $alter = "ALTER TABLE `{$this->_name}` ADD COLUMN {$columnDefinition} {$after};";
        $this->execSafe($alter);
    }

    /**
     * @param string $column_name
     * @return void
     * @throws SchemaException
     */
    private function alterColumn(string $column_name): void
    {
        $columnDefinition = $this->parseColumn($column_name);
        $alter = "ALTER TABLE `{$this->_name}` MODIFY COLUMN {$columnDefinition};";
        $this->execSafe($alter);
    }

    private function parseColumn(string $column_name): string
    {
        $col = $this->schemaFields[$column_name];
        $type = $col['type'];
        $null = isset($col['is_null']) ? '' : 'NOT NULL';
        $default = isset($col['default']) ? "DEFAULT '{$col['default']}'" : '';
        $auto_increment = isset($col['auto_increment']) ? 'AUTO_INCREMENT' : '';

        return "`{$column_name}` {$type} {$null} {$default} {$auto_increment}";
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

    /**
     * @return void
     * @throws SchemaException
     */
    private function readDatabase(): void
    {
        try {
            $result = $this->connection->executeQuery("SHOW FULL COLUMNS FROM `{$this->_name}`");
        } catch (DBALException $e) {
            throw new SchemaException("Error reading database schema for table {$this->_name}", 0, $e);
        }

        foreach ($result as $column) {
            $this->localFields[$column['FIELD_NAME']] = $column;
        }
    }
}
