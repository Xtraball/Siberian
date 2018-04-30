<?php

require __DIR__ . '/Render.php';
require __DIR__ . '/Colors.php';

/**
 * Class Module
 */
class Module
{
    /**
     * @var string
     */
    public $currentDir;

    /**
     * @var string
     */
    public $projectRoot;

    /**
     * @var string
     */
    public $templatePaths = [
        'model' => '/modules_templates/Model/Model.php',
        'table' => '/modules_templates/Model/Db/Table/Table.php',
        'form' => '/modules_templates/Form/Form.php',
        'form_delete' => '/modules_templates/Form/FormDelete.php',
        'crud_controller' => '/modules_templates/controllers/CrudController.php',
        'layout_xml' => '/modules_templates/resources/design/desktop/flat/layout/layout.xml',
        'edit_css' => '/modules_templates/resources/design/desktop/flat/template/module/application/edit.css',
        'edit_js' => '/modules_templates/resources/design/desktop/flat/template/module/application/edit.js',
        'edit_html' => '/modules_templates/resources/design/desktop/flat/template/module/application/edit.phtml',
    ];

    /**
     * @var array
     */
    public $defaultPackage = [
        'name' => '',
        'description' => '',
        'author' => '',
        'version' => '',
        'type' => 'module',
        'dependencies' => [
            'system' => [
                'type' => 'SAE',
                'version' => '4.13.16',
            ],
        ],
    ];

    /**
     * Default tree for a new module
     *
     * @var array
     */
    public $defaultTree = [
        'data' => '/resources/db/data',
        'schema' => '/resources/db/schema',
        'design_images' => '/resources/design/desktop/flat/images',
        'design_layout' => '/resources/design/desktop/flat/layout',
        'design_template' => '/resources/design/desktop/flat/template',
        'translations_default' => '/resources/translations/default',
        'controllers' => '/controllers',
        'form' => '/Form',
        'model' => '/Model',
        'table' => '/Model/Db/Table',
    ];

    /**
     * @var string
     */
    public $schemaPath = '/resources/db/schema';

    /**
     * @var array
     */
    public $availableModules = [];

    /**
     * @var
     */
    public $module;

    /**
     * @var array
     */
    public $placeholders = [];

    /**
     * @var array
     */
    public $models = [];

    /**
     * @var bool
     */
    public $force = false;

    /**
     * Module constructor.
     *
     * @param $argv
     */
    public function __construct($argv)
    {
        $this->currentDir = __DIR__;
        $this->projectRoot = realpath($this->currentDir . '/../../');

        // Interactive prompt mode!
        try {
            $this->listModules();
            $this->startInteractiveMode();
        } catch(Exception $e) {
            echo color($e->getMessage(), 'red') . PHP_EOL;
        }
    }

    /**
     * Display waiting/animated dots
     *
     * @param $seconds
     */
    public function waiter ($seconds)
    {
        while ($seconds != 0) {
            echo color('.', 'blue');
            $seconds--;
            sleep(1);
        }
        echo PHP_EOL;
    }

    /**
     * @param $string
     * @return mixed
     */
    public function camelize ($string)
    {
        return str_replace('_', '', ucwords($string, '_'));
    }

    /**
     * @param $string
     * @return mixed
     */
    public function humanize ($string)
    {
        return str_replace('_', ' ', ucwords($string, '_'));
    }

    /**
     * @throws Exception
     */
    public function startInteractiveMode ()
    {
        echo PHP_EOL . color('== Siberian Module Builder ==', 'blue') . PHP_EOL;
        $actions = [
            1 => 'Initialize an empty Module.',
            2 => 'Generate CRUD & Views from a schema.',
        ];


        $maxRange = null;
        foreach ($actions as $index => $action) {
            echo color($index . ') ' . $action, 'light_gray') . PHP_EOL;
            $maxRange = $index;
        }
        $actionRange = '1-' . $maxRange;

        $action = readline(color('Select an action (' . $actionRange . '): ', 'blue'));
        switch (intval($action)) {
            case 1:
                $this->initModule();
                break;
            case 2:
                $this->buildModule();
                break;
            default:
                throw new Exception('No choice, aborting.');
        }
    }

