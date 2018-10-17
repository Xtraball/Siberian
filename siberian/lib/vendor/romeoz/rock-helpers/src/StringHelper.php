<?php
namespace rock\helpers;

/**
 * Helper "String"
 *
 * @package rock\helpers
 */
class StringHelper
{
    /**
     * Returns the number of bytes in the given string.
     * This method ensures the string is treated as a byte array by using `mb_strlen()`.
     * @param string $string the string being measured for length
     * @return integer the number of bytes in the given string.
     */
    public static function byteLength($string)
    {
        return mb_strlen($string, '8bit');
    }

    /**
     * Returns the portion of string specified by the start and length parameters.
     * This method ensures the string is treated as a byte array by using `mb_substr()`.
     * @param string $string the input string. Must be one character or longer.
     * @param int $start the starting position
     * @param int|null $length the desired portion length
     * @return string the extracted part of string, or FALSE on failure or an empty string.
     * @see http://www.php.net/manual/en/function.substr.php
     */
    public static function byteSubstr($string, $start, $length = null)
    {
        return mb_substr($string, $start, $length === null ? mb_strlen($string, '8bit') : $length, '8bit');
    }

    /**
     * Truncates a string to the number of characters specified.
     *
     * @param string $string The string to truncate.
     * @param integer $length How many characters from original string to include into truncated string.
     * @param string $suffix String to append to the end of truncated string.
     * @param string $encoding The charset to use, defaults to charset currently used by application.
     * @return string
     */
    public static function truncate($string, $length = 4, $suffix = '...', $encoding = 'UTF-8')
    {
        $length = (int)$length;
        if (empty($string) || $length === 0) {
            return $string;
        }

        if (mb_strlen($string, $encoding) > $length) {
            return trim(mb_substr($string, 0, $length, $encoding)) . $suffix;
        } else {
            return $string;
        }
    }

    /**
     * Truncates a string to the number of words specified.
     *
     * @param string $string The string to truncate.
     * @param integer $length How many words from original string to include into truncated string.
     * @param string $suffix String to append to the end of truncated string.
     * @param string $encoding The charset to use, defaults to charset currently used by application.
     * @return string
     */
    public static function truncateWords($string, $length = 100, $suffix = '...', $encoding = 'UTF-8')
    {
        if (empty($string) || $length === 0 ||
            mb_strlen($string, $encoding) <= $length
        ) {
            return $string;
        }
        $string = mb_substr($string, 0, $length, $encoding);
        if (mb_substr($string, -1, 1, $encoding) != ' ') {
            $string = mb_substr($string, 0, mb_strrpos($string, ' ', 0, $encoding), $encoding);
        }

        if (!$string = trim($string)) {
            return '';
        }
        return $string . $suffix;
    }

    /**
     * String to uppercase.
     *
     * @param string $string string
     * @param string $encoding The charset to use, defaults to charset currently used by application.
     * @return string
     */
    public static function upper($string, $encoding = 'UTF-8')
    {
        if (empty($string)) {
            return $string;
        }
        $string = mb_strtoupper($string, $encoding);

        return $string;
    }

    /**
     * String to lowercase.
     *
     * @param string $string string
     * @param string $encoding The charset to use, defaults to charset currently used by application.
     * @return string
     */
    public static function lower($string, $encoding = 'UTF-8')
    {
        if (empty($string)) {
            return $string;
        }
        $string = mb_strtolower($string, $encoding);

        return $string;
    }

    /**
     * Upper first char.
     *
     * @param string $string string.
     * @param string $encoding The charset to use, defaults to charset currently used by application.
     * @return string
     */
    public static function upperFirst($string, $encoding = 'UTF-8')
    {
        if (empty($string)) {
            return $string;
        }
        $fc = mb_strtoupper(mb_substr($string, 0, 1, $encoding), $encoding);

        return $fc . mb_substr($string, 1, mb_strlen($string, $encoding), $encoding);
    }

