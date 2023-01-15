'use strict';

/*
 * verror.js: richer JavaScript errors
 */
const util = require('util');

const _ = require('lodash');
const assert = require('assert-plus');
const { sprintf } = require('extsprintf');

/*
 * Public interface
 */

/* So you can 'var VError = require('@netflix/nerror')' */
module.exports = VError;
/* For compatibility */
VError.VError = VError;
/* Other exported classes */
VError.PError = PError;
VError.SError = SError;
VError.WError = WError;
VError.MultiError = MultiError;

/**
 * Normalized forms, producing an object with the following properties.
 * @private
 * @typedef {Object} ParsedOptions parsed Options
 * @param {Object} options e- quivalent to "options" in third form. This will
 *              never
 *    			be a direct reference to what the caller passed in
 *    			(i.e., it may be a shallow copy), so it can be freely
 *    			modified.
 * @param {String} shortmessage - result of sprintf(sprintf_args), taking
 *              `options.strict` into account as described in README.md.
 */

/**
 * Common function used to parse constructor arguments for VError, WError, and
 * SError.  Named arguments to this function:
 *
 *     strict		force strict interpretation of sprintf arguments, even
 *     			if the options in "argv" don't say so
 *
 *     argv		error's constructor arguments, which are to be
 *     			interpreted as described in README.md.  For quick
 *     			reference, "argv" has one of the following forms:
 *
 *          [ sprintf_args... ]           (argv[0] is a string)
 *          [ cause, sprintf_args... ]    (argv[0] is an Error)
 *          [ options, sprintf_args... ]  (argv[0] is an object)
 *

 * @private
 * @param {Array} args - arguments
 * @returns {ParsedOptions} parsed options
 */
function parseConstructorArguments(args) {
    let options, sprintf_args, shortmessage, k;

    assert.object(args, 'args');
    assert.bool(args.strict, 'args.strict');
    assert.array(args.argv, 'args.argv');
    assert.optionalBool(args.skipPrintf, 'args.skipPrintf');
    const argv = args.argv;

    /*
     * First, figure out which form of invocation we've been given.
     */
    if (argv.length === 0) {
        options = {};
        sprintf_args = [];
    } else if (_.isError(argv[0])) {
        options = { cause: argv[0] };
        sprintf_args = argv.slice(1);
    } else if (typeof argv[0] === 'object') {
        options = {};
        // eslint-disable-next-line guard-for-in
        for (k in argv[0]) {
            options[k] = argv[0][k];
        }
        sprintf_args = argv.slice(1);
    } else {
        assert.string(
            argv[0],
            'first argument to VError, PError, SError, or WError ' +
                'constructor must be a string, object, or Error'
        );
        options = {};
        sprintf_args = argv;
    }

    // Preserve options
    if (args.skipPrintf) {
        options.skipPrintf = args.skipPrintf;
    }
    if (args.strict) {
        options.strict = args.strict;
    }

    /*
     * Now construct the error's message.
     *
     * extsprintf (which we invoke here with our caller's arguments in order
     * to construct this Error's message) is strict in its interpretation of
     * values to be processed by the "%s" specifier.  The value passed to
     * extsprintf must actually be a string or something convertible to a
     * String using .toString().  Passing other values (notably "null" and
     * "undefined") is considered a programmer error.  The assumption is
     * that if you actually want to print the string "null" or "undefined",
     * then that's easy to do that when you're calling extsprintf; on the
     * other hand, if you did NOT want that (i.e., there's actually a bug
     * where the program assumes some variable is non-null and tries to
     * print it, which might happen when constructing a packet or file in
     * some specific format), then it's better to stop immediately than
     * produce bogus output.
     *
     * However, sometimes the bug is only in the code calling VError, and a
     * programmer might prefer to have the error message contain "null" or
     * "undefined" rather than have the bug in the error path crash the
     * program (making the first bug harder to identify).  For that reason,
     * by default VError converts "null" or "undefined" arguments to their
     * string representations and passes those to extsprintf.  Programmers
     * desiring the strict behavior can use the SError class or pass the
     * "strict" option to the VError constructor.
     */
    assert.object(options);
    if (!options.skipPrintf && !options.strict && !args.strict) {
        sprintf_args = sprintf_args.map(function(a) {
            return a === null ? 'null' : a === undefined ? 'undefined' : a;
        });
    }

    if (sprintf_args.length === 0) {
        shortmessage = '';
    } else if (
        options.skipPrintf ||
        (sprintf_args.length === 1 && typeof sprintf_args[0] === 'string')
    ) {
        assert.equal(
            sprintf_args.length,
            1,
            'only one argument is allowed with options.skipPrintf'
        );
        shortmessage = sprintf_args[0];
    } else {
        shortmessage = sprintf.apply(null, sprintf_args);
    }

    return {
        options: options,
        shortmessage: shortmessage
    };
}