    /**
     * List all available modules
     */
    public function listModules ()
    {
        $this->availableModules = [];

        $modules = new DirectoryIterator($this->projectRoot . '/modules/');
        $index = 1;
        foreach ($modules as $module) {
            $packageJson = $module->getPathname() . '/package.json';
            if (is_file($packageJson)) {
                $packageContent = json_decode(file_get_contents($packageJson), true);
                $packageContent['root'] = $module->getFilename();
                $this->availableModules[$index++] = $packageContent;
            }
        }
    }

    /**
     * Initialize a new module
     *
     * @throws Exception
     */
    public function initModule ()
    {
        $moduleName = readline(color('Module name: ', 'blue'));
        if (empty($moduleName)) {
            throw new Exception('Module name is required.');
        }

        $moduleDir = $this->currentDir . '/../../modules/' . $moduleName;
        if (is_dir($moduleDir)) {
            $force = readline(color('A module with this name ' . $moduleName .
                ' already exists, would you like to overwrite it? (Y/n): ', 'red'));
            if ($force !== 'Y') {
                throw new Exception('Aborting.');
            }
        }

        $version = readline(color('Version (default: 1.0.0): ', 'blue'));
        if (empty($version)) {
            $version = '1.0.0';
        }
        $author = readline(color('Author: ', 'blue'));
        $description = readline(color('Description: ', 'blue'));

        $package = $this->defaultPackage;
        $package['name'] = $moduleName;
        $package['description'] = $author;
        $package['author'] = $description;
        $package['version'] = $version;

        @mkdir($moduleDir, 0777, true);
        // Build default tree
        foreach ($this->defaultTree as $folder) {
            @mkdir($moduleDir . $folder, 0777, true);
        }
        file_put_contents($moduleDir . '/package.json', json_encode($package, JSON_PRETTY_PRINT));

        echo color('Now you can link your new module into Siberian to start with, use `./sb lm` ' .
            $moduleName . '`', 'blue') . PHP_EOL;
    }

    /**
     * Build a complete Module from schema
     *
     * @throws Exception
     */
    public function buildModule ()
    {
        echo PHP_EOL . color('== CRUD Builder ==', 'blue') . PHP_EOL;
        $actions = [
            1 => 'Complete CRUD and views (include all the options below)',
            2 => 'Models',
            3 => 'Forms',
            4 => 'Controllers & views',
            5 => 'Mobile controllers & views',
        ];


        $maxRange = null;
        foreach ($actions as $index => $action) {
            echo color($index . ') ' . $action, 'light_gray') . PHP_EOL;
            $maxRange = $index;
        }
        $actionRange = '1-' . $maxRange;

        $action = readline(color('Select an action (' . $actionRange . '): ', 'blue'));
        switch (intval($action)) {
            case 1:
                $this->buildAll();
                /**$this->buildModel();
                 * $this->buildForm();
                 * $this->buildControllerView();*/
                break;
            case 2:
                $this->buildModel();
                break;
            case 3:
                $this->buildForm();
                break;
            case 4:
                $this->buildControllerView();
                break;
            case 5:
                // xxx
                break;
            default:
                throw new Exception('No choice, aborting.');
        }
    }

    /**
     * Read/Fetch missing required arguments!
     *
     * @param array $arguments
     * @param array $required
     * @throws Exception
     */
    public function readArguments ($arguments, $required)
    {
        foreach ($arguments as $argument) {
            $argKey = $argument['key'];
            if (property_exists($this, $argKey) && empty($this->{$argKey})) {
                $value = readline(color($argument['label'] . ' :', 'blue'));
                if (empty($value) && array_key_exists($argKey, $required)) {
                    throw new Exception('Empty value, aborting.');
                }
                $this->{$argKey} = trim($value);
            }
        }
    }

