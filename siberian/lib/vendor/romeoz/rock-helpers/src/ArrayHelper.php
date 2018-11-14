<?php
namespace rock\helpers;

use rock\components\Arrayable;

/**
 * Helper "ArrayHelper"
 *
 * @package rock\helpers
 */
class ArrayHelper
{
    /**
     * If need chunk of keys
     */
    const TRACE = 0x01;
    /**
     * Escape string quotes
     */
    const ESCAPE = 0x01;
    /**
     *  Associative array
     */
    const ASSOC = 0x02;
    /**
     * Move element to top
     */
    const MOVE_HEAD = 0x01;
    /**
     * Move element to tail
     */
    const MOVE_TAIL = 0x02;

    /**
     * Retrieves the value of an array element or object property with the given key or property name.
     * If the key does not exist in the array or object, the default value will be returned instead.
     *
     * The key may be specified in a dot format to retrieve the value of a sub-array or the property
     * of an embedded object. In particular, if the key is `x.y.z`, then the returned value would
     * be `$array['x']['y']['z']` or `$array->x->y->z` (if `$array` is an object). If `$array['x']`
     * or `$array->x` is neither an array nor an object, the default value will be returned.
     * Note that if the array already has an element `x.y.z`, then its value will be returned
     * instead of going through the sub-arrays.
     *
     * Below are some usage examples,
     *
     * ```php
     * // working with array
     * $username = ArrayHelper::getValue($_POST, 'username');
     * // working with object
     * $username = ArrayHelper::getValue($user, 'username');
     * // working with anonymous function
     * $fullName = ArrayHelper::getValue($user, function($user, $defaultValue) {
     *     return $user->firstName . ' ' . $user->lastName;
     * });
     * // using dot format to retrieve the property of embedded object
     * $street = ArrayHelper::get($users, 'address.street');
     * ```
     *
     * @param array|object $array array or object to extract value from
     * @param string|array|callable $key key name of the array element, or property name of the object,
     *                                       or an anonymous function returning the value. The anonymous function signature should be:
     *                                       `function($array, $defaultValue)`.
     * @param mixed $default the default value to be returned if the specified key does not exist
     * @return mixed the value of the element if found, default value otherwise
     */
    public static function getValue($array, $key, $default = null)
    {
        if (empty($array)) {
            return $default;
        }
        if (empty($key)) {
            return $array;
        }
        if ($key instanceof \Closure) {
            return $key($array, $default);
        }
        if (is_object($array) && is_array($key)) {
            $key = implode('.', $key);
        }
        if (is_array($array) && is_array($key)) {
            return static::keyAsArray($array, $key, $default);
        }
        if (is_array($array) && array_key_exists($key, $array)) {
            return $array[$key];
        }
        if (($pos = strrpos($key, '.')) !== false) {
            $array = static::getValue($array, substr($key, 0, $pos), $default);
            $key = substr($key, $pos + 1);
        }
        if (is_object($array)) {
            return $array->$key;
        } elseif (is_array($array)) {
            return array_key_exists($key, $array) ? $array[$key] : $default;
        } else {
            return $default;
        }
    }

    /**
     * Set value in array.
     *
     * @param array $array
     * @param array|string $keys chain keys of the array element
     * @param mixed $value value of array
     * @return array
     */
    public static function setValue(array &$array, $keys, $value = null)
    {
        if (empty($keys)) {
            return $array;
        }
        if (is_string($keys)) {
            $keys = explode('.', $keys);
        }
        $current = &$array;
        foreach ($keys as $key) {
            if (!is_array($current) || empty($current[$key])) {
                $current[$key] = [];
            }
            $current = &$current[$key];
        }
        $current = $value;

        return $array;
    }