/**
 * @public
 * @typedef {Object} VErrorOptions Options
 * @param {String} name - Name of the error.
 * @param {Error} [cause] -  Indicates that the new error was caused by `cause`.
 * @param {Boolean} [strict=false] - If true, then `null` and `undefined` values
 *  in `sprintf_args` are passed through to `sprintf()`
 * @param {Function} [constructorOpt] -If specified, then the stack trace for
 *  this error ends at function `constructorOpt`.
 * @param {Object} [info]- Specifies arbitrary informational properties.
 * @param {Boolean} [skipPrintf=false] - If true, then `sprintf()` is not called
 */

/**
 *
 * About Constructor:
 * All of these forms construct a new VError that behaves just like the built-in
 * JavaScript `Error` class, with some additional methods described below.
 *
 * About Properties:
 * For all of these classes except `PError`, the printf-style arguments passed to
 * the constructor are processed with `sprintf()` to form a message.
 * For `WError`, this becomes the complete `message` property.  For `SError` and
 * `VError`, this message is prepended to the message of the cause, if any
 * (with a suitable separator), and the result becomes the `message` property.
 *
 * The `stack` property is managed entirely by the underlying JavaScript
 * implementation.  It's generally implemented using a getter function because
 * constructing the human-readable stack trace is somewhat expensive.
 *
 * @public
 * @class VError
 * @param {...String|VErrorOptions|Error} [arg] - sprintf args, options or cause
 * @param {...String} [args] - sprintf args
 * @property {String} name - Programmatically-usable name of the error.
 * @property {String} message - Human-readable summary of the failure.
 * Programmatically-accessible details are provided through `VError.info(err)`
 * class method.
 * @property {String} stack - Human-readable stack trace where the Error was
 * constructed.
 * @example
 * // This is the most general form.  You can specify any supported options
 * // including "cause" and "info") this way.</caption>
 * new VError(options, sprintf_args...)
 * @example
 * // This is a useful shorthand when the only option you need is "cause".
 * new VError(cause, sprintf_args...)
 * @example
 * // This is a useful shorthand when you don't need any options at all.
 * new VError(sprintf_args...)
 */
