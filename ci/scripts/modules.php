<?php

class Module
{

    /**
     * @var string
     */
    public $path;

    /**
     * @var string
     */
    public $current_dir;

    /**
     * @var string
     */
    public $templates_path = [
        'model' => '/modules_templates/Model/model.php',
        'table' => '/modules_templates/Model/Db/Table/table.php',
        'form' => '/modules_templates/Form/form.php',
        'form_delete' => '/modules_templates/Form/form_delete.php',
        'crud_controller' => '/modules_templates/controllers/crud_controller.php',
    ];

    /**
     * @var string
     */
    public $path_to_schema = '/resources/db/schema';

    /**
     * @var string
     */
    public $module;

    /**
     * @var string
     */
    public $model;

    /**
     * 'all', 'model', 'form', 'controller'
     *
     * @var string
     */
    public $type = 'all';

    /**
     * @var bool
     */
    public $build_all = false;

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
        $this->current_dir = __DIR__;
        $this->parseArgv($argv);
    }

    /**
     * Build phase
     */
    public function build()
    {
        if ($this->build_all) {
            $this->fetchSchemas();
        }

        switch ($this->type) {
            case 'all':
                $this->buildModel();
                $this->buildForm();
                $this->buildController();
                break;
            case 'model':
                $this->buildModel();
                break;
            case 'form':
                $this->buildForm();
                break;
            case 'controller':
                $this->buildController();
                break;
        }
    }

    /**
     * Parse arguments to fill-in the Builder
     *
     * @param $argv
     */
    public function parseArgv($argv)
    {
        foreach ($argv as $arg) {
            $parts = explode('=', $arg);
            $key = str_replace('-', '_', str_replace('--', '', $parts[0]));
            # Boolean if only key
            $value = isset($parts[1]) ? $parts[1] : true;

            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * Fetch all availables schemas
     */
    public function fetchSchemas()
    {
        $schemas_path = sprintf('%s%s', $this->path, $this->path_to_schema);
        $files = new DirectoryIterator($schemas_path);

        $models = [];
        foreach ($files as $file) {
            if ($file->isFile() && !$file->isDot()) {
                $models[] = str_replace('.php', '', $file->getFilename());
            }
        }

        $this->model = implode(',', $models);
    }

    /**
     * @throws Exception
     */
    public function buildModel()
    {
        # Try for multiple models
        $models = explode(',', $this->model);

        foreach ($models as $model) {
            $model_shorten = trim(str_replace(strtolower($this->module.'_'), '', $model));
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
                $camelized_model = str_replace('_', '', ucwords($model_shorten, '_'));

                # Model file
                $model_file = sprintf('%s%s', $this->current_dir, $this->templates_path['model']);
                $model_content = file_get_contents($model_file);
                $model_content = str_replace('#MODULE#', $this->module, $model_content);
                $model_content = str_replace('#NAME#', $camelized_model, $model_content);

                $target_model_file = sprintf('%s/Model/%s.php', $this->path, $camelized_model);
                if (!file_exists($target_model_file) || $this->force) {
                    @mkdir(dirname($target_model_file), 0777, true);
                    file_put_contents($target_model_file, $model_content);
                } else {
                    throw new Exception(sprintf('Unable to create \'%s\' model, file exists. Use --force if you want to replace it.', $target_model_file));
                }

                # Db_Table file
                $table_file = sprintf('%s%s', $this->current_dir, $this->templates_path['table']);
                $table_content = file_get_contents($table_file);
                $table_content = str_replace('#MODULE#', $this->module, $table_content);
                $table_content = str_replace('#NAME#', $camelized_model, $table_content);
                $table_content = str_replace('#TABLE_NAME#', $model, $table_content);
                $table_content = str_replace('#PRIMARY_KEY#', $primary_key, $table_content);

                $target_table_file = sprintf('%s/Model/Db/Table/%s.php', $this->path, $camelized_model);

                if (!file_exists($target_table_file) || $this->force) {
                    @mkdir(dirname($target_table_file), 0777, true);
                    file_put_contents($target_table_file, $table_content);
                } else {
                    throw new Exception(sprintf('Unable to create \'%s\' table, file exists. Use --force if you want to replace it.', $target_table_file));
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    public function buildForm()
    {
        # Try for multiple models
        $models = explode(",", $this->model);

        foreach ($models as $model) {
            $model_shorten = trim(str_replace(strtolower($this->module.'_'), '', $model));
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

                $build_elements = [];
                foreach ($columns as $name => $options) {
                    if (isset($options['primary'])) {
                        $primary_key = $name;
                    } else {
                        if (in_array($name, array('created_at', 'updated_at'))) {
                            continue;
                        }
                        if (isset($options['type'])) {
                            $type = strtolower($options['type']);
                            $required = isset($options['is_null']) ? !$options['is_null'] : true;

                            if (strpos($type, 'tinyint(1)') !== false || strpos($type, 'boolean') !== false) {
                                $build_elements[] = $this->renderBoolean($name, $required);
                            } else if (strpos($type, 'int') !== false ||
                                strpos($type, 'float') !== false ||
                                strpos($type, 'double') !== false ||
                                strpos($type, 'decimal') !== false ||
                                strpos($type, 'real') !== false ||
                                strpos($type, 'numeric') !== false) {

                                $build_elements[] = $this->renderNumeric($name, $required);
                            } else if (strpos($type, 'char') !== false) {
                                $build_elements[] = $this->renderText($name, $required);
                            } else if (strpos($type, 'text') !== false) {
                                $build_elements[] = $this->renderTextarea($name, $required);
                            } else if (strpos($type, 'enum') !== false) {
                                $build_elements[] = $this->renderSelect($name, $type, $required);
                            } else if (strpos($type, 'date') !== false) {
                                $build_elements[] = $this->renderDate($name, $required);
                            } else if (strpos($type, 'datetime') !== false || strpos($type, 'timestamp') !== false) {
                                $build_elements[] = $this->renderDatetime($name, $required);
                            } else if (strpos($type, 'time') !== false) {
                                $build_elements[] = $this->renderTime($name, $required);
                            }

                        }
                    }
                }

                if (!$primary_key) {
                    throw new Exception(sprintf('Unable to find a primary_key for \'%s\' model.', $model));
                }

                # Opts
                $module_action = strtolower($this->module);
                $model_controller = strtolower(str_replace('_', '', $model_shorten));
                $camelized_model = str_replace('_', '', ucwords($model_shorten, '_'));
                $save_action = sprintf('/%s/%s/editpost', $module_action, $model_controller);
                $delete_action = sprintf('/%s/%s/deletepost', $module_action, $model_controller);
                $model_id = str_replace('_', '-', $model);
                $primary_key_camelized = str_replace('_', '', ucwords($primary_key, '_'));
                # Appointment Provider
                $human_model = str_replace('_', ' ', ucwords($model_shorten, '_'));
                # Elements
                $build_elements = implode("\n\n", $build_elements);

                // #FORM_SAVE_ACTION#
                // #FORM_ID#
                // #MODULE_LOWER#

                # Form file
                $form_file = sprintf('%s%s', $this->current_dir, $this->templates_path['form']);
                $form_content = file_get_contents($form_file);
                $form_content = str_replace('#MODULE#', $this->module, $form_content);
                $form_content = str_replace('#MODEL#', $camelized_model, $form_content);
                $form_content = str_replace('#FORM_SAVE_ACTION#', $save_action, $form_content);
                $form_content = str_replace('#MODULE_LOWER#', $model, $form_content);
                $form_content = str_replace('#FORM_ID#', $model_id, $form_content);
                $form_content = str_replace('#PRIMARY_KEY#', $primary_key, $form_content);
                $form_content = str_replace('#PRIMARY_KEY_CAMEL#', $primary_key_camelized, $form_content);
                $form_content = str_replace('#HUMAN#', $human_model, $form_content);
                $form_content = str_replace('#ELEMENTS#', $build_elements, $form_content);

                $target_form_file = sprintf('%s/Form/%s.php', $this->path, $camelized_model);
                if (!file_exists($target_form_file) || $this->force) {
                    @mkdir(dirname($target_form_file), 0777, true);
                    file_put_contents($target_form_file, $form_content);
                } else {
                    throw new Exception(sprintf('Unable to create \'%s\' form, file exists. Use --force if you want to replace it.', $target_form_file));
                }

                # Form delete file
                $form_delete_file = sprintf('%s%s', $this->current_dir, $this->templates_path['form_delete']);
                $form_delete_content = file_get_contents($form_delete_file);
                $form_delete_content = str_replace('#MODULE#', $this->module, $form_delete_content);
                $form_delete_content = str_replace('#MODEL#', $camelized_model, $form_delete_content);
                $form_delete_content = str_replace('#FORM_DELETE_ACTION#', $delete_action, $form_delete_content);
                $form_delete_content = str_replace('#FORM_ID#', $model_id, $form_delete_content);
                $form_delete_content = str_replace('#TABLE_NAME#', $model, $form_delete_content);
                $form_delete_content = str_replace('#PRIMARY_KEY#', $primary_key, $form_delete_content);
                $form_delete_content = str_replace('#HUMAN#', $human_model, $form_delete_content);

                $target_form_delete_file = sprintf('%s/Form/%s/Delete.php', $this->path, $camelized_model);

                if (!file_exists($target_form_delete_file) || $this->force) {
                    @mkdir(dirname($target_form_delete_file), 0777, true);
                    file_put_contents($target_form_delete_file, $form_delete_content);
                } else {
                    throw new Exception(sprintf('Unable to create \'%s\' form delete, file exists. Use --force if you want to replace it.', $target_form_delete_file));
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    public function buildController() {
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
                $form_file = sprintf('%s%s', $this->current_dir, $this->templates_path['crud_controller']);
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

            }
        }
    }

    /**
     * @param $name
     * @param bool $required
     * @return string
     */
    public function renderBoolean($name, $required = false) {
        $code = '
        $'.$name.' = $this->addSimpleCheckbox("'.$name.'", __("'.$this->humanize($name).'"));';

        if($required) {
            $code .= '
        $'.$name.'->setRequired(true);';
        }

        return $code;
    }

    /**
     * @param $name
     * @param bool $required
     */
    public function renderNumeric($name, $required = false) {
        $code = '
        $'.$name.' = $this->addSimpleText("'.$name.'", __("'.$this->humanize($name).'"));';

        if($required) {
            $code .= '
        $'.$name.'->setRequired(true);';
        }

        return $code;
    }

    /**
     * @param $name
     * @param bool $required
     * @return string
     */
    public function renderText($name, $required = false) {
        $code = '
        $'.$name.' = $this->addSimpleText("'.$name.'", __("'.$this->humanize($name).'"));';

        if($required) {
            $code .= '
        $'.$name.'->setRequired(true);';
        }

        return $code;
    }

    /**
     * @param $name
     * @param bool $required
     * @return string
     */
    public function renderTextarea($name, $required = false) {
        $code = '
        $'.$name.' = $this->addSimpleTextarea("'.$name.'", __("'.$this->humanize($name).'"));';

        if($required) {
            $code .= '
        $'.$name.'->setRequired(true);';
        }

        return $code;
    }

    /**
     * @param $name
     * @param $type
     * @param bool $required
     * @return string
     */
    public function renderSelect($name, $type, $required = false) {
        $opts = str_replace(")", "", str_replace("enum(", "", str_replace("\\", "", $type)));
        $opts = explode(",", $opts);

        $values = "";
        foreach($opts as $opt) {
            $values .= '
            "$opt" => "'.$this->humanize($opt).'",';
        }

        $code = '
        $'.$name.' = $this->addSimpleSelect("'.$name.'", __("'.$this->humanize($name).'"), array(
            '.$values.'
        ));';

        if($required) {
            $code .= '
        $'.$name.'->setRequired(true);';
        }

        return $code;
    }

    /**
     * @param $name
     * @param bool $required
     * @return string
     */
    public function renderDate($name, $required = false) {
        $code = '
        $'.$name.' = $this->addSimpleDatetimepicker("'.$name.'", __("'.$this->humanize($name).'"), false, Siberian_Form_Abstract::DATEPICKER);';

        if($required) {
            $code .= '
        $'.$name.'->setRequired(true);';
        }

        return $code;
    }

    /**
     * @param $name
     * @param bool $required
     * @return string
     */
    public function renderDatetime($name, $required = false) {
        $code = '
        $'.$name.' = $this->addSimpleDatetimepicker("'.$name.'", __("'.$this->humanize($name).'"), false, Siberian_Form_Abstract::DATETIMEPICKER);';

        if($required) {
            $code .= '
        $'.$name.'->setRequired(true);';
        }

        return $code;
    }

    /**
     * @param $name
     * @param bool $required
     * @return string
     */
    public function renderTime($name, $required = false) {
        $code = '
        $'.$name.' = $this->addSimpleDatetimepicker("'.$name.'", __("'.$this->humanize($name).'"), false, Siberian_Form_Abstract::TIMEPICKER);';

        if($required) {
            $code .= '
        $'.$name.'->setRequired(true);';
        }

        return $code;
    }

    /**
     * @param $text
     * @return mixed
     */
    public function humanize($text) {
        return str_replace('_', ' ', ucwords($text, '_'));
    }

}

try {
    $module = new Module($argv);
    $module->build();
} catch(Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}

print_r($module);