    /**
     * Wraps all builders
     *
     * @throws Exception
     */
    public function buildAll ()
    {
        echo PHP_EOL . color('== Available Modules ==', 'blue') . PHP_EOL;
        foreach ($this->availableModules as $index => $module) {
            echo color(str_pad($index . ')', 5) . str_pad($module['name'], 30) .
                ' (/modules/' . $module['root'] . ')', 'light_gray') . PHP_EOL;
        }
        $range = '1-' . $index;
        $moduleIndex = readline(color('Select a module within the list (' . $range . '): ', 'blue'));

        if (!isset($this->availableModules[$moduleIndex])) {
            throw new Exception('No module selected, aborting.');
        }

        $this->module = $this->availableModules[$moduleIndex];

        // WARNING with confirmation
        $force = readline(color('Would you like to overwrite ALL CRUD files?' . PHP_EOL .
            'If YES, all existing CRUD files will be overwritten, ' . PHP_EOL .
            'otherwise only missing files will be created (Y/n): ', 'red'));
        if ($force === 'Y') {
            $this->force = true;
        }

        $this->fetchSchemas();

        // Builds listed models
        foreach ($this->models as $model) {
            $this->buildModel($model);
            $this->buildForm($model);
        }
    }

    /**
     * Fetch all availables schemas
     *
     * @throws Exception
     */
    public function fetchSchemas()
    {
        $schemaPaths = sprintf('%s/modules/%s%s',
            $this->projectRoot,
            $this->module['root'],
            $this->schemaPath);

        $files = new DirectoryIterator($schemaPaths);

        foreach ($files as $file) {
            if ($file->isFile() && !$file->isDot()) {
                $modelShort = str_replace('.php', '', $file->getFilename());
                $this->models[] = [
                    'name' => $modelShort,
                    'path' => $file->getPathname(),
                ];
            }
        }

        if (empty($this->models)) {
            throw new Exception('Unable to find any db/schema file, are you sure you have created them?');
        }

        echo color('Found the following schemas: ', 'blue') . PHP_EOL;
        $index = 1;
        foreach ($this->models as $model) {
            echo color(str_pad($index++ . ') ', 5) . str_pad($model['name'], 30), 'light_gray') . PHP_EOL;
        }

        // Building various placeholders!
        $this->generatePlaceholders();
    }

    /**
     *
     */
    public function generatePlaceholders()
    {
        foreach ($this->models as &$model) {
            $modelName = $model['name'];
            $modelPath = $model['path'];

            if (!is_file($modelPath)) {
                throw new Exception(sprintf('Unable to read corresponding schema file for \'%s\' model.', $modelName));
            }

            /**
             * @var array $schemas
             */
            require $modelPath;

            // Building the model placeholders
            $modelShort = trim(str_replace(strtolower($this->module['name'] . '_'), '', $modelName));
            if (!array_key_exists($modelName, $schemas)) {
                throw new Exception(sprintf('Unable to read corresponding model from the file \'%s\'.', $modelName));
            }

            $columns = $schemas[$modelName];
            $primaryKey = false;
            foreach ($columns as $name => $options) {
                if (array_key_exists('primary', $options)) {
                    $primaryKey = $name;
                    break;
                }
            }

            if (!$primaryKey) {
                throw new Exception(sprintf('Unable to find a primary_key for \'%s\' model.', $modelName));
            }

            # Opts
            $modelCamelized = $this->camelize($modelShort);
            $moduleAction = strtolower($this->module['name']);
            $modelController = strtolower(str_replace('_', '', $modelShort));

            $model['placeholders'] = [
                '#MODULE#' => $this->module['name'],
                '#HUMAN#' => $this->humanize($modelShort),
                '#MODEL#' => $modelCamelized,
                '#MODEL_SHORT#' => $modelShort,
                '#TABLE_NAME#' => $modelName,
                '#PRIMARY_KEY#' => $primaryKey,
                '#PRIMARY_KEY_CAMEL#' => $this->camelize($primaryKey),
                '#FORM_SAVE_ACTION#' => sprintf('/%s/%s/editpost', $moduleAction, $modelController),
                '#FORM_DELETE_ACTION#' => sprintf('/%s/%s/deletepost', $moduleAction, $modelController),
                '#MODULE_LOWER#' => $modelName,
                '#FORM_ID#' => str_replace('_', '-', strtolower($modelName)),
            ];

            $model['columns'] = $columns;
        }
    }