function VError(...args) {
    let obj, ctor, message, k;

    /*
     * This is a regrettable pattern, but JavaScript's built-in Error class
     * is defined to work this way, so we allow the constructor to be called
     * without "new".
     */
    if (!(this instanceof VError)) {
        obj = Object.create(VError.prototype);
        VError.apply(obj, arguments);
        return obj;
    }

    /*
     * For convenience and backwards compatibility, we support several
     * different calling forms.  Normalize them here.
     */
    const parsed = parseConstructorArguments({
        argv: args,
        strict: false
    });

    /*
     * If we've been given a name, apply it now.
     */
    if (parsed.options.name) {
        assert.string(parsed.options.name, 'error\'s "name" must be a string');
        this.name = parsed.options.name;
    }

    /*
     * For debugging, we keep track of the original short message (attached
     * this Error particularly) separately from the complete message (which
     * includes the messages of our cause chain).
     */
    this.jse_shortmsg = parsed.shortmessage;
    message = parsed.shortmessage;

    /*
     * If we've been given a cause, record a reference to it and update our
     * message appropriately.
     */
    const cause = parsed.options.cause;
    if (cause) {
        VError._assertError(cause, '"cause" must be an Error');
        this.jse_cause = cause;

        if (!parsed.options.skipCauseMessage) {
            message += ': ' + cause.message;
        }
    }

    /*
     * If we've been given an object with properties, shallow-copy that
     * here.  We don't want to use a deep copy in case there are non-plain
     * objects here, but we don't want to use the original object in case
     * the caller modifies it later.
     */
    this.jse_info = {};
    if (parsed.options.info) {
        // eslint-disable-next-line guard-for-in
        for (k in parsed.options.info) {
            this.jse_info[k] = parsed.options.info[k];
        }
    }

    this.message = message;
    Error.call(this, message);

    if (Error.captureStackTrace) {
        ctor = parsed.options.constructorOpt || this.constructor;
        Error.captureStackTrace(this, ctor);
    }

    return this;
}

util.inherits(VError, Error);
VError.prototype.name = 'VError';

/**
 * Appends any keys/fields to the existing jse_info. this can stomp over any
 * existing fields.
 * @public
 * @memberof VError.prototype
 * @param {Object} obj source obj to assign fields from
 * @return {Object} new info object
 */
VError.prototype.assignInfo = function ve_assignInfo(obj) {
    assert.optionalObject(obj, 'obj');
    return Object.assign(this.jse_info, obj);
};

/**
 * Instance level convenience method vs using the static methods on VError.
 * @public
 * @memberof VError.prototype
 * @return {Object} info object
 */
VError.prototype.info = function ve_info() {
    return VError.info(this);
};

/**
 * A string representing the VError.
 * @public
 * @memberof VError.prototype
 * @return {String} string representation
 */
VError.prototype.toString = function ve_toString() {
    let str =
        (this.hasOwnProperty('name') && this.name) ||
        this.constructor.name ||
        this.constructor.prototype.name;
    if (this.message) {
        str += ': ' + this.message;
    }

    return str;
};

/**
 * This method is provided for compatibility.  New callers should use
 * VError.cause() instead.  That method also uses the saner `null` return value
 * when there is no cause.
 * @public
 * @memberof VError.prototype
 * @return {undefined|Error} Error cause if any
 */
VError.prototype.cause = function ve_cause() {
    const cause = VError.cause(this);
    return cause === null ? undefined : cause;
};

/*
 * Static methods
 *
 * These class-level methods are provided so that callers can use them on
 * instances of Errors that are not VErrors.  New interfaces should be provided
 * only using static methods to eliminate the class of programming mistake where
 * people fail to check whether the Error object has the corresponding methods.
 */

/**
 * @private
 * @static
 * @memberof VError
 * @param {Error} err - error to assert
 * @param {String} [msg] - optional message
 * @returns {undefined} no return value
 * @throws AssertationError - when input is not an error
 */
VError._assertError = function _assertError(err, msg) {
    assert.optionalString(msg, 'msg');
    const _msg = (msg || 'err must be an Error') + ` but got ${String(err)}`;
    assert.ok(_.isError(err), _msg);
};

/**
 * Checks if an error is a VError or VError sub-class.
 *
 * @public
 * @static
 * @memberof VError
 * @param {Error} err - error
 * @return {Boolean} is a VError or VError sub-class
 */
VError.isVError = function assignInfo(err) {
    // We are checking on internals here instead of using
    // `err instanceof VError` to being compatible with the original VError lib.
    return err && err.hasOwnProperty('jse_info');
};

