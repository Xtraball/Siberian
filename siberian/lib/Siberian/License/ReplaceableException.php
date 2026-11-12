<?php

namespace Siberian\License;

/**
 * The saved licence key of a module is expired or no longer usable, a new one can replace it.
 * The installation itself is not blocked by it.
 */
class ReplaceableException extends \Exception
{
}
