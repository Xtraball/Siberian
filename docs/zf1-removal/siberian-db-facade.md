# Siberian\Db: a facade over PDO for the part of Zend_Db we use

Part of [0001 Remove Zend Framework 1](../decisions/0001-remove-zf1.md), step 4. A design to review, nothing is built yet. Measured on `trunk` on 2026-11-06 unless said otherwise.

## What the code uses today

### The layers

| Layer | Today | Size |
|---|---|---|
| Adapter | `Siberian_Db_Adapter_Pdo_Mysql extends Zend_Db_Adapter_Pdo_Mysql`: one override, `query()`, which reconnects once on "MySQL server has gone away" (2006). Configured by `resources.db.*` in `app.ini` (`adapter = PDO_MYSQL`, `adapterNamespace = Siberian_Db_Adapter`, host, dbname, username, password, charset UTF8, `date_format`), created in `Bootstrap.php` and `BootstrapCron.php` and stored in `Zend_Registry` as `db`. The installer builds one with `Zend_Db::factory('Pdo_Mysql', $params)` before `app.ini` exists | 40 lines of our own |
| Table | `Core_Model_Db_Table extends Zend_Db_Table_Abstract` (361 lines): `findById`, `findByField`, `findByArray`, `findLastByField`, `findLastByArray`, `findAll`, `findAllWithFilters`, `countAll`, `getFields`, `getPrimaryKey`, `insert`, `update`, `delete`, `beginTransaction`, `commit`, `rollback`, `checkConnection`, `toModelClass`. About 160 core classes extend it or `Zend_Db_Table_Abstract` | |
| Row | `Core_Model_Db_Table_Row` (261 lines) on `Zend_Db_Table_Row_Abstract`: model class binding, `getData`/`setData`/`mergeData`/`getId`/`setId`, a magic `__call` for `getX`/`setX`, `_insert`/`_update` hooks, excluded columns | |
| Rowset | `Siberian_Db_Table_Rowset` (101 lines): `findById`, `addRow`, `removeRow`, `removeCurrent`, `toSelectOptions` | |
| Profiler | `Siberian_Db_Profiler` (44 lines) on `Zend_Db_Profiler` | |
| Builder | `Zend_Db_Select` and `Zend_Db_Expr`, used directly all over the models and controllers | |

The model base class `Core_Model_Default_Abstract` reaches the table through `getTable()` (`findById`, `findByArray`, `findByField`, `findLastByArray`, `findAll`, `countAll`, `getFields`, `createRow`).

### The surface, counted by method name

Occurrences of `->method(` in core (`siberian/app`, `siberian/lib/Siberian`) and in the `siberian-modules` collection (250 PHP files). It is a **count by name, not by receiver type**: `delete`, `update`, `query`, `where` also match non-database methods, so read it as an upper bound that ranks what matters. Redo it with a real parser before the swap.

| Method | Core | Modules | | Method | Core | Modules |
|---|---|---|---|---|---|---|
| `where` | 305 | 150 | | `limit` | 50 | 10 |
| `findAll` | 288 | 56 | | `fetchRow` | 48 | 18 |
| `select` | 190 | 63 | | `setIntegrityCheck` | 47 | 11 |
| `from` | 177 | 63 | | `joinLeft` | 41 | 12 |
| `query` | 157 | 4 | | `fetchOne` | 32 | 2 |
| `delete` | 144 | 20 | | `orWhere` | 31 | 0 |
| `fetchAll` | 116 | 41 | | `prepare` | 24 | 3 |
| `order` | 80 | 27 | | `update` / `insert` | 19 / 18 | 1 / 1 |
| `join` | 71 | 12 | | `group` | 18 | 8 |
| `quoteInto` | 52 | 21 | | `commit` / `beginTransaction` / `rollback` | 13 / 11 / 10 | 1 / 1 / 1 |
| `joinInner` | 0 | 25 | | `fetchCol` / `fetchAssoc` | 9 / 8 | 0 / 0 |
| `having` | 7 | 6 | | `distinct` / `columns` / `union` / `assemble` | 3 / 2 / 1 / 3 | 0 |

Classes named in code (core / modules): `Zend_Db_Expr` 103 / 66, `Zend_Db_Table` 41 / 17, `Zend_Db_Table_Rowset_Abstract` 21 / 18, `Zend_Db_Statement_Exception` 20 / 33, `Zend_Db_Table_Row_Abstract` 16 / 7, `Zend_Db_Select_Exception` 11 / 34, `Zend_Db_Adapter_Exception` 10 / 2, `Zend_Db_Table_Exception` 3 / 12, `Zend_Db_Adapter_Abstract` 5 / 0, `Zend_Db_Select` 1 / 2. The exception types matter as much as the builder: modules catch them.

