<?php

namespace Siberian\Cron;

use ReflectionClass;
use ReflectionMethod;
use Throwable;

/**
 * Class Callback
 *
 * Checks that the command of a cron job can be called by the scheduler with the running PHP version, and builds the
 * call. A command is either the name of a public method of \Siberian\Cron, or "Class::method" (a module job), which the
 * scheduler calls with the scheduler and the task.
 *
 * PHP 8 turned some calls that used to warn into fatal errors: a non static method named as "Class::method" can no
 * longer be called statically (TypeError in call_user_func), a method requiring more arguments than it is given throws
 * an ArgumentCountError. A module installed years ago can hold such a job, so it is checked here instead of being
 * discovered when the scheduler runs it.
 */
class Callback
{
    const OK = 'ok';
    const WARNING = 'warning';
    const ERROR = 'error';

    /**
     * What the scheduler gives a "Class::method" job, and a method of the scheduler itself.
     */
    const ARGUMENTS_CLASS_METHOD = 2;
    const ARGUMENTS_LOCAL_METHOD = 1;

    /**
     * Looks at a command without running it.
     *
     * @param string $command
     * @param string|object $runner the scheduler, the class when the command names one of its methods
     * @return array{status:string,code:string,command:string,message:string}
     */
    public static function inspect($command, $runner = \Siberian\Cron::class)
    {
        $command = trim((string) $command);
        if ($command === '') {
            return self::result(self::ERROR, 'empty_command', $command);
        }

        if (strpos($command, '::') === false) {
            return self::inspectMethod(is_object($runner) ? get_class($runner) : $runner, $command, $command, true);
        }

        list($class, $method) = array_pad(explode('::', $command, 2), 2, '');
        try {
            $exists = class_exists($class);
        } catch (Throwable $e) {
            // The autoloader can throw on a file that does not parse with this version of PHP
            return self::result(self::ERROR, 'class_error', $command, [$class, $e->getMessage()]);
        }

        if (!$exists) {
            return self::result(self::ERROR, 'missing_class', $command, [$class]);
        }

        return self::inspectMethod($class, $method, $command, false);
    }

    /**
     * Inspects the command and, when it can run, adds the call to make: `call` is a closure to invoke without
     * arguments, null when the command can not run (the reason is the message). A non static method is called on a
     * new instance.
     *
     * @param string $command
     * @param object $runner the scheduler
     * @param mixed $task the task given to the job
     * @return array
     */
    public static function prepare($command, $runner, $task)
    {
        $report = self::inspect($command, $runner);
        $report['call'] = null;
        if ($report['status'] === self::ERROR) {
            return $report;
        }

        $command = trim((string) $command);
        if (strpos($command, '::') === false) {
            $report['call'] = function () use ($runner, $command, $task) {
                return $runner->$command($task);
            };

            return $report;
        }

        list($class, $method) = explode('::', $command, 2);
        $report['call'] = function () use ($class, $method, $runner, $task) {
            $target = (new ReflectionMethod($class, $method))->isStatic() ? $class : new $class();

            return call_user_func([$target, $method], $runner, $task);
        };

        return $report;
    }

    /**
     * The state of every job: `results` by command, and what needs attention among the active ones.
     *
     * @param array $jobs each with `command`, and `is_active`
     * @param string $phpVersion
     * @param string|object $runner
     * @return array
     */
    public static function report(array $jobs, $phpVersion = PHP_VERSION, $runner = \Siberian\Cron::class)
    {
        $report = [
            'php_version' => $phpVersion,
            'checked' => 0,
            'ok' => 0,
            'warnings' => 0,
            'errors' => 0,
            'problems' => 0,
            'items' => [],
            'results' => [],
        ];

        foreach ($jobs as $job) {
            $command = isset($job['command']) ? (string) $job['command'] : '';
            $result = self::inspect($command, $runner);
            $report['results'][$command] = $result;

            // A disabled job never runs, it is shown but not counted
            if (empty($job['is_active'])) {
                continue;
            }

            $report['checked']++;
            if ($result['status'] === self::OK) {
                $report['ok']++;
                continue;
            }

            $report[$result['status'] === self::ERROR ? 'errors' : 'warnings']++;
            $report['problems']++;
            $report['items'][] = $result + ['name' => isset($job['name']) ? (string) $job['name'] : $command];
        }

        return $report;
    }