/**
 * Appends any keys/fields to the `jse_info`. This can stomp over any existing
 * fields.
 *
 * Note: This method is static because in this way we don't need to check on
 * VError versions to be sure `assignInfo` method is supported.
 *
 * @public
 * @static
 * @memberof VError
 * @param {Error} err - error
 * @param {Object} obj - source obj to assign fields from
 * @return {Object} new info object
 */
VError.assignInfo = function assignInfo(err, obj) {
    VError._assertError(err);
    assert.optionalObject(obj, 'obj');

    if (!VError.isVError(err)) {
        throw new TypeError('err must be an instance of VError');
    }

    return Object.assign(err.jse_info, obj);
};

/**
 * Returns the next Error in the cause chain for `err`, or `null` if there is no
 * next error.  See the `cause` argument to the constructor.
 * Errors can have arbitrarily long cause chains.  You can walk the `cause`
 * chain by invoking `VError.cause(err)` on each subsequent return value.
 * If `err` is not a `VError`, the cause is `null`.
 *
 * @public
 * @static
 * @memberof VError
 * @param {VError} err - error
 * @return {undefined|Error} Error cause if any
 */
VError.cause = function cause(err) {
    VError._assertError(err);
    return _.isError(err.jse_cause) ? err.jse_cause : null;
};

/**
 * Returns an object with all of the extra error information that's been
 * associated with this Error and all of its causes. These are the properties
 * passed in using the `info` option to the constructor. Properties not
 * specified in the constructor for this Error are implicitly inherited from
 * this error's cause.
 *
 * These properties are intended to provide programmatically-accessible metadata
 * about the error.  For an error that indicates a failure to resolve a DNS
 * name, informational properties might include the DNS name to be resolved, or
 * even the list of resolvers used to resolve it.  The values of these
 * properties should generally be plain objects (i.e., consisting only of null,
 * undefined, numbers, booleans, strings, and objects and arrays containing only
 * other plain objects).
 *
 * @public
 * @static
 * @memberof VError
 * @param {VError} err - error
 * @return {Object} info object
 */
VError.info = function info(err) {
    let rv, k;

    VError._assertError(err);
    const cause = VError.cause(err);
    if (cause !== null) {
        rv = VError.info(cause);
    } else {
        rv = {};
    }

    if (typeof err.jse_info === 'object' && err.jse_info !== null) {
        // eslint-disable-next-line guard-for-in
        for (k in err.jse_info) {
            rv[k] = err.jse_info[k];
        }
    }

    return rv;
};

/**
 * The `findCauseByName()` function traverses the cause chain for `err`, looking
 * for an error whose `name` property matches the passed in `name` value. If no
 * match is found, `null` is returned.
 *
 * If all you want is to know _whether_ there's a cause (and you don't care what
 * it is), you can use `VError.hasCauseWithName(err, name)`.
 *
 * If a vanilla error or a non-VError error is passed in, then there is no cause
 * chain to traverse. In this scenario, the function will check the `name`
 * property of only `err`.
 *
 * @public
 * @static
 * @memberof VError
 * @param {VError} err - error
 * @param {String} name - name of cause Error
 * @return {null|Error} cause if any
 */
VError.findCauseByName = function findCauseByName(err, name) {
    let cause;

    VError._assertError(err);
    assert.string(name, 'name');
    assert.ok(name.length > 0, 'name cannot be empty');

    for (cause = err; cause !== null; cause = VError.cause(cause)) {
        assert.ok(_.isError(cause));
        if (cause.name === name) {
            return cause;
        }
    }

    return null;
};

/**
 * Returns true if and only if `VError.findCauseByName(err, name)` would return
 * a non-null value.  This essentially determines whether `err` has any cause in
 * its cause chain that has name `name`.
 *
 * @public
 * @static
 * @memberof VError
 * @param {VError} err - error
 * @param {String} name - name of cause Error
 * @return {Boolean} has cause
 */
