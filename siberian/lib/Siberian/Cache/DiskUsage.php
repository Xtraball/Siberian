<?php

namespace Siberian\Cache;

/**
 * What the dashboard shows of the web space: sizes in bytes, and where they stand against an informative quota.
 *
 * Pure rules, no framework: the measure itself is Siberian\Cache::getDiskUsage(true), run once a day by the cron.
 */
class DiskUsage
{
    /**
     * System config code of the quota, in GB. Empty: no quota.
     */
    const QUOTA_KEY = 'web_space_quota_gb';

    /**
     * Share of the quota from which the dashboard asks to take a look, and from which it is urgent.
     */
    const WARN_PERCENT = 80;
    const DANGER_PERCENT = 95;

    /**
     * text key => bytes key of every measured folder
     */
    const FOLDERS = [
        'total' => 'total_bytes',
        'log_size' => 'log_bytes',
        'cache_size' => 'cache_bytes',
        'cache_images_size' => 'cache_images_bytes',
        'tmp_size' => 'tmp_bytes',
    ];

    /**
     * Size as "du -h" prints it: 12K, 4.3M, 891M, 1.2G.
     *
     * @param int $bytes
     * @return string
     */
    public static function human($bytes)
    {
        $bytes = max(0, (int) $bytes);
        if ($bytes < 1024) {
            return $bytes . 'B';
        }

        $units = ['K', 'M', 'G', 'T'];
        $value = $bytes / 1024;
        $unit = 0;
        while ($value >= 1024 && $unit < count($units) - 1) {
            $value /= 1024;
            $unit++;
        }

        $rounded = $value < 10 ? round($value, 1) : round($value);

        return rtrim(rtrim(number_format($rounded, 1, '.', ''), '0'), '.') . $units[$unit];
    }

    /**
     * Bytes of a size printed by "du -h" (the value of a measure taken before the bytes were kept), null when unknown.
     *
     * @param mixed $text
     * @return int|null
     */
    public static function parse($text)
    {
        if (!is_string($text) || !preg_match('/^\s*([0-9]+(?:[.,][0-9]+)?)\s*([BKMGT]?)i?B?\s*$/i', $text, $m)) {
            return null;
        }

        $power = ['' => 0, 'B' => 0, 'K' => 1, 'M' => 2, 'G' => 3, 'T' => 4][strtoupper($m[2])];

        return (int) round((float) str_replace(',', '.', $m[1]) * pow(1024, $power));
    }

    /**
     * A measure with its bytes: the ones missing are read back from the text, and what no folder explains is "other".
     *
     * @param array $usage
     * @return array
     */
    public static function normalize(array $usage)
    {
        foreach (self::FOLDERS as $textKey => $bytesKey) {
            if (!isset($usage[$bytesKey]) || !is_numeric($usage[$bytesKey])) {
                $usage[$bytesKey] = self::parse($usage[$textKey] ?? null);
            }
        }

        $usage['other_bytes'] = null;
        if ($usage['total_bytes'] !== null) {
            $known = (int) $usage['log_bytes'] + (int) $usage['cache_bytes'] +
                (int) $usage['cache_images_bytes'] + (int) $usage['tmp_bytes'];
            $usage['other_bytes'] = max(0, (int) $usage['total_bytes'] - $known);
        }

        return $usage;
    }

    /**
     * The quota in bytes, null when there is none.
     *
     * @param mixed $gb
     * @return int|null
     */
    public static function quotaBytes($gb)
    {
        $gb = self::validQuota($gb);

        return is_float($gb) ? (int) round($gb * 1024 * 1024 * 1024) : null;
    }

    /**
     * A quota typed by an admin: null for none (empty), the size in GB, false when it is not a size.
     *
     * @param mixed $input
     * @return float|null|false
     */
    public static function validQuota($input)
    {
        if ($input === null || $input === false || trim((string) $input) === '') {
            return null;
        }

        $value = str_replace(',', '.', trim((string) $input));
        if (!is_numeric($value) || (float) $value <= 0 || (float) $value > 1000000) {
            return false;
        }

        return round((float) $value, 2);
    }

    /**
     * The measure with the quota: quota_gb, quota_bytes, quota_percent of it used, and quota_level none|ok|warn|danger.
     *
     * @param array $usage a normalized measure
     * @param mixed $quotaGb the config value
     * @return array
     */
    public static function withQuota(array $usage, $quotaGb)
    {
        $quota = self::validQuota($quotaGb);
        $usage['quota_gb'] = is_float($quota) ? $quota : null;
        $usage['quota_bytes'] = self::quotaBytes($quotaGb);
        $usage['quota_percent'] = null;
        $usage['quota_level'] = 'none';

        if ($usage['quota_bytes'] !== null && ($usage['total_bytes'] ?? null) !== null) {
            $percent = $usage['total_bytes'] / $usage['quota_bytes'] * 100;
            $usage['quota_percent'] = round($percent, 1);
            $usage['quota_level'] = $percent >= self::DANGER_PERCENT ? 'danger' :
                ($percent >= self::WARN_PERCENT ? 'warn' : 'ok');
        }

        return $usage;
    }
}
