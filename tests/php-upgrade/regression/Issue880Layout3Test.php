<?php
/**
 * #880: a lone "-" in front of a statement negates the assignment, which is a TypeError on PHP 8
 * (Layout3Options). Static check on the source, so it runs without the application.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';

/** Lines of statements that start with a unary minus, e.g. "-\n $title = ...". */
$minusStatements = function (string $code) {
    $tokens = array_values(array_filter(token_get_all($code), function ($t) {
        return !(is_array($t) && in_array($t[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true));
    }));
    $lines = [];
    foreach ($tokens as $i => $token) {
        $prev = $tokens[$i - 1] ?? null;
        $next = $tokens[$i + 1] ?? null;
        $statementStart = in_array($prev, [';', '{', '}'], true) || (is_array($prev) && $prev[0] === T_OPEN_TAG);
        if ($token === '-' && $statementStart && is_array($next) && $next[0] === T_VARIABLE) {
            $lines[] = $next[2];
        }
    }
    return $lines;
};

return [
    '880 detector finds a leading minus and ignores subtraction' => function () use ($minusStatements) {
        t_equal([3], $minusStatements("<?php\n\$a = 1;\n- \$b = 2;"), 'leading minus found');
        t_equal([], $minusStatements('<?php $a = 5; $c = $a - $b; $d = -$a;'), 'subtraction and negation in an expression are fine');
    },

    '880 Layout3Options has no stray minus' => function () use ($siberian, $minusStatements) {
        $code = file_get_contents($siberian . '/app/sae/modules/Layouts/Form/Layout3Options.php');
        t_equal([], $minusStatements($code), 'no stray minus');
        t_true(strpos($code, '$title = $this->addSimpleRadio("title"') !== false, 'the title radio is still added');
    },

    '880 no other form in the SAE modules has one' => function () use ($siberian, $minusStatements) {
        $found = [];
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($siberian . '/app/sae/modules', FilesystemIterator::SKIP_DOTS));
        foreach ($it as $file) {
            if ($file->getExtension() === 'php' && strpos($file->getPathname(), '/Form/') !== false) {
                $hits = $minusStatements(file_get_contents($file->getPathname()));
                if ($hits) {
                    $found[] = $file->getPathname() . ':' . implode(',', $hits);
                }
            }
        }
        t_equal([], $found, 'forms with a stray minus');
    },
];
