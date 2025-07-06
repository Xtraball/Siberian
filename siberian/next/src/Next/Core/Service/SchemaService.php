<?php

declare(strict_types=1);

namespace App\Next\Core\Service;

use App\Next\Core\Utility\SchemaUtility;
use Doctrine\DBAL\Connection;
use App\Next\Core\Utility\Engine\EngineException;

class SchemaService
{
    private Connection $defaultConnection;
    private Connection $legacyConnection;

    public function __construct(Connection $defaultConnection, Connection $legacyConnection)
    {
        $this->defaultConnection = $defaultConnection;
        $this->legacyConnection = $legacyConnection;
    }

    /**
     * @param string $pathPattern
     * @return array
     * @throws EngineException
     */
    public function processSchemas(string $pathPattern): array
    {
        return SchemaUtility::findAndProcessSchemas(
            $pathPattern,
            $this->defaultConnection,
            $this->legacyConnection
        );
    }
}
