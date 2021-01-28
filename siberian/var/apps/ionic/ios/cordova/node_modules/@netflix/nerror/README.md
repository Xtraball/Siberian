# nerror: rich JavaScript errors

[![Build Status](https://travis-ci.org/Netflix/nerror.svg?branch=master)](https://travis-ci.org/Netflix/nerror)

*Netflix services uses [VError](https://github.com/joyent/node-verror) to make
operation of Node.js applications easier  through meaningful error chains.
VError is an amazing library by Joyent and we  are glad for all the hard work
for the contributors made during the years.
In early 2019 Netflix error handling requirements started to broaden enough that
we had to find a way to make quick iterations on VError with minimizing the
churn on existing VError customers. As a result of this we decided to fork
VError as NError. We hope in the future after the initial development period we
can seek convergence between the two projects.*

This module provides several classes in support of Joyent's [Best Practices for
Error Handling in Node.js](http://www.joyent.com/developers/node/design/errors).
If you find any of the behavior here confusing or surprising, check out that
document first.

## API

See [API](/api.md)

## Classes

The error classes here support:

* printf-style arguments for the message
* chains of causes
* properties to provide extra information about the error
* creating your own subclasses that support all of these

The classes here are:

* **VError**, for chaining errors while preserving each one's error message.
  This is useful in servers and command-line utilities when you want to
  propagate an error up a call stack, but allow various levels to add their own
  context.  See examples below.
* **WError**, for wrapping errors while hiding the lower-level messages from the
  top-level error.  This is useful for API endpoints where you don't want to
  expose internal error messages, but you still want to preserve the error chain
  for logging and debugging.
* **PError**, which is just like VError but does not interpret printf-style
  arguments at all.
* **SError**, which is just like VError but interprets printf-style arguments
  more strictly.
* **MultiError**, which is just an Error that encapsulates one or more other
  errors.  (This is used for parallel operations that return several errors.)

For the full list of features see [API](/api.md).

## Quick start

First, install the package:

    npm install @netflix/nerror

If nothing else, you can use VError as a drop-in replacement for the built-in
JavaScript Error class, with the addition of printf-style messages:

```javascript
const { VError } = require('@netflix/nerror');
const err = new VError('missing file: "%s"', '/etc/passwd');
console.log(err.message);
```

This prints:

    missing file: "/etc/passwd"

You can also pass a `cause` argument, which is any other Error object:

```javascript
const fs = require('fs');
const filename = '/nonexistent';
fs.stat(filename, function (err1) {
	const err2 = new VError(err1, 'stat "%s"', filename);
	console.error(err2.message);
});
```

This prints out:

    stat "/nonexistent": ENOENT, stat '/nonexistent'

which resembles how Unix programs typically report errors:

    $ sort /nonexistent
    sort: open failed: /nonexistent: No such file or directory

To match the Unixy feel, when you print out the error, just prepend the
program's name to the VError's `message`.  Or just call
[node-cmdutil.fail(your_verror)](https://github.com/joyent/node-cmdutil), which
does this for you.

You can get the next-level Error using `err.cause()`:

```javascript
console.error(err2.cause().message);
```

prints:

    ENOENT, stat '/nonexistent'

Of course, you can chain these as many times as you want, and it works with any
kind of Error:

```javascript
const err1 = new Error('No such file or directory');
const err2 = new VError(err1, 'failed to stat "%s"', '/junk');
const err3 = new VError(err2, 'request failed');
console.error(err3.message);
```

This prints:

    request failed: failed to stat "/junk": No such file or directory

The idea is that each layer in the stack annotates the error with a description
of what it was doing.  The end result is a message that explains what happened
at each level.

You can also decorate Error objects with additional information so that callers
can not only handle each kind of error differently, but also construct their own
error messages (e.g., to localize them, format them, group them by type, and so
on).  See the example below.


## Deeper dive

The two main goals for VError are:

* **Make it easy to construct clear, complete error messages intended for
  people.**  Clear error messages greatly improve both user experience and
  debuggability, so we wanted to make it easy to build them.  That's why the
  constructor takes printf-style arguments.
* **Make it easy to construct objects with programmatically-accessible
  metadata** (which we call _informational properties_).  Instead of just saying
  "connection refused while connecting to 192.168.1.2:80", you can add
  properties like `"ip": "192.168.1.2"` and `"tcpPort": 80`.  This can be used
  for feeding into monitoring systems, analyzing large numbers of Errors (as
  from a log file), or localizing error messages.

To really make this useful, it also needs to be easy to compose Errors:
higher-level code should be able to augment the Errors reported by lower-level
code to provide a more complete description of what happened.  Instead of saying
"connection refused", you can say "operation X failed: connection refused".
That's why VError supports `causes`.

In order for all this to work, programmers need to know that it's generally safe
to wrap lower-level Errors with higher-level ones.  If you have existing code
that handles Errors produced by a library, you should be able to wrap those
Errors with a VError to add information without breaking the error handling
code.  There are two obvious ways that this could break such consumers:

* The error's name might change.  People typically use `name` to determine what
  kind of Error they've got.  To ensure compatibility, you can create VErrors
  with custom names, but this approach isn't great because it prevents you from
  representing complex failures.  For this reason, VError provides
  `findCauseByName`, which essentially asks: does this Error _or any of its
  causes_ have this specific type?  If error handling code uses
  `findCauseByName`, then subsystems can construct very specific causal chains
  for debuggability and still let people handle simple cases easily.  There's an
  example below.
* The error's properties might change.  People often hang additional properties
  off of Error objects.  If we wrap an existing Error in a new Error, those
  properties would be lost unless we copied them.  But there are a variety of
  both standard and non-standard Error properties that should _not_ be copied in
  this way: most obviously `name`, `message`, and `stack`, but also `fileName`,
  `lineNumber`, and a few others.  Plus, it's useful for some Error subclasses
  to have their own private properties -- and there'd be no way to know whether
  these should be copied.  For these reasons, VError first-classes these
  information properties.  You have to provide them in the constructor, you can
  only fetch them with the `info()` function, and VError takes care of making
  sure properties from causes wind up in the `info()` output.

Let's put this all together with an example from the node-fast RPC library.
node-fast implements a simple RPC protocol for Node programs.  There's a server
and client interface, and clients make RPC requests to servers.  Let's say the
server fails with an UnauthorizedError with message "user 'bob' is not
authorized".  The client wraps all server errors with a FastServerError.  The
client also wraps all request errors with a FastRequestError that includes the
name of the RPC call being made.  The result of this failed RPC might look like
this:

    name: FastRequestError
    message: "request failed: server error: user 'bob' is not authorized"
    rpcMsgid: <unique identifier for this request>
    rpcMethod: GetObject
    cause:
        name: FastServerError
        message: "server error: user 'bob' is not authorized"
        cause:
            name: UnauthorizedError
            message: "user 'bob' is not authorized"
            rpcUser: "bob"

When the caller uses `VError.info()`, the information properties are collapsed
so that it looks like this:

    message: "request failed: server error: user 'bob' is not authorized"
    rpcMsgid: <unique identifier for this request>
    rpcMethod: GetObject
    rpcUser: "bob"

Taking this apart:

* The error's message is a complete description of the problem.  The caller can
  report this directly to its caller, which can potentially make its way back to
  an end user (if appropriate).  It can also be logged.
* The caller can tell that the request failed on the server, rather than as a
  result of a client problem (e.g., failure to serialize the request), a
  transport problem (e.g., failure to connect to the server), or something else
  (e.g., a timeout).  They do this using `findCauseByName('FastServerError')`
  rather than checking the `name` field directly.
* If the caller logs this error, the logs can be analyzed to aggregate
  errors by cause, by RPC method name, by user, or whatever.  Or the
  error can be correlated with other events for the same rpcMsgid.
* It wasn't very hard for any part of the code to contribute to this Error.
  Each part of the stack has just a few lines to provide exactly what it knows,
  with very little boilerplate.

It's not expected that you'd use these complex forms all the time.  Despite
supporting the complex case above, you can still just do:

   new VError("my service isn't working");

for the simple cases.


## Examples

The "Demo" section above covers several basic cases.  Here's a more advanced
case:

```javascript
const err1 = new VError('something bad happened');
/* ... */
const err2 = new VError({
    'name': 'ConnectionError',
    'cause': err1,
    'info': {
        'errno': 'ECONNREFUSED',
        'remote_ip': '127.0.0.1',
        'port': 215
    }
}, 'failed to connect to "%s:%d"', '127.0.0.1', 215);

console.log(err2.message);
console.log(err2.name);
console.log(VError.info(err2));
console.log(err2.stack);
```

This outputs:

    failed to connect to "127.0.0.1:215": something bad happened
    ConnectionError
    { errno: 'ECONNREFUSED', remote_ip: '127.0.0.1', port: 215 }
    ConnectionError: failed to connect to "127.0.0.1:215": something bad happened
        at Object.<anonymous> (/home/dap/node-verror/examples/info.js:5:12)
        at Module._compile (module.js:456:26)
        at Object.Module._extensions..js (module.js:474:10)
        at Module.load (module.js:356:32)
        at Function.Module._load (module.js:312:12)
        at Function.Module.runMain (module.js:497:10)
        at startup (node.js:119:16)
        at node.js:935:3

Information properties are inherited up the cause chain, with values at the top
of the chain overriding same-named values lower in the chain.  To continue that
example:

```javascript
const err3 = new VError({
    'name': 'RequestError',
    'cause': err2,
    'info': {
        'errno': 'EBADREQUEST'
    }
}, 'request failed');

console.log(err3.message);
console.log(err3.name);
console.log(VError.info(err3));
console.log(err3.stack);
```

This outputs:

    request failed: failed to connect to "127.0.0.1:215": something bad happened
    RequestError
    { errno: 'EBADREQUEST', remote_ip: '127.0.0.1', port: 215 }
    RequestError: request failed: failed to connect to "127.0.0.1:215": something bad happened
        at Object.<anonymous> (/home/dap/node-verror/examples/info.js:20:12)
        at Module._compile (module.js:456:26)
        at Object.Module._extensions..js (module.js:474:10)
        at Module.load (module.js:356:32)
        at Function.Module._load (module.js:312:12)
        at Function.Module.runMain (module.js:497:10)
        at startup (node.js:119:16)
        at node.js:935:3

You can also print the complete stack trace of combined `Error`s by using
`VError.fullStack(err).`

```javascript
const err1 = new VError('something bad happened');
/* ... */
const err2 = new VError(err1, 'something really bad happened here');

console.log(VError.fullStack(err2));
```

This outputs:

    VError: something really bad happened here: something bad happened
        at Object.<anonymous> (/home/dap/node-verror/examples/fullStack.js:5:12)
        at Module._compile (module.js:409:26)
        at Object.Module._extensions..js (module.js:416:10)
        at Module.load (module.js:343:32)
        at Function.Module._load (module.js:300:12)
        at Function.Module.runMain (module.js:441:10)
        at startup (node.js:139:18)
        at node.js:968:3
    caused by: VError: something bad happened
        at Object.<anonymous> (/home/dap/node-verror/examples/fullStack.js:3:12)
        at Module._compile (module.js:409:26)
        at Object.Module._extensions..js (module.js:416:10)
        at Module.load (module.js:343:32)
        at Function.Module._load (module.js:300:12)
        at Function.Module.runMain (module.js:441:10)
        at startup (node.js:139:18)
        at node.js:968:3

`VError.fullStack` is also safe to use on regular `Error`s, so feel free to use
it whenever you need to extract the stack trace from an `Error`, regardless if
it's a `VError` or not.
