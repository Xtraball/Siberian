<?php

namespace Cli;
/**
 * Class Packager
 */
class Packager
{
    /**
     * @var string
     */
    protected $root;

    /**
     * @var string
     */
    protected $siberian;

    /**
     * @var \stdClass
     */
    protected $package;

    /**
     * @var string
     */
    protected $release;

    /**
     * @var string
     */
    protected $requiredVersion;

    /**
     * @var string
     */
    protected $nativeVersion;

    /**
     * @var string
     */
    protected $apiVersion;

    /**
     * @var string
     */
    protected $hashFrom;

    /**
     * @var string
    ;     */
    protected $hashTo;

    /**
     * @var string
     */
    protected $buildPath;

    /**
     * @var string
     */
    protected $updatePackagePath;

    /**
     * @var string
     */
    protected $extraChange;

    /**
     * @var string
     */
    protected $extraDelete;

    /**
     * @var array
     */
    protected $forceChange;

    /**
     * @var array
     */
    protected $forceDelete;

    /**
     * @var array
     */
    protected $changeIndex;

    /**
     * @var array
     */
    protected $deleteIndex;

    /**
     * @var array
     */
    private $argv;

    /**
     * @var string
     */
    private $zipExcludeArgs = '--exclude=*.DS_Store* --exclude=*.idea* --exclude=*.git* --exclude=*.localized*';

    /**
     * Packager constructor.
     * @param $argv
     * @throws \Exception
     */
    public function __construct ($argv)
    {
        $this->argv = $argv;
        $this->root = realpath($argv[1]);
        $this->templates = realpath($this->root . '/ci/templates');
        $this->siberian = realpath($this->root . '/siberian');
        $this->package = json_decode(file_get_contents($this->root . '/package.json'));

        // Config files & extras!
        $this->extraChange = file_get_contents($this->root .
            '/ci/override/extra-change.txt');
        $this->extraDelete = file_get_contents($this->root .
            '/ci/override/extra-delete.txt');
        $this->forceChange = explode("\n", file_get_contents($this->root .
            '/ci/override/force-change.txt'));
        $this->forceDelete = explode("\n", file_get_contents($this->root .
            '/ci/override/force-delete.txt'));

        // Init!
        $this->fetchHashes();
        $this->buildStructure();
        $this->fetchChange();
        $this->fetchDelete();
        $this->copyChanges($this->updatePackagePath);
        $this->preparePackage('sae', 'Single App Edition', $this->updatePackagePath);
        $this->cleanUp($this->updatePackagePath);
        $this->buildManifest($this->updatePackagePath);
        $this->archive('sae', $this->updatePackagePath);

        // Done!
    }

    /**
     * Easy print debug
     */
    public function debug ()
    {
        print_r($this);
    }

