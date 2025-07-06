<?php

declare(strict_types=1);

namespace App\Next\Core\Utility\Engine;

use Doctrine\DBAL\Connection;
use Symfony\Component\Yaml\Yaml;

abstract class AbstractEngine
{
    protected string $schemaPath;
    protected array $schemaFields = [];
    protected array $localFields = [];
    protected string $charset;
    protected string $tableEngine;
    protected bool $force = false;
    protected Connection $connection;

    public function __construct(string $table_name, array $config = [], Connection $connection)
    {
        $this->_name = $table_name;
        $this->schemaPath = $config['schema_path'] ?? '';
        $this->connection = $connection;
    }

    /**
     * @return void
     * @throws EngineException
     */
    public function readSchema(): void
    {
        if (!empty($this->schemaFields)) {
            return;
        }

        if (is_readable($this->schemaPath)) {
            $schemaContent = file_get_contents($this->schemaPath);
            $schema = Yaml::parse($schemaContent);

            $this->schemaFields = $schema['schema'][$this->_name]['columns'];
            $this->charset = $schema['schema'][$this->_name]['metadata']['charset'];
            $this->tableEngine = $schema['schema'][$this->_name]['metadata']['engine'];
        } else {
            throw new EngineException("Unable to read latest schema for '{$this->_name}'.");
        }
    }

    public function setForce(bool $force): void
    {
        $this->force = $force;
    }

    abstract public function updateTable(): void;
    abstract public function createTable(): void;
    abstract public function addColumn(string $column_name, string $position): void;
}
