<?php
/**
 * Lets PHPUnit (and so Infection) run the tests of the custom runner. Each closure of a tests/php-upgrade/<suite>/<Name>Test.php
 * file becomes one PHPUnit case. Pick the file with MUTATION_TESTS, for example `regression/ColorTest`.
 *
 * Only for the files that need no database (regression suite and the db tests that run without one); the mutation
 * score is read per file, see docs/testing-plan.md and tests/php-upgrade/mutation/README.md.
 */
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RunnerBridgeTest extends TestCase
{
    /** @return array<string, array{0: string}> */
    public static function cases(): array
    {
        $spec = getenv('MUTATION_TESTS') ?: '';
        $file = dirname(__DIR__) . '/' . $spec . '.php';
        if ($spec === '' || !is_file($file)) {
            throw new RuntimeException('Set MUTATION_TESTS to a test file such as regression/ColorTest');
        }
        $cases = [];
        foreach (array_keys(self::load($file)) as $name) {
            $cases[$name] = [$name];
        }

        return $cases;
    }

    /** The file declares helper functions, so it is required once. */
    private static function load(string $file): array
    {
        static $loaded = [];
        return $loaded[$file] ??= require $file;
    }

    #[DataProvider('cases')]
    public function testCase(string $name): void
    {
        $file = dirname(__DIR__) . '/' . getenv('MUTATION_TESTS') . '.php';
        // A failed t_equal() throws; a passing closure returns.
        self::load($file)[$name]();
        $this->addToAssertionCount(1);
    }
}
