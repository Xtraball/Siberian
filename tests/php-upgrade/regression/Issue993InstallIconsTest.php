<?php
/**
 * Issue #993: Siberian\Feature::installIcons() looked an icon up with find(['library_id = ?' => ..., 'link = ?' => ...]).
 * find() with an array goes to Core_Model_Db_Table::findByArray(), which takes column names as keys and adds the
 * " = ?" itself, and quoteInto() replaces every "?": the query was
 *     library_id = 155 = 155 AND link = '/a.png' = '/a.png'
 * which never matches, so every module install or update inserted all its icons again.
 */
require_once dirname(__DIR__, 3) . '/siberian/app/sae/modules/Core/Model/Db/Table.php';

/** The real findByArray() on an in-memory database: what it asks for, and what it finds */
function icon_lookup_table(Zend_Db_Adapter_Pdo_Sqlite $db): Core_Model_Db_Table
{
    $table = new class extends Core_Model_Db_Table {
        public $where;

        public function __construct()
        {
        }

        public function fetchRow($where = null, $order = null, $offset = null)
        {
            $this->where = $where;

            return $this->_db->fetchRow('SELECT * FROM media_library_image WHERE ' . $where);
        }
    };
    $property = new ReflectionProperty(Zend_Db_Table_Abstract::class, '_db');
    $property->setAccessible(true);
    $property->setValue($table, $db);

    return $table;
}

function icon_database(): Zend_Db_Adapter_Pdo_Sqlite
{
    $db = new Zend_Db_Adapter_Pdo_Sqlite(['dbname' => ':memory:']);
    $db->query('CREATE TABLE media_library_image (image_id INTEGER PRIMARY KEY, library_id INTEGER, link TEXT)');
    $db->query("INSERT INTO media_library_image (image_id, library_id, link) VALUES (7, 155, '/app/x/icon.png')");

    return $db;
}

/** The array installIcons() passes to find(), read from its source */
function install_icons_lookup_keys(): array
{
    $source = file_get_contents(dirname(__DIR__, 3) . '/siberian/lib/Siberian/Feature.php');
    t_true(preg_match('/new \\\\Media_Model_Library_Image\(\)\)->find\(\[(.*?)\]\);/s', $source, $m) === 1, 'the lookup is there');
    preg_match_all("/'([^']+)' => \\\$/", $m[1], $keys);

    return $keys[1];
}

return [
    'installIcons() looks the icon up with column names as keys' => function () {
        t_equal(['library_id', 'link'], install_icons_lookup_keys(), 'the keys of find()');
    },

    'the lookup of installIcons() finds an icon that is already there' => function () {
        $table = icon_lookup_table(icon_database());
        $lookup = [];
        foreach (install_icons_lookup_keys() as $column) {
            $lookup[$column] = $column === 'library_id' ? 155 : '/app/x/icon.png';
        }

        $row = $table->findByArray($lookup);

        t_true(is_array($row), "found, with the where: {$table->where}");
        t_equal('7', (string) $row['image_id'], 'the existing row, not a new one');
    },

    'a key that already has the operator is not usable with findByArray (what the bug was)' => function () {
        $table = icon_lookup_table(icon_database());

        $row = $table->findByArray(['library_id = ?' => 155, 'link = ?' => '/app/x/icon.png']);

        t_true(!is_array($row), 'nothing found: ' . $table->where);
    },
];
