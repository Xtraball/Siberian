<?php
/**
 * Core_Model_Default_Abstract::getTable() instantiates `$this->_db_table` with `new $this->_db_table(...)` without
 * checking the class exists first (production error group "Uncaught Error: Class ? not found" in
 * app/sae/modules/Core/Model/Default/Abstract.php:218). A module whose table class is missing, renamed or simply
 * not loaded (a bad update, a stale autoload cache, a module removed from disk without being uninstalled) gets an
 * opaque fatal that points at this shared base method instead of naming the real, missing class.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
require_once $siberian . '/lib/System/polyfills.php';
require_once $siberian . '/app/sae/modules/Core/Model/Default/Abstract.php';

// The constructor reads the logger from the registry regardless of what the model needs.
if (!Zend_Registry::isRegistered('logger')) {
    Zend_Registry::set('logger', new class {
        public function __call($name, $args)
        {
            return null;
        }
    });
}

class GetTableRealTable
{
    public $options;

    public function __construct($options = [])
    {
        $this->options = $options;
    }
}

class GetTableModelWithMissingClass extends Core_Model_Default_Abstract
{
    protected $_db_table = 'GetTableClassThatDoesNotExist';
}

class GetTableModelWithRealClass extends Core_Model_Default_Abstract
{
    protected $_db_table = 'GetTableRealTable';
}

return [
    'a missing table class throws, naming the model and the missing class, instead of an opaque fatal' => function () {
        $model = new GetTableModelWithMissingClass();
        try {
            $model->getTable();
            t_true(false, 'getTable() was expected to throw');
        } catch (\Error $e) {
            t_true(false, 'must not be the raw "Class not found" fatal: ' . $e->getMessage());
        } catch (\Exception $e) {
            t_true(strpos($e->getMessage(), 'GetTableModelWithMissingClass') !== false, 'names the model');
            t_true(strpos($e->getMessage(), 'GetTableClassThatDoesNotExist') !== false, 'names the missing class');
        }
    },
    'an existing table class is still instantiated with the model class passed through' => function () {
        $model = new GetTableModelWithRealClass();
        $table = $model->getTable();
        t_true($table instanceof GetTableRealTable, 'the real class is returned');
        t_equal('GetTableModelWithRealClass', $table->options['modelClass'], 'unchanged behaviour');
    },
];
