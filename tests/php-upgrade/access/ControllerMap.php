<?php
/**
 * Reads every controller of the application with the PHP tokenizer (no class is loaded) and describes how it is
 * guarded: its parent chain, the guard methods it overrides, and the actions it declares.
 *
 * Used by the access matrix test. Parsing tokens instead of matching text means a comment or a string that mentions
 * `parent::init()` does not count as a call.
 */
final class ControllerMap
{
    /** @var array<string, array> class => description */
    private $classes = [];

    /**
     * @param string $modulesRoot the app/sae/modules folder
     */
    public function __construct(string $modulesRoot)
    {
        $files = new RegexIterator(
            new RecursiveIteratorIterator(new RecursiveDirectoryIterator($modulesRoot, FilesystemIterator::SKIP_DOTS)),
            '/\/controllers\/.*Controller\.php$|\/Controller\/.*\.php$/'
        );
        $paths = array_map(static function ($f) { return $f->getPathname(); }, iterator_to_array($files, false));
        sort($paths);
        foreach ($paths as $path) {
            $description = $this->describe($path, $modulesRoot);
            if ($description !== null) {
                $this->classes[$description['class']] = $description;
            }
        }
    }

    /** @return array<string, array> */
    public function classes(): array
    {
        return $this->classes;
    }

    /**
     * Parent classes from the class up to the first one that is not a controller of this tree.
     *
     * @return string[]
     */
    public function chain(string $class): array
    {
        $chain = [];
        while ($class !== '' && !in_array($class, $chain, true)) {
            $chain[] = $class;
            $class = $this->classes[$class]['parent'] ?? '';
        }

        return $chain;
    }

    /**
     * Does the class, or a parent up to (not including) $stopAt, override $method without calling parent::$method()?
     */
    public function breaksChain(string $class, string $method): bool
    {
        $info = $this->classes[$class] ?? null;
        if ($info === null || !isset($info['methods'][$method])) {
            return false;
        }

        return !$info['methods'][$method]['callsParent'];
    }

    /**
     * Does the body of $class::$method mention the identifier (a function or method name, or a string equal to it)?
     */
    public function bodyMentions(string $class, string $method, string $identifier): bool
    {
        foreach ($this->classes[$class]['methods'][$method]['body'] ?? [] as $t) {
            if (is_array($t) && $t[0] === T_STRING && strcasecmp($t[1], $identifier) === 0) {
                return true;
            }
            // forward('notauthorized') names its target as a string
            if (is_array($t) && $t[0] === T_CONSTANT_ENCAPSED_STRING && trim($t[1], '\'"') === $identifier) {
                return true;
            }
        }

        return false;
    }

    /**
     * The module/controller/action triples a class lists in `public $openActions = [...]` (actions that skip the
     * login of the Admin and Application families).
     *
     * @return string[] "module/controller/action"
     */
    public function openActions(string $class): array
    {
        $values = $this->classes[$class]['openActions'] ?? [];
        $out = [];
        foreach (array_chunk($values, 3) as $triple) {
            if (count($triple) === 3) {
                $out[] = implode('/', $triple);
            }
        }

        return $out;
    }

    private function openActionStrings(array $tokens, int $from): array
    {
        $values = [];
        $count = count($tokens);
        for ($i = $from; $i < $count; $i++) {
            if ($tokens[$i] === ';') {
                break;
            }
            if (is_array($tokens[$i]) && $tokens[$i][0] === T_CONSTANT_ENCAPSED_STRING) {
                $text = trim($tokens[$i][1], '\'"');
                // keys are 'module' / 'controller' / 'action': only the values are wanted
                if (!in_array($text, ['module', 'controller', 'action'], true)) {
                    $values[] = $text;
                }
            }
        }

        return $values;
    }

