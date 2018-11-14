<?php

namespace rock\base;


trait ClassName
{
    /**
     * @return string the fully qualified name of this class.
     */
    public static function className()
    {
        return get_called_class();
    }
} 