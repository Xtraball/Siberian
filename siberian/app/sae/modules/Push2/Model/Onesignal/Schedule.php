<?php

namespace Push2\Model\Onesignal;

/**
 * Class Schedule
 *
 * Pure helper for the date of a scheduled push, kept apart from the controller so it can be tested alone.
 */
class Schedule
{
    /**
     * @param string|null $sendAfter "M d Y H:i:s O", e.g. "Feb 13 2023 00:00:00 +01:00"
     * @param string|null $deliveryTimeOfDay "H:i", replaces the time of $sendAfter when present
     * @return int|null timestamp of the scheduled send, null when the push is not scheduled
     */
    public static function timestamp($sendAfter, $deliveryTimeOfDay = null)
    {
        if (empty($sendAfter)) {
            return null;
        }

        $date = date_create_from_format('M d Y H:i:s O', $sendAfter);
        if ($date === false) {
            return null;
        }

        if (strpos((string) $deliveryTimeOfDay, ':') !== false) {
            list($hour, $minute) = explode(':', $deliveryTimeOfDay);
            $date->setTime((int) $hour, (int) $minute);
        }

        return $date->getTimestamp();
    }

    /**
     * The date shown in the push list: a scheduled push shows when it was sent, the others keep their creation date.
     *
     * @param string $createdAt
     * @param string|null $sendAfter
     * @param string|null $deliveryTimeOfDay
     * @return string "Y-m-d H:i:s"
     */
    public static function displayDate($createdAt, $sendAfter, $deliveryTimeOfDay = null)
    {
        $timestamp = self::timestamp($sendAfter, $deliveryTimeOfDay);

        return $timestamp === null ? $createdAt : date('Y-m-d H:i:s', $timestamp);
    }
}