    /**
     * @param string|object $class
     * @param string $method
     * @param string $command
     * @param bool $local the method of the scheduler
     * @return array
     */
    private static function inspectMethod($class, $method, $command, $local)
    {
        if ($method === '' || !method_exists($class, $method)) {
            return self::result(self::ERROR, 'missing_method', $command, [$command]);
        }

        $reflection = new ReflectionMethod($class, $method);
        if (!$reflection->isPublic()) {
            return self::result(self::ERROR, 'not_public', $command, [$command]);
        }

        if ($reflection->isAbstract()) {
            return self::result(self::ERROR, 'abstract_method', $command, [$command]);
        }

        $given = $local ? self::ARGUMENTS_LOCAL_METHOD : self::ARGUMENTS_CLASS_METHOD;
        $required = $reflection->getNumberOfRequiredParameters();
        if ($required > $given) {
            return self::result(self::ERROR, 'too_many_arguments', $command, [$command, $required, $given]);
        }

        if ($local || $reflection->isStatic()) {
            return self::result(self::OK, 'ok', $command);
        }

        $owner = new ReflectionClass($class);
        if (!$owner->isInstantiable()) {
            return self::result(self::ERROR, 'not_instantiable', $command, [$class, $command]);
        }

        $constructor = $owner->getConstructor();
        if ($constructor !== null && $constructor->getNumberOfRequiredParameters() > 0) {
            return self::result(self::ERROR, 'needs_constructor_args', $command, [$command, $class]);
        }

        return self::result(self::WARNING, 'non_static', $command, [$command]);
    }

    /**
     * @param string $status
     * @param string $code
     * @param string $command
     * @param array $arguments
     * @return array{status:string,code:string,command:string,message:string}
     */
    private static function result($status, $code, $command, array $arguments = [])
    {
        return [
            'status' => $status,
            'code' => $code,
            'command' => $command,
            'message' => self::describe($code, $arguments),
        ];
    }

    /**
     * @param string $code
     * @param array $arguments
     * @return string
     */
    private static function describe($code, array $arguments)
    {
        switch ($code) {
            case 'empty_command':
                return p__('backoffice', 'This job has no command.');
            case 'missing_class':
                return vsprintf(p__('backoffice', 'The class %s does not exist, the module providing this job is probably disabled or removed.'), $arguments);
            case 'class_error':
                return vsprintf(p__('backoffice', 'The class %s can not be loaded: %s'), $arguments);
            case 'missing_method':
                return vsprintf(p__('backoffice', 'The method %s does not exist.'), $arguments);
            case 'not_public':
                return vsprintf(p__('backoffice', 'The method %s is not public.'), $arguments);
            case 'abstract_method':
                return vsprintf(p__('backoffice', 'The method %s is abstract.'), $arguments);
            case 'too_many_arguments':
                return vsprintf(p__('backoffice', 'The method %s requires %d arguments, a job is given %d.'), $arguments);
            case 'not_instantiable':
                return vsprintf(p__('backoffice', 'The class %s can not be instantiated, declare the method %s static.'), $arguments);
            case 'needs_constructor_args':
                return vsprintf(p__('backoffice', 'The method %s is not static and the constructor of %s requires arguments, declare the method static.'), $arguments);
            case 'non_static':
                return vsprintf(p__('backoffice', 'The method %s is not static. PHP 8 can not call it statically, the scheduler runs it on a new instance instead; declare it static.'), $arguments);
        }

        return p__('backoffice', 'This job can run.');
    }
}
