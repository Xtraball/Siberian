# Cron jobs

The scheduler is `siberian/cron.php`, run every minute by the system cron. It calls `Siberian\Cron::triggerAll()`, which
runs the active rows of the `cron` table whose schedule matches the minute.

## What a job is

`Feature::installCronjob($name, $command, ...)` registers a row. `command` is either

- the name of a public method of `Siberian\Cron` (`logrotate`, `sources`, ...), called with the task, or
- `Class::method` (a module job), called with the scheduler and the task.

## What the scheduler guarantees

A job that cannot run, or that throws, never stops the others:

- the job is checked before it is called (`Siberian\Cron\Callback::prepare`): the class loads, the method exists, is
  public, and does not require more arguments than it is given;
- a non static `Class::method` is called on a new instance. PHP 8 refuses to call it statically (`call_user_func`
  throws a `TypeError`), PHP 7 only warned. Declare the method `static` in new code;
- anything thrown (`Error` and `Exception`) is caught per job, the lock is released, the message is stored on the job
  (`last_error`, shown in the Service panel of the dashboard) and the job is marked failed. The next job runs.

Note for the runner itself: in `namespace Siberian` a bare `catch (Exception $e)` catches `Siberian\Exception` only.
Write `\Throwable`.

## Seeing what cannot run

- Settings > Advanced > Cron: the "Compatibility with PHP" section checks every job against the PHP version of the
  server without running it, and lists the ones that cannot run with the reason and the fix. The jobs carry an icon in
  the tables.
- Dashboard > Services > Cron scheduler: a line names the jobs that cannot run, with a link to the page above.

`Siberian\Cron\Callback::report()` is pure (no database): `tests/php-upgrade/regression/CronCallbackTest.php`.

## Checking by hand

```
php siberian/cron.php runcommand 'Module_Model_Job::run'
```

runs one job through the same checks and prints the reason when it cannot run.