    /**
     * Update value in array.
     *
     * @param array $array current array.
     * @param array $keys chain keys of the array element.
     * @param callable $callback callback is modify.
     * @param bool $throwException
     * @return array
     * @throws ArrayException
     */
    public static function updateValue(array $array, array $keys, callable $callback, $throwException = true /* , $args... */)
    {
        $args = array_slice(func_get_args(), 4);
        if (!$keys) {
            return $array;
        }
        $current = &$array;
        foreach ($keys as $key) {
            if (!is_array($current) || !array_key_exists($key, $current)) {
                if (!$throwException) {
                    return $array;
                }
                throw new ArrayException(
                    sprintf(
                        'Did not find path %s in structure %s',
                        json_encode($keys),
                        json_encode($array)
                    )
                );
            }
            $current = &$current[$key];
        }
        $current = call_user_func_array($callback, array_merge([$current], $args));

        return $array;
    }

    /**
     * Removes an item from an array and returns the value. If the key does not exist in the array, the default value
     * will be returned instead.
     *
     * Usage examples:
     *
     * ```php
     * $array = ['type' => 'A', 'options' => [1, 2]];
     * ArrayHelper::removeValue($array, 'type');
     * // result: ['options' => [1, 2]]
     * ```
     *
     * @param array $array the array to extract value from
     * @param array|string $keys chain keys of the array element
     * @return mixed|null the value of the element if found, default value otherwise
     */
    public static function removeValue(array &$array, $keys)
    {
        if (is_string($keys) && strrpos($keys, '.') !== false) {
            $keys = explode('.', $keys);
        }
        if (is_array($keys)) {
            return $array = static::filterRecursive(static::setValue($array, $keys));
        }
        if (is_array($array) && (isset($array[$keys]) || array_key_exists($keys, $array))) {
            //$value = $array[$keys];
            unset($array[$keys]);

            return $array;
        }

        return $array;
    }

