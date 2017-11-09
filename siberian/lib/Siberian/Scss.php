<?php

include_once __DIR__ . '/../Scss/Base/Range.php';
include_once __DIR__ . '/../Scss/Block.php';
include_once __DIR__ . '/../Scss/Colors.php';
include_once __DIR__ . '/../Scss/Compiler.php';
include_once __DIR__ . '/../Scss/Compiler/Environment.php';
include_once __DIR__ . '/../Scss/Exception/CompilerException.php';
include_once __DIR__ . '/../Scss/Exception/ParserException.php';
include_once __DIR__ . '/../Scss/Exception/ServerException.php';
include_once __DIR__ . '/../Scss/Formatter.php';
include_once __DIR__ . '/../Scss/Formatter/Compact.php';
include_once __DIR__ . '/../Scss/Formatter/Compressed.php';
include_once __DIR__ . '/../Scss/Formatter/Crunched.php';
include_once __DIR__ . '/../Scss/Formatter/Debug.php';
include_once __DIR__ . '/../Scss/Formatter/Expanded.php';
include_once __DIR__ . '/../Scss/Formatter/Nested.php';
include_once __DIR__ . '/../Scss/Formatter/OutputBlock.php';
include_once __DIR__ . '/../Scss/Node.php';
include_once __DIR__ . '/../Scss/Node/Number.php';
include_once __DIR__ . '/../Scss/Parser.php';
include_once __DIR__ . '/../Scss/Type.php';
include_once __DIR__ . '/../Scss/Util.php';
include_once __DIR__ . '/../Scss/Version.php';
include_once __DIR__ . '/../Scss/Server.php';

class Siberian_Scss {

    static function getCompiler() {
        return new Leafo\ScssPhp\Compiler();
    }

}

