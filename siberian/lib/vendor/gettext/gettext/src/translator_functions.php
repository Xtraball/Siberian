<?php

use Gettext\BaseTranslator;

if (!function_exists('gettext_vsprintf')) {
    /**
     * vsprintf() for a translated text. Since PHP 8 a broken placeholder in a translation (machine translations do
     * produce "Súbor % neexistuje" for "The file '%s' does not exist") throws a ValueError instead of a warning, which
     * stopped every page that shows the message. The original text is formatted instead, and the raw text as last resort.
     *
     * @param string $text the translation
     * @param array $args
     * @param string|null $original the untranslated text
     *
     * @return string
     */
    function gettext_vsprintf($text, array $args, $original = null)
    {
        try {
            return vsprintf($text, $args);
        } catch (\ValueError $e) {
            if ($original !== null && (string) $original !== $text) {
                try {
                    return vsprintf((string) $original, $args);
                } catch (\ValueError $e2) {
                    return (string) $original;
                }
            }

            return $text;
        }
    }
}

/**
 * Returns the translation of a string.
 *
 * @param string $original
 *
 * @return string
 */
function __($original)
{
    extract___($original);
    $text = BaseTranslator::$current->gettext((string) $original);

    if (func_num_args() === 1) {
        return $text;
    }

    $args = array_slice(func_get_args(), 1);

    return is_array($args[0]) ? strtr($text, $args[0]) : gettext_vsprintf($text, $args, $original);
}

/**
 * Noop, marks the string for translation but returns it unchanged.
 *
 * @param string $original
 *
 * @return string
 */
function noop__($original)
{
    return $original;
}

/**
 * Returns the singular/plural translation of a string.
 *
 * @param string $original
 * @param string $plural
 * @param string $value
 *
 * @return string
 */
function n__($original, $plural, $value)
{
    $text = BaseTranslator::$current->ngettext((string) $original, (string) $plural, (string) $value);

    if (func_num_args() === 3) {
        return $text;
    }

    $args = array_slice(func_get_args(), 3);

    return is_array($args[0]) ? strtr($text, $args[0]) : gettext_vsprintf($text, $args, $original);
}

/**
 * Returns the translation of a string in a specific context.
 *
 * @param string $context
 * @param string $original
 * @param string $flag
 *
 * @return string
 */
function p__($context, $original, $flag = null)
{
    extract_p__($context, $original, $flag);
    $text = BaseTranslator::$current->pgettext((string) $context, (string) $original);

    // In development, returns the context!
    if (isDev() &&
        __getConfig("show_context") === true) {
        $text = "[{$context}] {$text}";
    }

    if (func_num_args() === 2) {
        return $text;
    }

    $args = array_slice(func_get_args(), 2);

    $result = is_array($args[0]) ? strtr($text, $args[0]) : gettext_vsprintf($text, $args, $original);

    return $result;
}

/**
 * Returns the translation of a string in a specific domain.
 *
 * @param string $domain
 * @param string $original
 *
 * @return string
 */
function d__($domain, $original)
{
    $text = BaseTranslator::$current->dgettext((string) $domain, (string) $original);

    if (func_num_args() === 2) {
        return $text;
    }

    $args = array_slice(func_get_args(), 2);

    return is_array($args[0]) ? strtr($text, $args[0]) : gettext_vsprintf($text, $args, $original);
}

/**
 * Returns the translation of a string in a specific domain and context.
 *
 * @param string $domain
 * @param string $context
 * @param string $original
 *
 * @return string
 */
function dp__($domain, $context, $original)
{
    $text = BaseTranslator::$current->dpgettext((string) $domain, (string) $context, (string) $original);

    if (func_num_args() === 3) {
        return $text;
    }

    $args = array_slice(func_get_args(), 3);

    return is_array($args[0]) ? strtr($text, $args[0]) : gettext_vsprintf($text, $args, $original);
}

/**
 * Returns the singular/plural translation of a string in a specific domain.
 *
 * @param string $domain
 * @param string $original
 * @param string $plural
 * @param string $value
 *
 * @return string
 */
function dn__($domain, $original, $plural, $value)
{
    $text = BaseTranslator::$current->dngettext((string) $domain, (string) $original, (string) $plural, (string) $value);

    if (func_num_args() === 4) {
        return $text;
    }

    $args = array_slice(func_get_args(), 4);

    return is_array($args[0]) ? strtr($text, $args[0]) : gettext_vsprintf($text, $args, $original);
}

/**
 * Returns the singular/plural translation of a string in a specific context.
 *
 * @param string $context
 * @param string $original
 * @param string $plural
 * @param string $value
 *
 * @return string
 */
function np__($context, $original, $plural, $value)
{
    $text = BaseTranslator::$current->npgettext((string) $context, (string) $original, (string) $plural, (string) $value);

    if (func_num_args() === 4) {
        return $text;
    }

    $args = array_slice(func_get_args(), 4);

    return is_array($args[0]) ? strtr($text, $args[0]) : gettext_vsprintf($text, $args, $original);
}

/**
 * Returns the singular/plural translation of a string in a specific domain and context.
 *
 * @param string $domain
 * @param string $context
 * @param string $original
 * @param string $plural
 * @param string $value
 *
 * @return string
 */
function dnp__($domain, $context, $original, $plural, $value)
{
    $text = BaseTranslator::$current->dnpgettext((string) $domain, (string) $context, (string) $original, (string) $plural, (string) $value);

    if (func_num_args() === 5) {
        return $text;
    }

    $args = array_slice(func_get_args(), 5);

    return is_array($args[0]) ? strtr($text, $args[0]) : gettext_vsprintf($text, $args, $original);
}