    /**
     * Lower first char.
     *
     * @param string $string string.
     * @param string $encoding The charset to use, defaults to charset currently used by application.
     * @return string
     */
    public static function lowerFirst($string, $encoding = 'UTF-8')
    {
        if (empty($string)) {
            return $string;
        }
        $fc = mb_strtolower(mb_substr($string, 0, 1, $encoding), $encoding);

        return $fc . mb_substr($string, 1, mb_strlen($string, $encoding), $encoding);
    }

    /**
     * Encodes special characters into HTML entities.
     *
     * @param string $content the content to be encoded
     * @param boolean $doubleEncode whether to encode HTML entities in `$content`. If false,
     *                              HTML entities in `$content` will not be further encoded.
     * @param string $encoding The charset to use, defaults to charset currently used by application.
     * @return string the encoded content
     * @see decode()
     * @see http://www.php.net/manual/en/function.htmlspecialchars.php
     */
    public static function encode($content, $doubleEncode = true, $encoding = 'UTF-8')
    {
        return htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, $encoding, $doubleEncode);
    }

    /**
     * Decodes special HTML entities back to the corresponding characters.
     * @param string $content the content to be decoded
     * @return string the decoded content
     * @see encode()
     * @see http://www.php.net/manual/en/function.htmlspecialchars-decode.php
     */
    public static function decode($content)
    {
        return htmlspecialchars_decode($content, ENT_QUOTES);
    }

    /**
     * Escape string single-quotes.
     *
     * @param string $string string
     * @return string
     */
    public static function quotes($string)
    {
        if (empty($string)) {
            return $string;
        }

        return '\'' . $string . '\'';
    }

    /**
     * Escape string double-quotes.
     *
     * @param string $string string
     * @return string
     */
    public static function doubleQuotes($string)
    {
        if (empty($string)) {
            return $string;
        }

        return '"' . $string . '"';
    }

    /**
     * Begin trim words (or)
     *
     * ```php
     * StringHelper::ltrimWord('foo text', ['foo', 'bar']); // ' text'
     * StringHelper::ltrimWord('bar text', ['foo', 'bar']); // ' text'
     * ```
     *
     * @param string $string
     * @param array $words
     * @return string
     */
    public static function ltrimWords($string, array $words)
    {
        return static::trimPattern($string, '/^(' . implode('|', $words) . ')\\s?/u');
    }

    /**
     * End trim words (or)
     * @param string $string
     * @param array $words
     * @return string
     */
    public static function rtrimWords($string, array $words)
    {
        return static::trimPattern($string, '/\\s?(' . implode('|', $words) . ')$/u');
    }

    /**
     * Trim spaces.
     *
     * @param string $string string
     * @return string
     */
    public static function removeSpaces($string)
    {
        return static::trimPattern($string, '/\s+/i');
    }

    /**
     * Trim by pattern.
     *
     * @param string $string
     * @param string $pattern regexp pattern.
     * @param int $limit
     * @return string
     */
    public static function trimPattern($string, $pattern, $limit = -1)
    {
        return preg_replace($pattern, '', $string, $limit);
    }

    /**
     * Check contains word or char in string.
     *
     * @param string $string
     * @param string $contains
     * @param bool $caseSensitive Case sensitive search. Default is false.
     * @param string $encoding
     * @return bool
     */
    public static function contains($string, $contains, $caseSensitive = false, $encoding = 'UTF-8')
    {
        if ($caseSensitive === false) {
            return false !== mb_stripos($string, $contains, 0, $encoding);
        }
        return false !== mb_strpos($string, $contains, 0, $encoding);
    }

    /**
     * Translate characters or replace sub-strings (case insensitive version of strtr).
     *
     * @param string $string
     * @param string|array $from
     * @param string|null $to
     * @param string $encoding
     * @return string
     * @link http://php.net/manual/ru/function.strtr.php#82051
     */
    public static function stritr($string, $from, $to = null, $encoding = 'UTF-8')
    {
        if (function_exists('stritr')) {
            return stritr($string, $from, $to);
        }
        if (is_string($from)) {
            $to = strval($to);
            $from = mb_substr($from, 0, min(mb_strlen($from, $encoding), mb_strlen($to, $encoding)), $encoding);
            $to = mb_substr($to, 0, min(mb_strlen($from, $encoding), mb_strlen($to, $encoding)), $encoding);
            $product = strtr($string, (mb_strtoupper($from, $encoding) . mb_strtolower($from, $encoding)), ($to . $to));
            return $product;
        } elseif (is_array($from)) {
            $pos1 = 0;
            $product = $string;
            while (count($from) > 0) {
                $positions = [];
                foreach ($from as $_from => $to) {
                    if (($pos2 = mb_stripos($product, $_from, $pos1, $encoding)) === false) {
                        unset($from[$_from]);
                    } else {
                        $positions[$_from] = $pos2;
                    }
                }
                if (count($from) <= 0) {
                    break;
                }
                $winner = min($positions);
                $key = array_search($winner, $positions);
                $product = (mb_substr($product, 0, $winner, $encoding) . $from[$key] . mb_substr($product, $winner + mb_strlen($key, $encoding), null, $encoding));
                $pos1 = ($winner + mb_strlen($from[$key], $encoding));
            }
            return $product;
        }
        return $string;
    }

    /**
     * Generator of random character string
     *
     * @param int $length length of string
     * @return string
     */
    public static function randChars($length = 6)
    {
        $chars = [
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l',
            'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'v', 'x', 'y', 'z',
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L',
            'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'V', 'X', 'Y', 'Z',
            '1', '2', '3', '4', '5', '6', '7', '8', '9', '0'
        ];

        $result = '';
        $count = count($chars) - 1;
        while ($length > 0) {
            $result .= $chars[mt_rand(0, $count)];
            --$length;
        }

        return $result;
    }

    /**
     * Replacement of random characters in the string.
     *
     * @param string $string string
     * @param string $replaceTo
     * @return string
     */
    public static function replaceRandChars($string, $replaceTo = '*')
    {
        $chars = preg_split('/(?<!^)(?!$)/u', $string);
        $count = count($chars);
        $len = ceil($count / 2);
        while ($len > 0) {
            $index = mt_rand(0, $count - 1);
            if ($chars[$index] === $replaceTo) {
                ++$len;
            }
            $chars[$index] = '*';
            --$len;
        }

        return implode("", $chars);
    }

    /**
     * Get all the words with uppercase.
     *
     * @param string $string string
     * @return array
     */
    public static function getWordsUppFirst($string)
    {
        preg_match_all('/\b[A-ZА-ЯЁ]+\\w*/u', $string, $match);

        return !empty($match[0]) ? $match[0] : [];
    }

    /**
     * Concat begin
     * @param string $value
     * @param string $concat
     * @param null $default
     * @return null|string
     *
     * ```php
     * StringHelper::lconcat('world', 'hello '); // hello world
     * StringHelper::lconcat(null, ' hello '); // null
     * ```
     */
    public static function lconcat(&$value, $concat, $default = null)
    {
        return $value ? $concat . $value : $default;
    }

    /**
     * Concat end.
     * @param string $value
     * @param string $concat
     * @param null $default
     * @return null|string
     *
     * ```php
     * StringHelper::rconcat('hello', ' world'); // hello world
     * StringHelper::rconcat(null, ' world'); // null
     * ```
     */
    public static function rconcat(&$value, $concat, $default = null)
    {
        return $value ? $value . $concat : $default;
    }

    /**
     * Replace.
     *
     * @param string $string string.
     * @param array $placeholders array placeholders for replace.
     * @param bool $removeBraces remove braces `{{...}}`.
     * @param string $pattern pattern for replace.
     * @return string
     */
    public static function replace($string, array $placeholders = [], $removeBraces = true, $pattern = '/\{{1,2}([\\w\.]+)\}{1,2}/')
    {
        if (strpos($string, '{') !== false) {
            return trim(preg_replace_callback(
                $pattern,
                function ($match) use ($placeholders, $removeBraces) {
                    if (isset($placeholders[$match[1]])) {
                        return self::_preparePlaceholder($placeholders[$match[1]]);
                    } elseif ($removeBraces) {
                        return '';
                    }
                    return $match[0];
                },
                $string
            ));
        }
        return $string;
    }

    /**
     * Check value is regexp pattern.
     *
     * @param string $subject string
     * @return bool
     */
    public static function isRegexp(&$subject)
    {
        $subject = trim($subject);
        if (isset($subject[0]) && $subject[0] === '~') {
            $subject = ltrim($subject, '~');
            return true;
        }

        return false;
    }

    /**
     * Returns the trailing name component of a path.
     * This method is similar to the php function `basename()` except that it will
     * treat both \ and / as directory separators, independent of the operating system.
     * This method was mainly created to work on php namespaces. When working with real
     * file paths, php's `basename()` should work fine for you.
     * Note: this method is not aware of the actual filesystem, or path components such as "..".
     *
     * @param string $path A path string.
     * @param string $suffix If the name component ends in suffix this will also be cut off.
     * @return string the trailing name component of the given path.
     * @see http://www.php.net/manual/en/function.basename.php
     */
    public static function basename($path, $suffix = '')
    {
        if (($len = mb_strlen($suffix)) > 0 && mb_substr($path, -$len) == $suffix) {
            $path = mb_substr($path, 0, -$len);
        }
        $path = rtrim(str_replace('\\', '/', $path), '/\\');
        if (($pos = mb_strrpos($path, '/')) !== false) {
            return mb_substr($path, $pos + 1);
        }
        return $path;
    }

    /**
     * Check if given string starts with specified substring.
     * Binary and multibyte safe.
     *
     * @param string $string Input string
     * @param string $with Part to search
     * @param bool $caseSensitive Case sensitive search. Default is true.
     * @param string $encoding
     * @return bool Returns true if first input starts with second input, false otherwise
     */
    public static function startsWith($string, $with, $caseSensitive = true, $encoding = 'UTF-8')
    {
        if (!$bytes = static::byteLength($with)) {
            return true;
        }
        if ($caseSensitive) {
            return strncmp($string, $with, $bytes) === 0;
        } else {
            return mb_strtolower(mb_substr($string, 0, $bytes, '8bit'), $encoding) === mb_strtolower($with, $encoding);
        }
    }

    /**
     * Check if given string ends with specified substring.
     * Binary and multibyte safe.
     *
     * @param string $string
     * @param string $with
     * @param bool $caseSensitive Case sensitive search. Default is true.
     * @param string $encoding
     * @return bool Returns true if first input ends with second input, false otherwise
     */
    public static function endsWith($string, $with, $caseSensitive = true, $encoding = 'UTF-8')
    {
        if (!$bytes = static::byteLength($with)) {
            return true;
        }
        if ($caseSensitive) {
            // Warning check, see http://php.net/manual/en/function.substr-compare.php#refsect1-function.substr-compare-returnvalues
            if (static::byteLength($string) < $bytes) {
                return false;
            }
            return substr_compare($string, $with, -$bytes, $bytes) === 0;
        } else {
            return mb_strtolower(mb_substr($string, -$bytes, null, '8bit'), $encoding) === mb_strtolower($with, $encoding);
        }
    }

    /**
     * Convert variables to string.
     *
     * ```php
     * StringHelper::toString(true); // "true"
     * StringHelper::toString('bar text', "'"); // "'bar text'"
     * ```
     *
     * @param mixed $value
     * @param string|null $quote
     * @param int $serializer
     * @return string
     */
    public static function toString($value, $quote = null, $serializer = Serialize::SERIALIZE_JSON)
    {
        if ($value === true) {
            return 'true';
        }

        if ($value === false) {
            return 'false';
        }

        if (is_string($value) && is_string($quote)) {
            return $quote . $value . $quote;
        }

        if (is_array($value)) {
            return Serialize::serialize($value, $serializer);
        }

        return $value;
    }

    private static function  _preparePlaceholder($placeholder)
    {
        if (is_object($placeholder)) {
            return get_class($placeholder);
        } elseif (is_array($placeholder)) {
            return Json::encode($placeholder);
        }
        return $placeholder;
    }
}