VError.hasCauseWithName = function hasCauseWithName(err, name) {
    return VError.findCauseByName(err, name) !== null;
};

/**
 * Returns a string containing the full stack trace, with all nested errors
 * recursively reported as `'caused by:' + err.stack`.
 *
 * @public
 * @static
 * @memberof VError
 * @param {VError} err - error
 * @return {String} full stack trace
 */
VError.fullStack = function fullStack(err) {
    VError._assertError(err);

    const cause = VError.cause(err);

    if (cause) {
        return err.stack + '\ncaused by: ' + VError.fullStack(cause);
    }

    return err.stack;
};

/**
 * Given an array of Error objects (possibly empty), return a single error
 * representing the whole collection of errors. If the list has:
 *
 * * 0 elements, returns `null`
 * * 1 element, returns the sole error
 * * more than 1 element, returns a MultiError referencing the whole list
 *
 * This is useful for cases where an operation may produce any number of errors,
 * and you ultimately want to implement the usual `callback(err)` pattern.
 * You can accumulate the errors in an array and then invoke
 * `callback(VError.errorFromList(errors))` when the operation is complete.
 *
 * @public
 * @static
 * @memberof VError
 * @param {Array<Error>} errors - errors
 * @return {null|Error|MultiError} single or multi error if any
 */
VError.errorFromList = function errorFromList(errors) {
    assert.arrayOfObject(errors, 'errors');

    if (errors.length === 0) {
        return null;
    }

    errors.forEach(function(e) {
        assert.ok(_.isError(e), 'all errors must be an Error');
    });

    if (errors.length === 1) {
        return errors[0];
    }

    return new MultiError(errors);
};

/**
 * Convenience function for iterating an error that may itself be a MultiError.
 *
 * In all cases, `err` must be an Error.  If `err` is a MultiError, then `func`
 * is invoked as `func(errorN)` for each of the underlying errors of the
 * MultiError.
 * If `err` is any other kind of error, `func` is invoked once as `func(err)`.
 * In all cases, `func` is invoked synchronously.
 *
 * This is useful for cases where an operation may produce any number of
 * warnings that may be encapsulated with a MultiError -- but may not be.
 *
 * This function does not iterate an error's cause chain.
 *
 * @public
 * @static
 * @memberof VError
 * @param {Error} err - error
 * @param {Function} func - iterator
 * @return {undefined} no return value
 */
VError.errorForEach = function errorForEach(err, func) {
    VError._assertError(err);
    assert.func(func, 'func');

    if (err.name === 'MultiError') {
        err.errors().forEach(function iterError(e) {
            func(e);
        });
    } else {
        func(err);
    }
};

/**
 * PError is like VError, but the message is not run through printf-style
 * templating.
 *
 * @public
 * @class PError
 * @extends VError
 * @param {...String|VErrorOptions|Error} [arg] - sprintf args, options or cause
 * @param {...String} [args] - sprintf args
 */
function PError(...args) {
    let obj;

    if (!(this instanceof PError)) {
        obj = Object.create(PError.prototype);
        PError.apply(obj, args);
        return obj;
    }

    const parsed = parseConstructorArguments({
        argv: args,
        strict: false,
        skipPrintf: true
    });

    VError.call(this, parsed.options, parsed.shortmessage);

    return this;
}

util.inherits(PError, VError);
PError.prototype.name = 'PError';

/**
 * SError is like VError, but stricter about types. You cannot pass "null" or
 * "undefined" as string arguments to the formatter.
 *
 * @public
 * @class SError
 * @extends VError
 * @param {...String|VErrorOptions|Error} [arg] - sprintf args, options or cause
 * @param {...String} [args] - sprintf args
 */
