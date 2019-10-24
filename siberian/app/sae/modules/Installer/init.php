<?php
/**
 * @param $bootstrap
 */
$init = function ($bootstrap) {
    \Siberian\Security::allowExtension("zip");
};