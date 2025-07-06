<?php

declare(strict_types=1);

namespace App\Next\Core\Utility;

use App\Next\Core\Utility\Engine\MySQL;
use App\Next\Core\Utility\Engine\PostgreSQL;
use App\Next\Core\Utility\Engine\EngineException;
use App\Next\Core\Utility\Engine\SchemaException;
use Doctrine\DBAL\Connection;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class SchemaUtility
{
    /**
     * @param string $pathPattern
     * @param Connection $defaultConnection
     * @param Connection $legacyConnection
     * @return array
     * @throws EngineException
     */
    public static function findAndProcessSchemas(
        string     $pathPattern,
        Connection $defaultConnection,
        Connection $legacyConnection
    ): array
    {
        $finder = new Finder();
        $finder->files()->in($pathPattern)->name('*.yaml')->name('*.yml');

        $schemaInstances = [];

        foreach ($finder as $file) {
            $schemaContent = $file->getContents();
            $schema = Yaml::parse($schemaContent);

            foreach ($schema['schema'] as $tableName => $tableSchema) {
                $engine = $tableSchema['metadata']['engine'] ?? '';
                $schemaPath = $file->getRealPath();

                $instance = self::factorySchemaInstance(
                    $engine,
                    $tableName,
                    $schemaPath,
                    $defaultConnection,
                    $legacyConnection
                );
                $schemaInstances[] = $instance;
            }
        }

        return $schemaInstances;
    }

    /**
     * @param string $engine
     * @param string $tableName
     * @param string $schemaPath
     * @param Connection $defaultConnection
     * @param Connection $legacyConnection
     * @return MySQL|PostgreSQL
     * @throws EngineException|SchemaException
     */
    private static function factorySchemaInstance(
        string     $engine,
        string     $tableName,
        string     $schemaPath,
        Connection $defaultConnection,
        Connection $legacyConnection
    ): MySQL|PostgreSQL
    {
        $config = [
            'schema_path' => $schemaPath,
            // Add other configuration options as needed
        ];

        return match (strtolower($engine)) {
            'mysql' => new MySQL($tableName, $config, $legacyConnection),
            'postgresql', 'pg' => new PostgreSQL($tableName, $config, $defaultConnection),
            default => throw new EngineException("Unsupported database engine: {$engine}"),
        };
    }
}