    private function describe(string $path, string $root): ?array
    {
        $tokens = token_get_all((string) file_get_contents($path));
        $count = count($tokens);
        $class = null;
        $parent = '';
        $abstract = false;
        $methods = [];
        $openActions = [];
        $namespace = '';
        $aliases = [];

        for ($i = 0; $i < $count; $i++) {
            $t = $tokens[$i];
            if (!is_array($t)) {
                continue;
            }
            if ($class === null && $t[0] === T_ABSTRACT) {
                $abstract = true;
            }
            if ($class === null && $t[0] === T_NAMESPACE) {
                $namespace = ltrim($this->nextName($tokens, $i + 1), '\\');
            }
            if ($class === null && $t[0] === T_USE) {
                // use Foo\Bar as Baz;  (only the aliased imports matter: they rename a parent class)
                $imported = ltrim($this->nextName($tokens, $i + 1), '\\');
                for ($j = $i + 1; $j < $count && $tokens[$j] !== ';'; $j++) {
                    if (is_array($tokens[$j]) && $tokens[$j][0] === T_AS) {
                        $aliases[$this->nextName($tokens, $j + 1)] = $imported;
                    }
                }
                if (!isset($aliases[substr(strrchr('\\' . $imported, '\\'), 1)])) {
                    $aliases[substr(strrchr('\\' . $imported, '\\'), 1)] = $imported;
                }
            }
            if ($t[0] === T_CLASS && $class === null && !$this->prevIsDoubleColon($tokens, $i)) {
                $class = ($namespace !== '' ? $namespace . '\\' : '') . $this->nextName($tokens, $i + 1);
                for ($j = $i; $j < $count && $tokens[$j] !== '{'; $j++) {
                    if (is_array($tokens[$j]) && $tokens[$j][0] === T_EXTENDS) {
                        $raw = $this->nextName($tokens, $j + 1);
                        if (strpos($raw, '\\') === 0) {
                            $parent = ltrim($raw, '\\');
                        } elseif (isset($aliases[$raw])) {
                            $parent = $aliases[$raw];
                        } else {
                            $parent = ($namespace !== '' ? $namespace . '\\' : '') . $raw;
                        }
                    }
                }
                continue;
            }
            if ($class !== null && $t[0] === T_VARIABLE && $t[1] === '$openActions') {
                $openActions = $this->openActionStrings($tokens, $i + 1);
            }
            if ($class !== null && $t[0] === T_FUNCTION) {
                $name = $this->nextName($tokens, $i + 1);
                $visibility = $this->visibilityBefore($tokens, $i);
                [$body, $end] = $this->bodyFrom($tokens, $i);
                $methods[$name] = [
                    'visibility' => $visibility,
                    'callsParent' => $this->callsParent($body, $name),
                    'body' => $body,
                ];
                $i = $end;
            }
        }

        if ($class === null) {
            return null;
        }

        return [
            'class' => $class,
            'parent' => $parent,
            'abstract' => $abstract,
            'file' => ltrim(substr($path, strlen($root)), '/'),
            'methods' => $methods,
            'openActions' => $openActions,
            'actions' => array_keys(array_filter($methods, static function ($m, $name) {
                return $m['visibility'] === 'public' && substr($name, -6) === 'Action';
            }, ARRAY_FILTER_USE_BOTH)),
        ];
    }

    private function prevIsDoubleColon(array $tokens, int $i): bool
    {
        for ($j = $i - 1; $j >= 0; $j--) {
            if (is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) {
                continue;
            }

            return is_array($tokens[$j]) && $tokens[$j][0] === T_DOUBLE_COLON;
        }

        return false;
    }

    private function nextName(array $tokens, int $i): string
    {
        $name = '';
        for ($n = count($tokens); $i < $n; $i++) {
            $t = $tokens[$i];
            if (is_array($t) && $t[0] === T_WHITESPACE) {
                if ($name !== '') {
                    break;
                }
                continue;
            }
            if (is_array($t) && in_array($t[0], [T_STRING, T_NS_SEPARATOR, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED], true)) {
                $name .= $t[1];
                continue;
            }
            if ($t === '&' && $name === '') {
                continue;
            }
            break;
        }

        return $name;
    }

    private function visibilityBefore(array $tokens, int $i): string
    {
        for ($j = $i - 1; $j >= 0; $j--) {
            $t = $tokens[$j];
            if (is_array($t) && in_array($t[0], [T_WHITESPACE, T_STATIC, T_FINAL, T_ABSTRACT], true)) {
                continue;
            }
            if (is_array($t) && $t[0] === T_PUBLIC) {
                return 'public';
            }
            if (is_array($t) && $t[0] === T_PROTECTED) {
                return 'protected';
            }
            if (is_array($t) && $t[0] === T_PRIVATE) {
                return 'private';
            }
            break;
        }

        return 'public';
    }

    /** @return array{0: array, 1: int} the tokens of the method body and the index of its closing brace */
    private function bodyFrom(array $tokens, int $i): array
    {
        $count = count($tokens);
        while ($i < $count && $tokens[$i] !== '{' && $tokens[$i] !== ';') {
            $i++;
        }
        if ($i >= $count || $tokens[$i] === ';') {
            return [[], $i];
        }
        $depth = 0;
        $body = [];
        for (; $i < $count; $i++) {
            $t = $tokens[$i];
            $text = is_array($t) ? $t[1] : $t;
            if ($text === '{' || (is_array($t) && in_array($t[0], [T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES], true))) {
                $depth++;
            } elseif ($text === '}') {
                $depth--;
                if ($depth === 0) {
                    return [$body, $i];
                }
            }
            $body[] = $t;
        }

        return [$body, $i];
    }

    private function callsParent(array $body, string $method): bool
    {
        $n = count($body);
        for ($i = 0; $i < $n; $i++) {
            if (is_array($body[$i]) && $body[$i][0] === T_STRING && strtolower($body[$i][1]) === 'parent') {
                $j = $i + 1;
                while ($j < $n && is_array($body[$j]) && $body[$j][0] === T_WHITESPACE) {
                    $j++;
                }
                if ($j < $n && is_array($body[$j]) && $body[$j][0] === T_DOUBLE_COLON) {
                    $j++;
                    while ($j < $n && is_array($body[$j]) && $body[$j][0] === T_WHITESPACE) {
                        $j++;
                    }
                    if ($j < $n && is_array($body[$j]) && strtolower($body[$j][1]) === strtolower($method)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
