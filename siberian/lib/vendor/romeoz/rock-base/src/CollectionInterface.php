<?php

namespace rock\base;


interface CollectionInterface extends \IteratorAggregate
{
    /**
     * Returns value by name.
     * @param string $name
     * @return mixed
     */
    public function get($name);

    /**
     * Returns list values.
     * @param array $only list of items whose value needs to be returned.
     * @param array $exclude list of items whose value should NOT be returned.
     * @return array
     */
    public function getAll(array $only = [], array $exclude = []);

    /**
     * Returns count values.
     * @return int
     */
    public function getCount();

    /**
     * Adding value by name.
     * @param string $name
     * @param mixed $value
     */
    public function add($name, $value);

    /**
     * Multi adding values.
     * @param array $data
     */
    public function addMulti(array $data);

    /**
     * Exists value by name.
     * @param string $name
     * @return bool
     */
    public function exists($name);

    /**
     * Removing value by name.
     * @param string $name
     */
    public function remove($name);

    /**
     * Multi removing values.
     * @param array $names
     */
    public function removeMulti(array $names);

    /**
     * Removing all values.
     */
    public function removeAll();
}