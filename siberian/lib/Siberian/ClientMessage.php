<?php

namespace Siberian;

/**
 * What an app user may read in an error message.
 *
 * The mobile controllers answer `['error' => true, 'message' => $e->getMessage()]`, which is right for our own messages
 * ("This e-mail address is already in use") and wrong for a database error: "SQLSTATE[HY093]: Invalid parameter
 * number" tells the user nothing, and tells everybody the tables and queries of the server.
 * The technical text stays in the logs and in the development answers, the app gets a plain sentence.
 */
class ClientMessage
{
    /** What a message of a database (or of the database layer) looks like */
    private const TECHNICAL = [
        '/SQLSTATE\[/i',
        '/\bPDOException\b/i',
        '/\bZend_Db_\w+/i',
        '/You have an error in your SQL syntax/i',
        '/Integrity constraint violation/i',
        '/Duplicate entry .+ for key/i',
        '/Unknown column .+ in /i',
        '/Data too long for column/i',
        '/(Base table or view|Table .+) (not found|doesn\'t exist)/i',
        '/Lock wait timeout exceeded|Deadlock found when trying to get lock/i',
        '/MySQL server has gone away|Lost connection to MySQL server/i',
        '/Fetching the (many-to-many )?rows of \w+ failed/i',
    ];

    /**
     * @param mixed $message
     * @return bool true when the message is the text of a database error
     */
    public static function isTechnical($message): bool
    {
        if (!is_string($message) || $message === '') {
            return false;
        }

        foreach (self::TECHNICAL as $pattern) {
            if (preg_match($pattern, $message) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * The payload of an error answer, with the technical message replaced.
     *
     * @param array $payload the answer about to be sent
     * @param string $replacement the plain sentence the app shows instead
     * @param callable|null $log receives the original message
     * @return array
     */
    public static function clean(array $payload, string $replacement, ?callable $log = null): array
    {
        if (empty($payload['error']) || !self::isTechnical($payload['message'] ?? null)) {
            return $payload;
        }

        if ($log !== null) {
            $log($payload['message']);
        }

        $payload['message'] = $replacement;

        return $payload;
    }
}
