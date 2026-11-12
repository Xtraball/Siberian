<?php

namespace Siberian;

/**
 * Class \Siberian\IconvFallback
 *
 * A PHP without the iconv extension makes Zend_Date, Zend_Locale_Format and slugify() end in a fatal error
 * ("Call to undefined function iconv_substr()"). iconv is a requirement of Siberian, but installations exist
 * without it. These methods give the same answer on UTF-8 text with mbstring (a requirement as well), and
 * IconvPolyfill.php maps the iconv functions onto them, only when the extension is missing.
 */
class IconvFallback
{
    /**
     * Letters that do not decompose into a base letter and an accent.
     */
    private const ASCII_MAP = [
        'ß' => 'ss', 'æ' => 'ae', 'Æ' => 'AE', 'œ' => 'oe', 'Œ' => 'OE', 'ø' => 'o', 'Ø' => 'O',
        'đ' => 'd', 'Đ' => 'D', 'ł' => 'l', 'Ł' => 'L', 'ð' => 'd', 'Ð' => 'D', 'þ' => 'th', 'Þ' => 'TH',
        'ı' => 'i', 'ħ' => 'h', 'Ħ' => 'H', '’' => "'", '‘' => "'", '“' => '"', '”' => '"', '–' => '-', '—' => '-',
        '€' => 'EUR', '£' => 'GBP', '©' => '(c)', '®' => '(r)', '™' => '(tm)', '…' => '...',
    ];

    /**
     * @param string $string
     * @param string|null $encoding
     * @return int
     */
    public static function strlen($string, $encoding = null)
    {
        return mb_strlen((string) $string, $encoding ?: 'UTF-8');
    }

    /**
     * iconv_substr: a length of null means "to the end".
     *
     * @param string $string
     * @param int $offset
     * @param int|null $length
     * @param string|null $encoding
     * @return string
     */
    public static function substr($string, $offset, $length = null, $encoding = null)
    {
        return mb_substr((string) $string, (int) $offset, $length, $encoding ?: 'UTF-8');
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @param int $offset
     * @param string|null $encoding
     * @return int|false
     */
    public static function strpos($haystack, $needle, $offset = 0, $encoding = null)
    {
        if ($needle === '') {
            return false;
        }
        $encoding = $encoding ?: 'UTF-8';
        if ($offset > mb_strlen((string) $haystack, $encoding)) {
            return false;
        }
        return mb_strpos((string) $haystack, (string) $needle, (int) $offset, $encoding);
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @param string|null $encoding
     * @return int|false
     */
    public static function strrpos($haystack, $needle, $encoding = null)
    {
        if ($needle === '') {
            return false;
        }
        return mb_strrpos((string) $haystack, (string) $needle, 0, $encoding ?: 'UTF-8');
    }

    /**
     * iconv($from, $to, $string): the //TRANSLIT and //IGNORE suffixes are honoured, an unknown encoding gives false.
     *
     * @param string $from
     * @param string $to
     * @param string $string
     * @return string|false
     */
    public static function convert($from, $to, $string)
    {
        $translit = stripos($to, '//TRANSLIT') !== false;
        $to = preg_replace('~//.*$~', '', $to);
        $from = preg_replace('~//.*$~', '', $from);
        $known = array_map('strtoupper', mb_list_encodings());
        $aliases = ['US-ASCII' => 'ASCII', 'CP1252' => 'Windows-1252', 'MACROMAN' => 'ISO-8859-1'];
        $toName = $aliases[strtoupper($to)] ?? $to;
        $fromName = $aliases[strtoupper($from)] ?? $from;
        if (!in_array(strtoupper($toName), $known, true) || !in_array(strtoupper($fromName), $known, true)) {
            return false;
        }

        $string = (string) $string;
        if (strtoupper($toName) === 'ASCII' && $translit) {
            if (strtoupper($fromName) !== 'UTF-8') {
                $string = mb_convert_encoding($string, 'UTF-8', $fromName);
            }
            return self::toAscii($string);
        }

        return mb_convert_encoding($string, $toName, $fromName);
    }

    /**
     * Approximates iconv('UTF-8', 'ASCII//TRANSLIT'): accents are dropped, known letters are spelled, the rest is "?".
     *
     * @param string $string UTF-8 text
     * @return string
     */
    public static function toAscii($string)
    {
        $string = (string) $string;
        if ($string === '' || !preg_match('/[^\x00-\x7F]/', $string)) {
            return $string;
        }

        $string = strtr($string, self::ASCII_MAP);
        if (class_exists('\Transliterator', false)) {
            $transliterator = \Transliterator::create('Any-Latin; Latin-ASCII');
            $result = $transliterator ? $transliterator->transliterate($string) : false;
            if (is_string($result)) {
                $string = $result;
            }
        } elseif (class_exists('\Normalizer')) {
            $decomposed = \Normalizer::normalize($string, \Normalizer::FORM_D);
            if (is_string($decomposed)) {
                $string = preg_replace('/\p{Mn}+/u', '', $decomposed);
            }
        }

        return preg_replace('/[^\x00-\x7F]/u', '?', $string);
    }
}
