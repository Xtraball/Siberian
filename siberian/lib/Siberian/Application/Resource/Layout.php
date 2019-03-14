<?php

/**
 * Class Siberian_Application_Resource_Layout
 */
class Siberian_Application_Resource_Layout
    extends Zend_Application_Resource_Layout
{
    /**
     * @return \Siberian\Layout|Zend_Layout
     * @throws Zend_Layout_Exception
     */
    public function getLayout()
    {
        if (null === $this->_layout) {
            $this->_layout = Siberian\Layout::startMvc($this->getOptions());
        }
        return $this->_layout;
    }
}