Less common features (files, core / modules):

| Feature | Core | Modules |
|---|---|---|
| `getDefaultAdapter` (the static way to get the connection) | 30 | 10 |
| `describeTable` | 13 | 0 |
| `Zend_Db_Profiler` / `setProfiler` | 6 / 2 | 0 |
| `setDefaultMetadataCache` | 2 | 0 |
| `_referenceMap`, `findManyToManyRowset` | 1, 1 | 0 |
| `_dependentTables`, `findDependentRowset`, `findParentRow` | 0 | 0 |
| `Zend_Paginator`, `setFetchMode`, `Zend_Db_Table_Select`, `listTables` | 0 | 0 |

## The contract Siberian\Db keeps

Only what is used above. MySQL and MariaDB only.

**`Siberian\Db\Connection`** (PDO underneath, lazy connect, same `app.ini` keys, utf8 charset, reconnect once on error 2006 like today's override, 2013 to consider):

- `query($sql|Select, array $bind = [])` returning a statement, `prepare`, `execute`;
- `fetchAll`, `fetchRow`, `fetchOne`, `fetchCol`, `fetchAssoc`, `fetchPairs` with `$bind`; default fetch mode associative array;
- `insert($table, array)`, `update($table, array, $where)`, `delete($table, $where)`: affected rows; `lastInsertId()`;
- `quote`, `quoteInto($text, $value)` (arrays become a comma list, `Expr` is not quoted), `quoteIdentifier`;
- `beginTransaction`, `commit`, `rollBack` (no nesting, as today);
- `select()`, `getConnection()` (the raw `\PDO`, 5 calls in core), `describeTable($table)`, `closeConnection()`;
- `Siberian\Db::connection()` replaces `Zend_Registry::get('db')` and the static `getDefaultAdapter()` (30 + 10 files).

**`Siberian\Db\Select`**: `from`, `columns`, `where` / `orWhere` (with `?` binding and arrays), `join`, `joinInner`, `joinLeft`, `joinRight`, `joinCross`, `order`, `limit`, `limitPage`, `group`, `having`, `orHaving`, `distinct`, `forUpdate`, `union`, `reset`, `assemble`, `__toString`, and the array forms ZF1 accepts for table names and columns (`['alias' => 'table']`, `['a' => 'COUNT(*)']`). `setIntegrityCheck()` stays as a no-op marked deprecated (47 + 11 calls only exist because ZF1 made join rows read-only).

**`Siberian\Db\Expr`**: a string that is never quoted.

**`Siberian\Db\Table`, `Row`, `Rowset`**: the public methods of `Core_Model_Db_Table`, `Core_Model_Db_Table_Row` and `Siberian_Db_Table_Rowset` as listed above, plus what `Core_Model_Default_Abstract` calls on the table (`findById`, `findByArray`, `findByField`, `findLastByArray`, `findAll`, `countAll`, `getFields`, `createRow`); the other methods of `Zend_Db_Table_Abstract` and of rows that the measurement finds are added to the contract only if used. Rowset is `Iterator`, `Countable`, `SeekableIterator` and `ArrayAccess` over lazily built rows. Table metadata (primary key, columns) comes from `DESCRIBE` and is cached like today (`setDefaultMetadataCache`).

**Exceptions**: `Siberian\Db\Exception` and subclasses for statement, select, adapter and table errors, carrying the SQLSTATE and driver code. They replace the four `Zend_Db_*_Exception` types modules catch. Code that catches `\Exception` or `\Throwable` is unaffected.

## What we would NOT keep

- Other databases than MySQL and MariaDB (`Pgsql`, `Oracle`, `Sqlsrv`, `Db2`, `Mysqli` adapters, the `Zend_Db::factory` adapter name lookup).
- Table relationships: `_dependentTables`, `findDependentRowset`, `findParentRow` have no use; `_referenceMap` and `findManyToManyRowset` have one file each in core, to check and port by hand.
- `Zend_Db_Table_Select` (the select that knows its table): `Table::select()` returns a plain `Siberian\Db\Select`.
- Fetch modes other than associative and numeric arrays, `fetchObject`, `setFetchMode`.
- The static default adapter on the table class and lookup of the adapter by the registry key `db`.
- `setIntegrityCheck` as behaviour (kept as a no-op).
- `Zend_Db_Profiler` and its subclasses: a small query log in `Siberian\Db\Profiler` stays (there is a `Siberian_Db_Profiler` today).
- `Zend_Paginator` adapters (unused).
- Anything in the table above with zero uses. If a module needs it, the author calls PDO.

Not decided, measure first: property access on rows (`$row->field`), which ZF1 rows support; `Rowset::toArray`; `Row::save()` on rows built from a join.

## Migration steps

The point is that no step needs the swap to work, and every step can be reverted alone.

1. **Measure again, with a parser.** Replace the counts above with a script (`nikic/php-parser`, run offline) that finds calls whose receiver is a `Zend_Db_*` object, plus the string class names in config. Run it on core and on every module collection. Keep the output in `docs/zf1-removal/`.
2. **Freeze behaviour in tests** (next section). They run against a real MariaDB, in the CI image, and they run against `Zend_Db` first.
3. **Add the facade classes as thin subclasses of ZF1** (`Siberian\Db\Expr extends Zend_Db_Expr`, `Siberian\Db\Select extends Zend_Db_Select`, and so on; each `Zend_` line says `ZF1-OK: facade`). No behaviour changes. `Siberian\Db\Exception` types are introduced as parents the ZF1 exceptions are re-thrown as, only where the facade itself throws.
4. **Move callers, mechanical first:** `new Zend_Db_Expr` to `new \Siberian\Db\Expr` (core 103, modules 66), then `catch` clauses, then `Zend_Registry::get('db')` and `getDefaultAdapter()` to `Siberian\Db::connection()`, then select builders, then the table, row and rowset base classes. Each as its own pull request, one or two modules at a time. 5.x.
5. **Swap the implementation** (6.0): the facade classes stop extending ZF1 and use PDO. The differential tests must pass with both. The installer path (`Zend_Db::factory`) moves in the same step.
6. **Delete** `lib/Zend/Db*`, the allowlist entries and the `ZF1-OK` markers of this area.

## Tests that must exist before the swap

All run on both implementations; the swap is judged by them.

- **SQL builder, differential.** A corpus of selects collected from the models and modules (log the assembled SQL of the e2e flows with ZF1 and keep it as fixtures), rebuilt with the new builder and compared as text and, on a real database, as results. Include aliases (`'a' => 'table'`), `COUNT(*)` and other expression columns (ZF1 treats a column containing a parenthesis as an expression), joins with a column list, `where` with arrays and `?`, `limit` with offset, `group`/`having`, `union`, `forUpdate`.
- **Quoting.** `quote` and `quoteInto` over strings with quotes and backslashes, `null`, booleans, integers, floats, arrays, `Expr`, strings that look like numbers, multibyte text, identifiers with dots and aliases.
- **Types.** What PHP type each MySQL column type comes back as (ints and decimals as strings or numbers, dates as strings, `NULL`), with the same PDO attributes as today. Models and the API output depend on it.
- **Table.** Primary key and columns detected from `DESCRIBE`, `findById` / `findByArray` / `findAll` with filters, `insert` returning the id, `update` and `delete` with a where array and a string, `countAll`, the row hooks `_insert` and `_update`, excluded columns, `createRow` then `save`.
- **Rowset.** Iteration, `count`, `current`, `seek`, `findById`, `addRow`, `removeRow`, `toSelectOptions`, lazy hydration.
- **Transactions.** Commit, rollback, an exception inside a transaction, no nesting.
- **Connection.** Reconnect after the server closed the connection (kill the connection, then query), charset, config from `app.ini`, the installer connecting with test credentials before `app.ini` exists, the cron and CLI entry points.
- **Errors.** The class and message of the exception for a syntax error, a duplicate key, a missing table and a lost connection; modules catch these.
- **Schema sync and migrations** (`Siberian\Migration`, `resources/db/schema`) run on a fresh database and on an upgrade: they use the adapter too.

## Risks specific to Db

- Result types and quoting differences change data without an error. The differential tests exist to catch this; do not swap without them.
- `getDefaultAdapter()` in 30 core and 10 module files is the real coupling, more than the builder. It is the first thing to replace.
- Modules catch `Zend_Db_Statement_Exception` and `Zend_Db_Select_Exception` (33 and 34 times in the one collection we have). They are the first modules to break at 6.0, and the migration note must list the replacement.
- Ciphered modules can call any of this and cannot be inspected.
- The installer is code that runs before the framework is configured, and must work at every step.