    /**
     * @param $model
     * @throws Exception
     */
    public function buildModel($model)
    {
        $placeholders = $model['placeholders'];

        # Model file
        $modelFile = sprintf('%s%s', $this->currentDir, $this->templatePaths['model']);
        $modelContent = str_replace(
            array_keys($placeholders),
            array_values($placeholders),
            file_get_contents($modelFile));

        $targetModelFile = sprintf('%s/modules/%s/Model/%s.php',
            $this->projectRoot,
            $this->module['root'],
            $placeholders['#MODEL#']);

        if (!file_exists($targetModelFile) || $this->force) {
            @mkdir(dirname($targetModelFile), 0777, true);
            file_put_contents($targetModelFile, $modelContent);
        } else {
            echo color(sprintf('Will not create \'%s\' model, file exists. Use force option if you want to replace it.',
                $targetModelFile), 'brown') . PHP_EOL;
        }

        # Db_Table file
        $tableFile = sprintf('%s%s', $this->currentDir, $this->templatePaths['table']);
        $tableContent = str_replace(
            array_keys($placeholders),
            array_values($placeholders),
            file_get_contents($tableFile));

        $targetTableFile = sprintf('%s/modules/%s/Model/Db/Table/%s.php',
            $this->projectRoot,
            $this->module['root'],
            $placeholders['#MODEL#']);

        if (!file_exists($targetTableFile) || $this->force) {
            @mkdir(dirname($targetTableFile), 0777, true);
            file_put_contents($targetTableFile, $tableContent);
        } else {
            echo color(sprintf('Will not create \'%s\' table, file exists. Use force option if you want to replace it.',
                $targetTableFile), 'brown') . PHP_EOL;
        }
    }

    /**
     * @param $model
     * @throws Exception
     */
    public function buildForm($model)
    {
        $placeholders = $model['placeholders'];
        $columns = $model['columns'];

        $buildElements = [];
        foreach ($columns as $name => $options) {
            if (isset($options['primary'])) {
                $primaryKey = $name;
            } else {
                if (in_array($name, ['created_at', 'updated_at'])) {
                    continue;
                }
                if (isset($options['type'])) {
                    $type = strtolower($options['type']);
                    $required = isset($options['is_null']) ? !$options['is_null'] : true;

                    if (strpos($type, 'tinyint(1)') !== false || strpos($type, 'boolean') !== false) {
                        $buildElements[] = Render::formBoolean($name, $required);
                    } else if (strpos($type, 'int') !== false ||
                        strpos($type, 'float') !== false ||
                        strpos($type, 'double') !== false ||
                        strpos($type, 'decimal') !== false ||
                        strpos($type, 'real') !== false ||
                        strpos($type, 'numeric') !== false) {

                        $buildElements[] = Render::formNumeric($name, $required);
                    } else if (strpos($type, 'char') !== false) {
                        $buildElements[] = Render::formText($name, $required);
                    } else if (strpos($type, 'text') !== false) {
                        $buildElements[] = Render::formTextarea($name, $required);
                    } else if (strpos($type, 'enum') !== false) {
                        $buildElements[] = Render::formSelect($name, $type, $required);
                    } else if (strpos($type, 'date') !== false) {
                        $buildElements[] = Render::formDate($name, $required);
                    } else if (strpos($type, 'datetime') !== false ||
                                strpos($type, 'timestamp') !== false) {
                        $buildElements[] = Render::formDatetime($name, $required);
                    } else if (strpos($type, 'time') !== false) {
                        $buildElements[] = Render::formTime($name, $required);
                    }

                }
            }
        }

        if (!$primaryKey) {
            throw new Exception(sprintf('Unable to find a primary_key for \'%s\' model.', $model['name']));
        }

        # Elements
        $buildElements = implode("\n", $buildElements);

        # Form file
        $formFile = sprintf('%s%s', $this->currentDir, $this->templatePaths['form']);
        $formContent = str_replace(
            array_keys($placeholders),
            array_values($placeholders),
            file_get_contents($formFile));

        $formContent = str_replace(
            '#ELEMENTS',
            $buildElements,
            $formContent);

        $targetFormFile = sprintf('%s/modules/%s/Form/%s.php',
            $this->projectRoot,
            $this->module['root'],
            $placeholders['#MODEL#']);

        if (!file_exists($targetFormFile) || $this->force) {
            @mkdir(dirname($targetFormFile), 0777, true);
            file_put_contents($targetFormFile, $formContent);
        } else {
            echo color(sprintf('Will not create \'%s\' form, file exists. Use force option if you want to replace it.',
                    $targetFormFile), 'brown') . PHP_EOL;
        }

        # Form delete file
        $formDeleteFile = sprintf('%s%s', $this->currentDir, $this->templatePaths['form_delete']);
        $formDeleteContent = str_replace(
            array_keys($placeholders),
            array_values($placeholders),
            file_get_contents($formDeleteFile));

        $targetFormDeleteFile = sprintf('%s/modules/%s/Form/%s/Delete.php',
            $this->projectRoot,
            $this->module['root'],
            $placeholders['#MODEL#']);

        if (!file_exists($targetFormDeleteFile) || $this->force) {
            @mkdir(dirname($targetFormDeleteFile), 0777, true);
            file_put_contents($targetFormDeleteFile, $formDeleteContent);
        } else {
            echo color(sprintf('Will not create \'%s\' form delete, file exists. Use force option if you want to replace it.',
                    $targetFormDeleteFile), 'brown') . PHP_EOL;
        }
    }