    /**
     * Filter values of array.
     * Note: if callback is null, then removed all the null values in array.
     *
     * @param array $array
     * @param callable|string $function
     * @return array
     */
    public static function filterRecursive(array $array, $function = null)
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result[$key] = static::filterRecursive($value, $function);
                continue;
            }
            if (!isset($function)) {
                $function = 'boolval';
            }
            $params = [$value];
            if (!is_string($function)) {
                $params[] = $key;
            }
            if ((bool)call_user_func_array($function, $params) === true) {
                $result[$key] = $value; // KEEP
                continue;
            }
        }

        return $result;
    }

    /**
     * Convert object to multi-array (recursive).
     *
     * @param mixed $value current object
     * @param array $only list of items whose value needs to be returned.
     * @param array $exclude list of items whose value should NOT be returned.
     * @param bool $unserialize
     * @return array
     */
    public static function toArray($value, array $only = [], array $exclude = [], $unserialize = false)
    {
        if (is_array($value)) {
            return static::map(
                $value,
                function ($value) use ($only, $exclude, $unserialize) {
                    return static::toArray($value, $only, $exclude, $unserialize);
                },
                true
            );
        }
        if (is_object($value) && !$value instanceof \Closure) {
            if ($value instanceof Arrayable) {
                $attributes = $value->toArray($only, $exclude);
            } else {
                $attributes =
                    $value instanceof \stdClass && isset($value->scalar) ? $value->scalar : get_object_vars($value);
            }
            if (is_array($attributes)) {
                return static::toArray($attributes, $only, $exclude, $unserialize);
            }
            $value = $attributes;
        }

        return $unserialize === true ? Serialize::unserialize($value, false) : $value;
    }

    /**
     * Map recursive.
     *
     * @param array $array
     * @param callable $callback
     * @param bool $recursive
     * @param int $depth
     * @param int $count
     * @return array
     */
    public static function map(array $array, callable $callback, $recursive = false, $depth = null, &$count = 0)
    {
        foreach ($array as $key => $value) {
            if (isset($depth) && $count === $depth) {
                return $array;
            }
            ++$count;
            if (is_array($array[$key]) && $recursive === true) {
                $array[$key] = static::map($array[$key], $callback, $recursive);
            } else {
                $array[$key] = call_user_func($callback, $array[$key], $key);
            }
        }

        return $array;
    }

    /**
     * Convert multi-array to single-array.
     *
     * ```php
     * $array = [
     *  'aa'=>'text',
     *  'bb' => ['aa' => 'text2'],
     *  'cc' => [
     *      'aa' => ['gg' => 'text3']
     *  ]
     * ];
     * ArrayHelper::toSingle($array);
     * // result: ['aa'=>'text', 'bb.aa' => 'text2', 'cc.aa.gg' => 'text3'];
     * ```
     *
     * @param array $data
     * @param string $separator
     * @return array
     * @see http://stackoverflow.com/questions/9416661/php-multidimensional-array-to-simple-array
     */
    public static function toSingle(array $data, $separator = '.')
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($data));
        $result = [];
        foreach ($iterator as $leafValue) {
            $keys = [];
            foreach (range(0, $iterator->getDepth()) as $depth) {
                $keys[] = $iterator->getSubIterator($depth)->key();
            }
            $result[join($separator, $keys)] = $leafValue;
        }

        return $result;
    }

    /**
     * Convert single-array to multi-array.
     *
     * ```php
     * $array = ['aa'=>'text', 'bb.aa' => 'text2', 'cc.aa.gg' => 'text3'];
     * $result = ArrayHelper::toMulti($array);
     * the result is: [
     *                    'aa'=>'text',
     *                    'bb' => ['aa' => 'text2'],
     *                    'cc' => [
     *                          'aa' =>
     *                              ['gg' => 'text3']
     *                          ]
     *                   ]
     * ```
     *
     * @param array $data
     * @param string $separator
     * @param bool $recursive
     * @return array
     */
    public static function toMulti(array &$data, $separator = '.', $recursive = false)
    {
        foreach ($data as $key => $value) {
            if (is_array($value) && $recursive === true) {
                $value = static::toMulti($value, $separator, $recursive);
                //continue;
            }
            if (($keys = explode($separator, $key)) && count($keys) > 1) {
                $data = ArrayHelper::setValue($data, $keys, $value);
                unset($data[$key]);
                continue;
            }
            $data[$key] = $value;
        }

        return $data;
    }

    /**
     * Move element of array (top, bottom).
     *
     * @param array $array current array
     * @param string|int $key key to move
     * @param int $move constant
     *
     * - `MOVE_HEAD` move head
     * - `MOVE_TAIL` move tail
     *
     * @return array
     */
    public static function moveElement(array $array, $key, $move = self::MOVE_HEAD)
    {
        if (!isset($array[$key])) {
            return $array;
        }
        if ($move & self::MOVE_HEAD) {
            $buff = [$key => $array[$key]];
            unset($array[$key]);

            return $buff + $array;
        }
        $buff = $array[$key];
        unset($array[$key]);
        $array[$key] = $buff;

        return $array;
    }

    /**
     * Contains value.
     *
     * @param string $needle needle value
     * @param array $array current array
     * @return bool
     */
    public static function contains($needle, array $array)
    {
        return (bool)static::search($needle, $array);
    }

    /**
     * Search element by value.
     *
     * @param string $needle needle value
     * @param array $array current array
     * @param array $keys trace keys
     * @return array
     */
    public static function search($needle, array $array, array &$keys = null)
    {
        $needle = '/^' . preg_quote($needle, '/') . '$/i';

        return static::_searchInternal($needle, $array, $keys, 0);
    }

    /**
     * Search all elements by value.
     *
     * @param string $needle needle value
     * @param array $array current array
     * @param array $keys trace keys
     * @return array
     */
    public static function searchAll($needle, array $array, array &$keys = null)
    {
        $needle = '/^' . preg_quote($needle, '/') . '$/i';

        return static::_searchAllInternal($needle, $array, $keys, 0);
    }

    /**
     * Search element by key.
     *
     * @param string $needle needle key
     * @param array $array current array
     * @param array $keys trace keys
     * @return array
     */
    public static function searchByKey($needle, array $array, array &$keys = null)
    {
        $needle = '/^' . preg_quote($needle, '/') . '$/i';

        return static::_searchInternal($needle, $array, $keys, \RegexIterator::USE_KEY);
    }

    /**
     * Search all elements by key.
     *
     * @param string $needle needle key
     * @param array $array current array
     * @param array $keys trace keys
     * @return array
     */
    public static function searchAllByKey($needle, array $array, array &$keys = null)
    {
        $needle = '/^' . preg_quote($needle, '/') . '$/i';

        return static::_searchAllInternal($needle, $array, $keys, \RegexIterator::USE_KEY);
    }

    /**
     * Search element by value (use RegExp-pattern).
     *
     * @param string $pattern RegExp-pattern
     * @param array $array current array
     * @param array $keys trace keys
     * @return array
     */
    public static function pregSearch($pattern, array $array, array &$keys = null)
    {
        return static::_searchInternal($pattern, $array, $keys, 0);
    }

    /**
     * Search all elements by value (use RegExp-pattern).
     *
     * @param string $pattern RegExp-pattern
     * @param array $array current array
     * @param array $keys trace keys
     * @return array
     */
    public static function pregSearchAll($pattern, array $array, array &$keys = null)
    {
        return static::_searchAllInternal($pattern, $array, $keys, 0);
    }

    /**
     * Search element by key (use RegExp-pattern).
     *
     * @param string $pattern RegExp-pattern
     * @param array $array current array
     * @param array $keys trace keys
     * @return array
     */
    public static function pregSearchByKey($pattern, array $array, array &$keys = null)
    {
        return static::_searchInternal($pattern, $array, $keys, \RegexIterator::USE_KEY);
    }

    /**
     * Search all elements by key (use RegExp-pattern).
     *
     * @param string $pattern RegExp-pattern
     * @param array $array current array
     * @param array $keys trace keys
     * @return array
     */
    public static function pregSearchAllByKey($pattern, array $array, array &$keys = null)
    {
        return static::_searchAllInternal($pattern, $array, $keys, \RegexIterator::USE_KEY);
    }

    /**
     * Filter by Column.
     *
     * @param array $array current array
     * @param array|string $keys names keys
     * @param string|int|null $indexKey the column to use as the index/keys for the returned array
     * @param bool $multi multi-array
     * @return array
     */
    public static function filterColumn(array $array, $keys = null, $indexKey = null, $multi = false)
    {
        $result = [];
        foreach ($array as $key => $val) {
            if (isset($indexKey) && isset($val[$indexKey])) {
                $key = $val[$indexKey];
            }
            if ($multi) {
                $result[$key][] = static::_filterColumnInternal($keys, $val);
                continue;
            }
            $result[$key] = static::_filterColumnInternal($keys, $val);
        }

        return $result;
    }

    /**
     * Indexes an array according to a specified key.
     * The input array should be multidimensional or an array of objects.
     *
     * The key can be a key name of the sub-array, a property name of object, or an anonymous
     * function which returns the key value given an array element.
     *
     * If a key value is null, the corresponding array element will be discarded and not put in the result.
     *
     * For example:
     *
     * ```php
     * $array = [
     *     ['id' => '123', 'data' => 'abc'],
     *     ['id' => '345', 'data' => 'def'],
     * ];
     * $result = ArrayHelper::index($array, 'id');
     * // result:
     * // [
     * //     '123' => ['id' => '123', 'data' => 'abc'],
     * //     '345' => ['id' => '345', 'data' => 'def'],
     * // ]
     *
     * // using anonymous function
     * $result = ArrayHelper::index($array, function ($element) {
     *     return $element['id'];
     * });
     * ```
     *
     * @param array $array the array that needs to be indexed
     * @param string|callable $key the column name or anonymous function whose result will be used to index the array
     * @return array the indexed array
     */
    public static function index($array, $key)
    {
        $result = [];
        foreach ($array as $element) {
            $value = static::getValue($element, $key);
            $result[$value] = $element;
        }

        return $result;
    }

    /**
     * @param array $keys keys
     * @param array $array current array
     * @param mixed $default
     * @return mixed
     */
    protected static function keyAsArray(array $array, array $keys, $default = null)
    {
        if (!$keys) {
            return $array;
        }
        $current = $array;
        foreach ($keys as $key) {
            if (!is_array($current) || empty($current[$key])) {
                return $default;
            }
            $current = $current[$key];
        }

        return $current;
    }

    /**
     * Merge key with value
     *
     * @param array $array current array
     * @param string $separator separator
     * @param int $const constant
     *
     * - `ESCAPE` escape value quotes
     *
     * @return array
     */
    public static function concatKeyValue(array $array, $separator = '=', $const = 0)
    {
        $result = [];
        foreach ($array as $key => $value) {
            $result[] =
                $key . ((isset($value) && $value !== 'null')
                    ? $separator . ($const & static::ESCAPE
                        ? StringHelper::doubleQuotes($value)
                        : $value)
                    : null);
        }

        return $result;
    }

    /**
     * Builds a map (key-value pairs) from a multidimensional array or an array of objects.
     *
     * The `$from` and `$to` parameters specify the key names or property names to set up the map.
     * Optionally, one can further group the map according to a grouping field `$group`.
     *
     * For example,
     *
     * ```php
     * $array = [
     *     ['id' => '123', 'name' => 'aaa', 'class' => 'x'],
     *     ['id' => '124', 'name' => 'bbb', 'class' => 'x'],
     *     ['id' => '345', 'name' => 'ccc', 'class' => 'y'],
     * ];
     *
     * $result = ArrayHelper::group($array, 'id', 'name');
     * // the result is:
     * // [
     * //     '123' => 'aaa',
     * //     '124' => 'bbb',
     * //     '345' => 'ccc',
     * // ]
     *
     * $result = ArrayHelper::group($array, 'id', 'name', 'class');
     * // the result is:
     * // [
     * //     'x' => [
     * //         '123' => 'aaa',
     * //         '124' => 'bbb',
     * //     ],
     * //     'y' => [
     * //         '345' => 'ccc',
     * //     ],
     * // ]
     * ```
     *
     * @param array $array
     * @param string|callable $from
     * @param string|callable $to
     * @param string|callable $group
     * @return array
     */
    public static function group($array, $from, $to, $group = null)
    {
        $result = [];
        foreach ($array as $keyElement => $element) {
            $key = static::getValue($element, $from, $keyElement);
            $value = static::getValue($element, $to, $keyElement);
            if ($group !== null) {
                $result[static::getValue($element, $group)][$key] = $value;
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Conversion to type of array values.
     *
     * @param array $array current array
     * @param bool $recursive
     * @return array
     */
    public static function toType(array $array, $recursive = true)
    {
        return static::map(
            $array,
            function ($value) {
                return Helper::toType($value);
            },
            $recursive
        );
    }

    /**
     * Depth array.
     *
     * @param array $array current array
     * @param bool $onlyFirst check only first element
     * @throws ArrayException
     * @return int
     */
    public static function depth(array $array, $onlyFirst = false)
    {
        $max = 0;
        try {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($array));
            foreach ($iterator as $value) {
                $max = (($depth = $iterator->getDepth()) > $max)
                    ? $depth
                    : $max;
                if ($onlyFirst === true) {
                    break;
                }
            }
        } catch (\Exception $e) {
            throw new ArrayException($e->getMessage(), ['name' => 'RecursiveIteratorIterator'], $e);
        }
        $iterator = null;

        return $max;
    }

    /**
     * Encodes special characters in an array of strings into HTML entities.
     *
     * Both the array keys and values will be encoded.
     * If a value is an array, this method will also encode it recursively.
     *
     * @param array $data data to be encoded
     * @param boolean $valuesOnly whether to encode array values only. If false,
     *                            both the array keys and array values will be encoded.
     * @param string $charset the charset that the data is using. If not set,
     * @return array the encoded data
     * @see http://www.php.net/manual/en/function.htmlspecialchars.php
     */
    public static function htmlEncode(array $data, $valuesOnly = true, $charset = 'UTF-8')
    {
        $d = [];
        foreach ($data as $key => $value) {
            if (!$valuesOnly && is_string($key)) {
                $key = htmlspecialchars($key, ENT_QUOTES, $charset);
            }
            if (is_string($value)) {
                $d[$key] = htmlspecialchars($value, ENT_QUOTES, $charset);
            } elseif (is_array($value)) {
                $d[$key] = static::htmlEncode($value, $valuesOnly, $charset);
            } else {
                $d[$key] = $value;
            }
        }

        return $d;
    }

    /**
     * Decodes HTML entities into the corresponding characters in an array of strings.
     *
     * Both the array keys and values will be decoded.
     * If a value is an array, this method will also decode it recursively.
     *
     * @param array $data data to be decoded
     * @param boolean $valuesOnly whether to decode array values only. If false,
     *                            both the array keys and array values will be decoded.
     * @return array the decoded data
     * @see http://www.php.net/manual/en/function.htmlspecialchars-decode.php
     */
    public static function htmlDecode(array $data, $valuesOnly = true)
    {
        $d = [];
        foreach ($data as $key => $value) {
            if (!$valuesOnly && is_string($key)) {
                $key = htmlspecialchars_decode($key, ENT_QUOTES);
            }
            if (is_string($value)) {
                $d[$key] = htmlspecialchars_decode($value, ENT_QUOTES);
            } elseif (is_array($value)) {
                $d[$key] = static::htmlDecode($value);
            } else {
                $d[$key] = $value;
            }
        }

        return $d;
    }

    /**
     * Sorts an array of objects or arrays (with the same structure) by one or several keys.
     *
     * @param array $array the array to be sorted. The array will be modified after calling this method.
     * @param string|callable|array $key the key(s) to be sorted by. This refers to a key name of the sub-array
     *                                         elements, a property name of the objects, or an anonymous function returning the values for comparison
     *                                         purpose. The anonymous function signature should be: `function($item)`.
     *                                         To sort by multiple keys, provide an array of keys here.
     * @param integer|array $direction the sorting direction. It can be either `SORT_ASC` or `SORT_DESC`.
     *                                         When sorting by multiple keys with different sorting directions, use an array of sorting directions.
     * @param integer|array $sortFlag the PHP sort flag. Valid values include
     *                                         `SORT_REGULAR`, `SORT_NUMERIC`, `SORT_STRING`, `SORT_LOCALE_STRING`, `SORT_NATURAL` and `SORT_FLAG_CASE`.
     *                                         Please refer to [PHP manual](http://php.net/manual/en/function.sort.php)
     *                                         for more details. When sorting by multiple keys with different sort flags, use an array of sort flags.
     * @throws ArrayException if the $descending or $sortFlag parameters do not have
     *                                         correct number of elements as that of $key.
     */
    public static function multisort(&$array, $key, $direction = SORT_ASC, $sortFlag = SORT_REGULAR)
    {
        $keys = is_array($key) ? $key : [$key];
        if (empty($keys) || empty($array)) {
            return;
        }
        $n = count($keys);
        if (is_scalar($direction)) {
            $direction = array_fill(0, $n, $direction);
        } elseif (count($direction) !== $n) {
            throw new ArrayException('The length of $descending parameter must be the same as that of $keys.');
        }
        if (is_scalar($sortFlag)) {
            $sortFlag = array_fill(0, $n, $sortFlag);
        } elseif (count($sortFlag) !== $n) {
            throw new ArrayException('The length of $sortFlag parameter must be the same as that of $keys.');
        }
        $args = [];
        foreach ($keys as $i => $key) {
            $flag = $sortFlag[$i];
            $args[] = static::getColumn($array, $key);
            $args[] = $direction[$i];
            $args[] = $flag;
        }
        $args[] = &$array;
        call_user_func_array('array_multisort', $args);
    }

    /**
     * Returns the values of a specified column in an array.
     *
     * The input array should be multidimensional or an array of objects.
     *
     * For example:
     *
     * ```php
     * $array = [
     *     ['id' => '123', 'data' => 'abc'],
     *     ['id' => '345', 'data' => 'def'],
     * ];
     * $result = ArrayHelper::getColumn($array, 'id');
     * // result: ['123', '345']
     *
     * // using anonymous function
     * $result = ArrayHelper::getColumn($array, function ($element) {
     *     return $element['id'];
     * });
     * ```
     *
     * @param array $array
     * @param callable|string $name
     * @param boolean $keepKeys whether to maintain the array keys. If false, the resulting array
     *                                  will be re-indexed with integers.
     * @return array the list of column values
     */
    public static function getColumn($array, $name, $keepKeys = true)
    {
        $result = [];
        if ($keepKeys) {
            foreach ($array as $k => $element) {
                $result[$k] = static::getValue($element, $name);
            }
        } else {
            foreach ($array as $element) {
                $result[] = static::getValue($element, $name);
            }
        }

        return $result;
    }

    /**
     * Checks if the given array contains the specified key.
     *
     * This method enhances the `array_key_exists()` function by supporting case-insensitive
     * key comparison.
     *
     * @param string $key the key to check
     * @param array $array the array with keys to check
     * @param boolean $caseSensitive whether the key comparison should be case-sensitive
     * @return boolean whether the array contains the specified key
     */
    public static function keyExists($key, $array, $caseSensitive = true)
    {
        if ($caseSensitive) {
            return array_key_exists($key, $array);
        } else {
            foreach (array_keys($array) as $k) {
                if (strcasecmp($key, $k) === 0) {
                    return true;
                }
            }

            return false;
        }
    }

    /**
     * Returns a value indicating whether the given array is an associative array.
     *
     * An array is associative if all its keys are strings. If `$allStrings` is false,
     * then an array will be treated as associative if at least one of its keys is a string.
     *
     * Note that an empty array will NOT be considered associative.
     *
     * @param array $array the array being checked
     * @param boolean $allStrings whether the array keys must be all strings in order for
     *                            the array to be treated as associative.
     * @return boolean whether the array is associative
     */
    public static function isAssociative($array, $allStrings = true)
    {
        if (!is_array($array) || empty($array)) {
            return false;
        }
        if ($allStrings) {
            foreach ($array as $key => $value) {
                if (!is_string($key)) {
                    return false;
                }
            }

            return true;
        } else {
            foreach ($array as $key => $value) {
                if (is_string($key)) {
                    return true;
                }
            }

            return false;
        }
    }

    /**
     * Returns a value indicating whether the given array is an indexed array.
     *
     * An array is indexed if all its keys are integers. If `$consecutive` is true,
     * then the array keys must be a consecutive sequence starting from 0.
     *
     * Note that an empty array will be considered indexed.
     *
     * @param array $array the array being checked
     * @param boolean $consecutive whether the array keys must be a consecutive sequence
     *                             in order for the array to be treated as indexed.
     * @return boolean whether the array is associative
     */
    public static function isIndexed($array, $consecutive = false)
    {
        if (!is_array($array)) {
            return false;
        }
        if (empty($array)) {
            return true;
        }
        if ($consecutive) {
            return array_keys($array) === range(0, count($array) - 1);
        } else {
            foreach ($array as $key => $value) {
                if (!is_integer($key)) {
                    return false;
                }
            }

            return true;
        }
    }

    /**
     * Returns only the specified key/value pairs from the array.
     * @param array $array current array
     * @param array $only included keys
     * @param array $exclude excluded keys
     * @return array
     */
    public static function only(array $array = [], array $only = [], array $exclude = [])
    {
        if (empty($array)) {
            return [];
        }
        if (!empty($only)) {
            $array = static::intersectByKeys($array, $only);
        }
        if (!empty($exclude)) {
            $array = static::diffByKeys($array, $exclude);
        }

        return $array;
    }

    public static function intersectByKeys(array $array, array $keys)
    {
        return array_intersect_key($array, array_flip($keys));
    }

    public static function diffByKeys(array $array, array $keys)
    {
        return array_diff_key($array, array_flip($keys));
    }

    /**
     * Merges two or more arrays into one recursively.
     *
     * If each array has an element with the same string key value, the latter
     * will overwrite the former (different from array_merge_recursive).
     * Recursive merging will be conducted if both arrays have an element of array
     * type and are having the same key.
     * For integer-keyed elements, the elements from the latter array will
     * be appended to the former array.
     *
     * @param array $a array to be merged to
     * @param array $b array to be merged from. You can specify additional
     *                 arrays via third argument, fourth argument etc.
     * @return array the merged array (the original arrays are not changed.)
     */
    public static function merge($a, $b)
    {
        $args = func_get_args();
        $res = array_shift($args);
        while (!empty($args)) {
            $next = array_shift($args);
            foreach ($next as $k => $v) {
                if (is_integer($k)) {
                    if (is_subclass_of($v, '\rock\base\Configure')) {
                        $res = [];
                        $res[] = $v;
                        continue;
                    }
                    isset($res[$k]) ? $res[] = $v : $res[$k] = $v;
                } elseif (is_array($v) && isset($res[$k]) && is_array($res[$k])) {
                    $res[$k] = self::merge($res[$k], $v);
                } else {
                    $res[$k] = $v;
                }
            }
        }

        return $res;
    }

    private static function _searchInternal($needle, array $array, array &$keys = null, $const = 0)
    {
        try {
            $iterator =
                new \RegexIterator(
                    new \RecursiveIteratorIterator(
                        new \RecursiveArrayIterator($array)
                    ),
                    $needle,
                    \RegexIterator::MATCH,
                    $const
                );

            $result = [];
            /** @var \RecursiveIteratorIterator $iterator */
            foreach ($iterator as $value) {
                $result[$iterator->key()] = $value;
                $keys = [];
                foreach (range(0, $iterator->getDepth()) as $depth) {
                    $keys[] = $iterator->getSubIterator($depth)->key();
                }
                break;
            }

            return $result;
        } catch (\Exception $e) {
            throw new ArrayException($e->getMessage(), [], $e);
        }
    }

    private static function _searchAllInternal($needle, array $array, array &$keys = null, $const = 0)
    {
        try {
            $iterator =
                new \RegexIterator(
                    new \RecursiveIteratorIterator(
                        new \RecursiveArrayIterator($array)
                    ),
                    $needle,
                    \RegexIterator::MATCH,
                    $const
                );

            $result = [];
            /** @var \RecursiveIteratorIterator $iterator */
            foreach ($iterator as $value) {
                $result[] = [
                    'key' => $iterator->key(),
                    'value' => $value
                ];
                $_keys = [];
                foreach (range(0, $iterator->getDepth()) as $depth) {
                    $_keys[] = $iterator->getSubIterator($depth)->key();
                }
                $keys[][$iterator->key()] = $_keys;
            }
        } catch (\Exception $e) {
            throw new ArrayException($e->getMessage(), [], $e);
        }

        return $result;
    }

    private static function _filterColumnInternal($keys = null, array $value)
    {
        if (empty($keys)) {
            return $value;
        } elseif (is_array($keys)) {
            return array_filter(
                $value,
                function () use (&$value, $keys) {
                    if (in_array(key($value), $keys)) {
                        next($value);

                        return true;
                    }
                    next($value);

                    return false;
                }
            );
        } else {
            if (isset($value[$keys])) {
                return $value[$keys];
            }
        }

        return null;
    }
}