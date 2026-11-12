<?php

/**
 * Hands a Zend_Session_SaveHandler_Interface to PHP as the SessionHandlerInterface object it expects.
 *
 * session_set_save_handler() with six separate callbacks is deprecated since PHP 8.4. The handlers of this application
 * only know the six methods of the Zend interface, so this thin adapter forwards each call and returns whatever the
 * handler returned (PHP reads the results the same way for callbacks and for an object).
 *
 * The methods carry no return types on purpose: the application still runs on PHP versions where the interface has
 * none, and #[ReturnTypeWillChange] keeps PHP 8.1+ quiet about it.
 */
class Zend_Session_SaveHandler_Adapter implements SessionHandlerInterface
{
    /**
     * @var Zend_Session_SaveHandler_Interface
     */
    protected $_handler;

    /**
     * @param Zend_Session_SaveHandler_Interface $handler
     */
    public function __construct(Zend_Session_SaveHandler_Interface $handler)
    {
        $this->_handler = $handler;
    }

    /**
     * @return Zend_Session_SaveHandler_Interface
     */
    public function getHandler()
    {
        return $this->_handler;
    }

    #[\ReturnTypeWillChange]
    public function open($path, $name)
    {
        return $this->_handler->open($path, $name);
    }

    #[\ReturnTypeWillChange]
    public function close()
    {
        return $this->_handler->close();
    }

    #[\ReturnTypeWillChange]
    public function read($id)
    {
        return $this->_handler->read($id);
    }

    #[\ReturnTypeWillChange]
    public function write($id, $data)
    {
        return $this->_handler->write($id, $data);
    }

    #[\ReturnTypeWillChange]
    public function destroy($id)
    {
        return $this->_handler->destroy($id);
    }

    #[\ReturnTypeWillChange]
    public function gc($maxlifetime)
    {
        return $this->_handler->gc($maxlifetime);
    }
}
