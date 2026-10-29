<?php

namespace Siberian\LoyaltyCard;

use Zend_Db_Adapter_Abstract;

/**
 * One-time QRCodes of the loyalty card (#851).
 *
 * The QRCode of an employee encodes a fixed unlock code, so a photo of it is enough to claim points for ever.
 * A one-time QRCode encodes a random token instead: only its hash is stored, it is valid for a short time and the
 * first scan consumes it. The consume is a single UPDATE ... WHERE used_at IS NULL, so two scans of the same token
 * can not both win.
 *
 * Class QrToken
 * @package Siberian\LoyaltyCard
 */
class QrToken
{
    /**
     * Table created by resources/db/schema/loyalty_card_qr_token.php
     */
    const TABLE = 'loyalty_card_qr_token';

    /**
     * Seconds a one-time QRCode stays valid
     */
    const TTL = 900;

    /**
     * Seconds after which the rows of expired tokens are deleted
     */
    const KEEP = 86400;

    /**
     * @return string 32 hexadecimal characters
     */
    public static function generate(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * @param mixed $token
     * @return bool
     */
    public static function isWellFormed($token): bool
    {
        return is_string($token) && preg_match('/^[0-9a-f]{32}$/', $token) === 1;
    }

    /**
     * @param string $token
     * @return string
     */
    public static function hash(string $token): string
    {
        return hash('sha256', $token);
    }

    /**
     * Stores a new token for an employee and returns it. This is the only time the clear token exists.
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param int $passwordId
     * @param int $valueId
     * @param int|null $now unix time, for the tests
     * @param int $ttl
     * @return string
     */
    public static function issue(Zend_Db_Adapter_Abstract $db, int $passwordId, int $valueId, ?int $now = null, int $ttl = self::TTL): string
    {
        $now = $now ?? time();

        $db->delete(self::TABLE, ['expires_at < ?' => date('Y-m-d H:i:s', $now - self::KEEP)]);

        $token = self::generate();
        $db->insert(self::TABLE, [
            'password_id' => $passwordId,
            'value_id' => $valueId,
            'token_hash' => self::hash($token),
            'expires_at' => date('Y-m-d H:i:s', $now + $ttl),
            'created_at' => date('Y-m-d H:i:s', $now),
        ]);

        return $token;
    }

    /**
     * Consumes a token: returns the id of the employee password it was issued for, once, and null for anything else
     * (unknown, expired, already used, another feature, not a token).
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param mixed $token
     * @param int $valueId
     * @param int|null $now unix time, for the tests
     * @return int|null
     */
    public static function consume(Zend_Db_Adapter_Abstract $db, $token, int $valueId, ?int $now = null): ?int
    {
        if (!self::isWellFormed($token)) {
            return null;
        }

        $nowString = date('Y-m-d H:i:s', $now ?? time());
        $hash = self::hash($token);

        $consumed = $db->update(self::TABLE, ['used_at' => $nowString], [
            'token_hash = ?' => $hash,
            'value_id = ?' => $valueId,
            'used_at IS NULL',
            'expires_at > ?' => $nowString,
        ]);
        if ((int) $consumed !== 1) {
            return null;
        }

        $passwordId = $db->fetchOne(
            $db->select()->from(self::TABLE, ['password_id'])->where('token_hash = ?', $hash)
        );

        return $passwordId === false ? null : (int) $passwordId;
    }
}