    /**
     * @return $this
     * @throws \Exception
     */
    private function fetchHashes ()
    {
        // From package.json!
        $this->release = $this->package->version;
        $this->requiredVersion = $this->package->lastversion;
        $this->nativeVersion = $this->package->nativeVersion;
        $this->apiVersion = $this->package->apiVersion;

        // From git repository!
        exec('git rev-parse --quiet --verify v' . $this->requiredVersion . '^0',
            $requiredVersionHash);
        exec('git rev-parse --quiet --verify v' . $this->release . '^0',
            $releaseHash);

        $this->hashFrom = $requiredVersionHash[0];
        $this->hashTo = $releaseHash[0];

        if (empty($this->hashFrom)) {
            throw new \Exception('Unable to find a hash to start with, aborting.');
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function buildStructure ()
    {
        $this->buildPath = $this->root . '/release-' . $this->release;
        $this->updatePackagePath = $this->buildPath . '/update-package';

        if (!is_dir($this->buildPath)) {
            mkdir($this->buildPath, 0777, true);
        }
        chdir($this->buildPath);
        // Clean-up!
        exec('rm -Rf *update*');
        chdir($this->root);
        mkdir($this->updatePackagePath, 0777, true);

        return $this;
    }

    /**
     * @return $this
     */
    public function fetchChange ()
    {
        $gitCommand = sprintf("git diff --name-status " .
            "--diff-filter=MACT --relative=siberian/ " .
            "%s %s %s |cut -f 2",
            $this->hashFrom,
            $this->hashTo,
            $this->extraChange);

        $modified = $this->gitWrapper($gitCommand);

        $gitCommand = sprintf("git diff --name-status " .
            "--diff-filter=R --relative=siberian/ " .
            "%s %s %s |cut -f 3",
            $this->hashFrom,
            $this->hashTo,
            $this->extraChange);

        $renamed = $this->gitWrapper($gitCommand);

        $this->changeIndex = array_filter(array_merge($modified, $renamed, $this->forceChange));

        return $this;
    }

    /**
     * @return $this
     */
    public function fetchDelete ()
    {
        $gitCommand = sprintf("git diff --name-status " .
            "--diff-filter=R --relative=siberian/ " .
            "%s %s %s |cut -f 2",
            $this->hashFrom,
            $this->hashTo,
            $this->extraDelete);

        $renamed = $this->gitWrapper($gitCommand);

        $gitCommand = sprintf("git diff --name-status " .
            "--diff-filter=D --relative=siberian/ " .
            "%s %s %s |cut -f 2",
            $this->hashFrom,
            $this->hashTo,
            $this->extraDelete);

        $deleted = $this->gitWrapper($gitCommand);

        $this->deleteIndex = array_filter(array_merge($renamed, $deleted, $this->forceDelete));

        return $this;
    }

    /**
     * @return $this
     */
    public function copyChanges ($path)
    {
        foreach ($this->changeIndex as $file) {
            $targetDirectory = sprintf("%s/%s", $path, dirname($file));
            $source = sprintf("%s/%s", $this->siberian, $file);
            $destination = sprintf("%s/%s", $path, $file);
            if (!is_dir($targetDirectory)) {
                mkdir($targetDirectory, 0777, true);
            }
            exec("cp -P '{$source}' '{$destination}'");
        }

        return $this;
    }

    /**
     * @param string $type
     * @param string $name
     * @param string $path
     * @return $this
     */
    public function preparePackage ($type, $name, $path)
    {
        // Do not 'delete' all files that changed (or that were forced to)
        $realDeleteIndex = array_diff($this->deleteIndex, $this->changeIndex);

        $releaseNote = 'https://updates02.siberiancms.com/release-notes/all/index.php?v=' . $this->release;
        $packageJson = [
            'name' => $name,
            'version'=> $this->release,
            'code' => '',
            'description' => '<a href="' . $releaseNote .
                '" target="_blank">Click here to read the release notes</a>',
            'restore_apps' => true,
            'release_note' => [
                'url' => $releaseNote,
                'show' => true,
                'is_major' => true,
            ],
            'dependencies' => [
                'system' => [
                    'type' => strtoupper($type),
                    'version' => $this->requiredVersion,
                ],
            ],
            'files_to_delete' => $realDeleteIndex,
        ];

        $packagePath = sprintf("%s/package.json", $path);
        file_put_contents($packagePath,
            json_encode($packageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $versionClass = '<?php

namespace Siberian;

/**
 * Class \Siberian\Version
 */
class Version
{
    const TYPE = \'' . strtoupper($type) . '\';
    const NAME = \'' . $name . '\';
    const VERSION = \'' . $this->release . '\';
    const PREVIOUS_VERSION = \'' . $this->requiredVersion . '\';
    const NATIVE_VERSION = \'' . $this->nativeVersion . '\';
    const API_VERSION = \'' . $this->apiVersion . '\';

    /**
     * @param string|array $type
     * @return bool
     */
    static function is($type)
    {
        if (is_array($type)) {
            foreach ($type as $t) {
                if (self::TYPE == strtoupper((string) $t)) {
                    return true;
                }
            }
            return false;
        }
        return self::TYPE == strtoupper((string) $type);
    }
}
';
        $versionClassPath = sprintf("%s/lib/Siberian/Version.php", $path);

        // Be sure we have this folder, because sometime there is no changes in it!
        if (!is_dir($path . '/lib/Siberian')) {
            mkdir($path . '/lib/Siberian', 0777, true);
        }

        file_put_contents($versionClassPath, $versionClass);

        return $this;
    }

    /**
     * @param $path
     * @return $this
     */
    public function cleanUp ($path)
    {
        $filesToRemove = [
            $path . '/var/apps/ionic/android.tgz',
            $path . '/var/apps/ionic/ios.tgz',
            $path . '/var/apps/ionic/ios-noads.tgz',
            $path . '/var/apps/browser.tgz',
            $path . '/app/sae/design/desktop/flat/images/header/logo.png',
        ];

        foreach ($filesToRemove as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        return $this;
    }

    /**
     * @param $path
     * @return $this
     */
    public function buildManifest ($path)
    {
        $manifestScriptPath = sprintf("%s/ci/scripts/manifest.php", $this->root);
        exec('php -f ' . $manifestScriptPath . ' sae ' .
            $this->siberian . '/ ' . $path . '/');

        return $this;
    }

    /**
     * @param $type
     * @param $path
     * @return $this
     */
    public function archive ($type, $path)
    {
        chdir($path);

        $zipCommand = sprintf("zip -r -y -9 %s ../siberian_%s.update.%s.zip ./",
            $this->zipExcludeArgs,
            strtolower($type),
            $this->release);
        exec($zipCommand);

        return $this;
    }

    /**
     * @param $command
     * @return mixed
     */
    public function gitWrapper($command)
    {
        $commandPrefix = 'git config diff.renameLimit 100000; ';
        $command = $commandPrefix . $command;

        exec($command, $output);

        return $output;
    }
}

// Run only if specified!
$willRun = false;
foreach ($argv as $arg) {
    if ($arg === '--run') {
        $willRun = true;
    }
}

if ($willRun) {
    // Run!
    try {
        $jenkins = new \Cli\Packager($argv);
        $jenkins->debug();
    } catch (\Exception $e) {
        echo 'Something went wrong, ' . $e->getMessage() . PHP_EOL;
    }
} else {
    echo 'You must use --run to pack the update.' . PHP_EOL;
}
