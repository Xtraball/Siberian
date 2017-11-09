<?php
class Siberian_Application_Resource_Layout
    extends Zend_Application_Resource_Layout
{
    /**
     * Retrieve layout object
     *
     * @return Zend_Layout
     */
    public function getLayout()
    {
        if (null === $this->_layout) {
            $this->_layout = Siberian_Layout::startMvc($this->getOptions());
        }
        return $this->_layout;
    }
}
