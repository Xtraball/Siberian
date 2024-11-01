<?php

namespace unit\releasenotes;

class ReleaseNotesTest extends \Codeception\Test\Unit
{
    use \Codeception\AssertThrows;

    /**
     * @var \UnitTester
     */
    protected $tester;

    protected $modelClass = \ReleaseNotes_Model_ReleaseNotes::class;

    protected function _before()
    {
        // Clear admins
        \Zend_Registry::get('db')->exec('SET foreign_key_checks = 0'); // To be able to truncate

        // Ensure table exists and is up-to-date
        $migration_db_table = new \Siberian_Migration_Db_Table('releasenotes', ['db' => \Zend_Registry::get('db')]);
        $migration_db_table->setSchemaPath('/home/anders/devs/xtraball/Siberian/siberian/app/local/modules/ReleaseNotes/resources/db/schema/releasenotes.php');
        $migration_db_table->tableExists();

        \Zend_Registry::get('db')->exec('TRUNCATE TABLE releasenotes');
        \Zend_Registry::get('db')->exec('SET foreign_key_checks = 1');
    }

    protected function _after()
    {
    }

    // tests
    public function testDbTableValue()
    {
        $self = new $this->modelClass();
        $this->assertEquals('ReleaseNotes_Model_ReleaseNotes', $self->getDbTable());
    }
}