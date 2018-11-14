<?php

namespace rock\base;


use rock\helpers\ObjectHelper;
use rock\helpers\StringHelper;

class Alias
{
    /**
     * @var array registered path aliases
     * @see getAlias()
     * @see setAlias()
     */
    public static $aliases = [];

    /**
     * Translates a path alias into an actual path.
     *
     * The translation is done according to the following procedure:
     *
     * 1. If the given alias does not start with '@', it is returned back without change;
     * 2. Otherwise, look for the longest registered alias that matches the beginning part
     *    of the given alias. If it exists, replace the matching part of the given alias with
     *    the corresponding registered path.
     * 3. Throw an exception or return false, depending on the `$throwException` parameter.
     *
     * For example, by default '@rock' is registered as the alias to the Rock framework directory,
     * say '/path/to/rock'. The alias '@rock/web' would then be translated into '/path/to/rock/web'.
     *
     * If you have registered two aliases '@foo' and '@foo/bar'. Then translating '@foo/bar/config'
     * would replace the part '@foo/bar' (instead of '@foo') with the corresponding registered path.
     * This is because the longest alias takes precedence.
     *
     * However, if the alias to be translated is '@foo/barbar/config', then '@foo' will be replaced
     * instead of '@foo/bar', because '/' serves as the boundary character.
     *
     * Note, this method does not check if the returned path exists or not.
     *
     * @param string $alias the alias to be translated.
     * @param array $placeholders
     * @param boolean $throwException whether to throw an exception if the given alias is invalid.
     *                                If this is false and an invalid alias is given, false will be returned by this method.
     * @throws \Exception if the alias is invalid while $throwException is true.
     * @return string|boolean the path corresponding to the alias, false if the root alias is not previously registered.
     * @see setAlias()
     */
    public static function getAlias($alias, array $placeholders = [], $throwException = true)
    {
        if (strncmp($alias, '@', 1)) {
            // not an alias
            return $alias;
        }

        $delimiter = ObjectHelper::isNamespace($alias) ? '\\' : '/';

        $pos = strpos($alias, $delimiter);
        $root = $pos === false ? $alias : substr($alias, 0, $pos);

        if (isset(static::$aliases[$root])) {
            if (is_string(static::$aliases[$root])) {
                $result = $pos === false ? static::$aliases[$root] : static::$aliases[$root] . substr($alias, $pos);
                return StringHelper::replace($result, $placeholders, false);
            } else {
                foreach (static::$aliases[$root] as $name => $path) {
                    if (strpos($alias . $delimiter, $name . $delimiter) === 0) {
                        $result = $path . substr($alias, strlen($name));
                        return StringHelper::replace($result, $placeholders, false);
                    }
                }
            }
        }

        if ($throwException) {
            throw new \Exception("Invalid path alias: $alias");
        } else {
            return false;
        }
    }

    /**
     * Registers a path alias.
     *
     * A path alias is a short name representing a long path (a file path, a URL, etc.)
     * For example, we use '@rock' as the alias of the path to the Rock framework directory.
     *
     * A path alias must start with the character '@' so that it can be easily differentiated
     * from non-alias paths.
     *
     * Note that this method does not check if the given path exists or not. All it does is
     * to associate the alias with the path.
     *
     *
     * @param string $alias the alias name (e.g. "@rock"). It must start with a '@' character.
     * It may contain the forward slash '/' which serves as boundary character when performing
     * alias translation by {@see \rock\base\Alias::getAlias()}.
     * @param string $path the path corresponding to the alias. Trailing '/' and '\' characters
     * will be trimmed. This can be
     *
     * - a directory or a file path (e.g. `/tmp`, `/tmp/main.txt`)
     * - a URL (e.g. `http://www.site.com`)
     * - a path alias (e.g. `@rock/base`). In this case, the path alias will be converted into the
     *   actual path first by calling {@see \rock\base\Alias::getAlias()}.
     *
     * @param bool $trailingTrim any trailing '/' and '\' characters in the given path/url will be trimmed.
     * @throws \Exception if $path is an invalid alias.
     * @see getAlias()
     */
    public static function setAlias($alias, $path, $trailingTrim = true)
    {
        if (strncmp($alias, '@', 1)) {
            $alias = '@' . $alias;
        }
        $delimiter = ObjectHelper::isNamespace($alias) ? '\\' : '/';

        $pos = strpos($alias, $delimiter);
        $root = $pos === false ? $alias : substr($alias, 0, $pos);
        if ($path !== null) {
            if (!strncmp($path, '@', 1)) {
                $path = static::getAlias($path);
            } elseif ($trailingTrim) {
                $path = rtrim($path, '\\/');
            }
            if (!isset(static::$aliases[$root])) {
                if ($pos === false) {
                    static::$aliases[$root] = $path;
                } else {
                    static::$aliases[$root] = [$alias => $path];
                }
            } elseif (is_string(static::$aliases[$root])) {
                if ($pos === false) {
                    static::$aliases[$root] = $path;
                } else {
                    static::$aliases[$root] = [
                        $alias => $path,
                        $root => static::$aliases[$root],
                    ];
                }
            } else {
                static::$aliases[$root][$alias] = $path;
                krsort(static::$aliases[$root]);
            }
        } elseif (isset(static::$aliases[$root])) {
            if (is_array(static::$aliases[$root])) {
                unset(static::$aliases[$root][$alias]);
            } elseif ($pos === false) {
                unset(static::$aliases[$root]);
            }
        }
    }

    /**
     * Defines path aliases.
     * This method calls {@see \rock\base\Alias::setAlias()} to register the path aliases.
     * This method is provided so that you can define path aliases when configuring a module.
     *
     * For example,
     *
     * ```php
     * [
     *     '@models' => '@app/models', // an existing alias
     *     '@backend' => __DIR__ . '/../backend',  // a directory
     * ]
     * ```
     * @property array list of path aliases to be defined. The array keys are alias names
     * (must start with '@') and the array values are the corresponding paths or aliases.
     * See {@see \rock\base\Alias::setAlias()} for an example.
     * @param array $aliases list of path aliases to be defined. The array keys are alias names
     * (must start with '@') and the array values are the corresponding paths or aliases.
     * @param bool $trailingTrim any trailing '/' and '\' characters in the given path/url will be trimmed.
     */
    public static function setAliases(array $aliases, $trailingTrim = true)
    {
        foreach ($aliases as $name => $alias) {
            static::setAlias($name, $alias, $trailingTrim);
        }
    }

    /**
     * Exists alias.
     *
     * @param string $alias the alias name (e.g. "@rock"). It must start with a '@' character.
     * @return bool
     */
    public static function existsAlias($alias)
    {
        if (strncmp($alias, '@', 1)) {
            $alias = '@' . $alias;
        }
        return isset(static::$aliases[$alias]);
    }
}