    /**
     * @throws Exception
     */
    public function buildControllerView() {
        # Try for multiple models
        $models = explode(',', $this->model);

        foreach ($models as $model) {
            $model_shorten = trim(str_replace(strtolower($this->module . '_'), '', $model));
            if (empty($model_shorten)) {
                $model_shorten = $model;
            }

            $schema_file = sprintf('%s%s/%s.php', $this->path, $this->path_to_schema, $model);
            if (!is_readable($schema_file)) {
                throw new Exception(sprintf('Unable to read corresponding schema file for \'%s\' model.', $model));
            } else {
                require $schema_file;

                # Fetch values
                $columns = $schemas[$model];
                $primary_key = false;
                foreach ($columns as $name => $options) {
                    if (isset($options['primary'])) {
                        $primary_key = $name;
                        break;
                    }
                }

                if (!$primary_key) {
                    throw new Exception(sprintf('Unable to find a primary_key for \'%s\' model.', $model));
                }

                # Opts
                # appointment
                $module_action = strtolower($this->module);
                # appointmentprovider
                $model_controller = strtolower(str_replace('_', '', $model_shorten));
                # Appointmentprovider
                $model_controller_class = ucfirst($model_controller);
                # AppointmentProvider
                $camelized_model = str_replace('_', '', ucwords($model_shorten, '_'));
                # Appointment Provider
                $human_model = str_replace('_', ' ', ucwords($model_shorten, '_'));
                # appointment-provider
                $model_id = str_replace('_', '-', $model_shorten);
                # ProviderId
                $primary_key_camelized = str_replace('_', '', ucwords($primary_key, '_'));


                # Form file
                $form_file = sprintf('%s%s', $this->currentDir, $this->templatePaths['crud_controller']);
                $controller_content = file_get_contents($form_file);
                $controller_content = str_replace('#MODULE#', $this->module, $controller_content);
                $controller_content = str_replace('#MODEL_CONTROLLER_CLASS#', $model_controller_class, $controller_content);
                $controller_content = str_replace('#MODEL_CAMEL#', $camelized_model, $controller_content);
                $controller_content = str_replace('#MODEL#', $model, $controller_content);
                $controller_content = str_replace('#FORM_ID#', $model_id, $controller_content);
                $controller_content = str_replace('#PRIMARY_KEY#', $primary_key, $controller_content);
                $controller_content = str_replace('#PRIMARY_KEY_CAMEL#', $primary_key_camelized, $controller_content);
                $controller_content = str_replace('#HUMAN_MODEL#', $human_model, $controller_content);

                $target_controller_file = sprintf('%s/controllers/%sController.php', $this->path, $model_controller_class);
                if (!file_exists($target_controller_file) || $this->force) {
                    @mkdir(dirname($target_controller_file), 0777, true);
                    file_put_contents($target_controller_file, $controller_content);
                } else {
                    throw new Exception(sprintf('Unable to create \'%s\' controller, file exists. Use --force if you want to replace it.', $target_controller_file));
                }

                # Editor files
                $layoutXmlFile = sprintf('%s%s', $this->currentDir, $this->templatePaths['layout_xml']);
                $layoutXmlContent = file_get_contents($layoutXmlFile);
                $layoutXmlContent = str_replace('#MODULE_ACTION#', $module_action, $layoutXmlContent);

                $targetLayoutXmlFile = sprintf('%s/resources/design/desktop/flat/layout/%s.xml', $this->path, $module_action);
                if (!file_exists($targetLayoutXmlFile) || $this->force) {
                    @mkdir(dirname($targetLayoutXmlFile), 0777, true);
                    file_put_contents($targetLayoutXmlFile, $layoutXmlContent);
                } else {
                    throw new Exception(sprintf('Unable to create \'%s\' layout, file exists. Use --force if you want to replace it.', $layoutXmlContent));
                }

                $editCssFile = sprintf('%s%s', $this->currentDir, $this->templatePaths['edit_css']);
                $editCssContent = file_get_contents($editCssFile);
                $editCssContent = str_replace('#MODULE_ACTION#', $module_action, $editCssContent);

                $targetEditCssFile = sprintf('%s/resources/design/desktop/flat/template/%s/application/edit.css', $this->path, $module_action);
                if (!file_exists($targetEditCssFile) || $this->force) {
                    @mkdir(dirname($targetEditCssFile), 0777, true);
                    file_put_contents($targetEditCssFile, $editCssContent);
                } else {
                    throw new Exception(sprintf('Unable to create \'%s\' css, file exists. Use --force if you want to replace it.', $targetEditCssFile));
                }

                $editJsFile = sprintf('%s%s', $this->currentDir, $this->templatePaths['edit_js']);
                $editJsContent = file_get_contents($editJsFile);
                $editJsContent = str_replace('#MODULE_ACTION#', $module_action, $editJsContent);

                $targetEditJsFile = sprintf('%s/resources/design/desktop/flat/template/%s/application/edit.js', $this->path, $module_action);
                if (!file_exists($targetEditJsFile) || $this->force) {
                    @mkdir(dirname($targetEditJsFile), 0777, true);
                    file_put_contents($targetEditJsFile, $editJsContent);
                } else {
                    throw new Exception(sprintf('Unable to create \'%s\' js, file exists. Use --force if you want to replace it.', $targetEditJsFile));
                }

                $editHtmlFile = sprintf('%s%s', $this->currentDir, $this->templatePaths['edit_html']);
                $editHtmlContent = file_get_contents($editHtmlFile);
                $editHtmlContent = str_replace('#MODULE_ACTION#', $module_action, $editHtmlContent);
                $editHtmlContent = str_replace('#MODULE#', $this->module, $editHtmlContent);
                $editHtmlContent = str_replace('#PRIMARY_KEY#', $primary_key, $editHtmlContent);


                $head = '';
                $body = '';
                foreach ($columns as $name => $options) {
                    if (isset($options['primary'])) {


                    }
                }

                /**
                 *
                #FIELDS_HEAD#
                <th class="sortable" style="width:26%;"><?php echo __("Title"); ?></th>
                <th class="sortable" style="width:64%;"><?php echo __("Subtitle"); ?></th>
                 */

                /**
                #FIELDS_BODY#
                <td><?php echo $item->getTitle(); ?></td>
                <td><?php echo cut(html_entity_decode($item->getSubtitle()), 60); ?></td>
                 */

                $targetEditHtmlFile = sprintf('%s/resources/design/desktop/flat/template/%s/application/edit.phtml', $this->path, $module_action);
                if (!file_exists($targetEditHtmlFile) || $this->force) {
                    @mkdir(dirname($targetEditHtmlFile), 0777, true);
                    file_put_contents($targetEditHtmlFile, $editHtmlContent);
                } else {
                    throw new Exception(sprintf('Unable to create \'%s\' phtml, file exists. Use --force if you want to replace it.', $targetEditHtmlFile));
                }
            }
        }
    }
}

new Module($argv);
