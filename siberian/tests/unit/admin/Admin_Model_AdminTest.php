<?php

namespace unit\admin;

class Admin_Model_AdminTest extends \Codeception\Test\Unit
{
    use \Codeception\AssertThrows;

    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        // Clear admins
        \Zend_Registry::get('db')->exec('SET foreign_key_checks = 0'); // To be able to truncate
        \Zend_Registry::get('db')->exec('TRUNCATE TABLE admin');
        \Zend_Registry::get('db')->exec('SET foreign_key_checks = 1');
    }

    protected function _after()
    {
    }

    // tests
    public function testDbTableValue()
    {
        $self = new \Admin_Model_Admin();
        // 'Admin_Model_Db_Table_Admin' == $self->_db_table
        $this->assertEquals('Admin_Model_Db_Table_Admin', $self->getDbTable());
    }

    // tests
    public function testCreateAdmin()
    {
        $self = new \Admin_Model_Admin();

        $this->assertThrows(\Exception::class, function () use ($self) {
            $self->setPassword('password');
        });

        $self
            ->setEmail('test@email.com')
            ->setFirstname('Test')
            ->setLastname('User')
            ->setPassword('Qwerty123!#')
            ->setRole('admin')
            ->save();

        $this->assertEquals('1', $self->getId(), 'Admin was created and has ID 1');

        $allAdmins = $self->getTable()->findAll([]);

        // Check if we have one admin in Db
        $this->assertEquals(1, $allAdmins->count());
        $this->assertEquals('test@email.com', $allAdmins[0]->getEmail());

        // Password must match the encrypted version, not clear.
        $this->assertNotEquals('Qwerty123!#', $allAdmins[0]->getPassword());
        $this->assertEquals(encrypt_password('Qwerty123!#'), $allAdmins[0]->getPassword());
    }
}