function SError(...args) {
    let obj;

    if (!(this instanceof SError)) {
        obj = Object.create(SError.prototype);
        SError.apply(obj, arguments);
        return obj;
    }

    const parsed = parseConstructorArguments({
        argv: args,
        strict: true
    });

    const options = parsed.options;
    options.skipPrintf = false;
    VError.call(this, options, '%s', parsed.shortmessage);

    return this;
}

/*
 * We don't bother setting SError.prototype.name because once constructed,
 * SErrors are just like VErrors.
 */
util.inherits(SError, VError);

/**
 * Represents a collection of errors for the purpose of consumers that generally
 * only deal with one error.  Callers can extract the individual errors
 * contained in this object, but may also just treat it as a normal single
 * error, in which case a summary message will be printed.
 *
 * @public
 * @class MultiError
 * @extends VError
 * @param {Array<Error>} errors - errors
 * @example
 * // `error_list` is an array of at least one `Error` object.
 * new MultiError(error_list)
 *
 * // The cause of the MultiError is the first error provided.  None of the
 * // other `VError` options are supported.  The `message` for a MultiError
 * // consists the `message` from the first error, prepended with a message
 * // indicating that there were other errors.
 *
 * //For example:
 * err = new MultiError([
 *     new Error('failed to resolve DNS name "abc.example.com"'),
 *     new Error('failed to resolve DNS name "def.example.com"'),
 * ]);
 * console.error(err.message);
 *
 * // outputs:
 * //   first of 2 errors: failed to resolve DNS name "abc.example.com"
 */
function MultiError(errors) {
    assert.array(errors, 'list of errors');
    assert.ok(errors.length > 0, 'must be at least one error');
    this.ase_errors = errors;

    VError.call(
        this,
        {
            cause: errors[0]
        },
        'first of %d error%s',
        errors.length,
        errors.length === 1 ? '' : 's'
    );
}

util.inherits(MultiError, VError);
MultiError.prototype.name = 'MultiError';

/**
 * Returns an array of the errors used to construct this MultiError.
 *
 * @public
 * @memberof MultiError.prototype
 * @returns {Array<Error>} errors
 */
MultiError.prototype.errors = function me_errors() {
    return this.ase_errors.slice(0);
};

/**
 * WError for wrapping errors while hiding the lower-level messages from the
 * top-level error.  This is useful for API endpoints where you don't want to
 * expose internal error messages, but you still want to preserve the error
 * chain for logging and debugging
 *
 * @public
 * @class WError
 * @extends VError
 * @param {...String|VErrorOptions|Error} [arg] - sprintf args, options or cause
 * @param {...String} [args] - sprintf args
 */
function WError(...args) {
    let obj;

    if (!(this instanceof WError)) {
        obj = Object.create(WError.prototype);
        WError.apply(obj, args);
        return obj;
    }

    const parsed = parseConstructorArguments({
        argv: args,
        strict: false
    });

    const options = parsed.options;
    options.skipCauseMessage = true;
    options.skipPrintf = false;
    VError.call(this, options, '%s', parsed.shortmessage);

    return this;
}

util.inherits(WError, VError);
WError.prototype.name = 'WError';

/**
 * A string representing the WError.
 * @public
 * @memberof WError.prototype
 * @return {String} string representation
 */
WError.prototype.toString = function we_toString() {
    let str =
        (this.hasOwnProperty('name') && this.name) ||
        this.constructor.name ||
        this.constructor.prototype.name;
    if (this.message) {
        str += ': ' + this.message;
    }
    if (this.jse_cause && this.jse_cause.message) {
        str += '; caused by ' + this.jse_cause.toString();
    }

    return str;
};

/**
 * For purely historical reasons, WError's cause() function allows you to set
 * the cause.
 * @public
 * @memberof WError.prototype
 * @param {Error} c - cause
 * @return {undefined|Error} Error cause
 */
WError.prototype.cause = function we_cause(c) {
    if (_.isError(c)) {
        this.jse_cause = c;
    }

    return this.jse_cause;
};
