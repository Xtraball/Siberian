/**
 * The module update screen of the back-office, driven in jsdom with the real angular.min.js, the real Installer
 * factory and the real ModuleController (design/desktop/backoffice/js/controllers/installer.js).
 *
 * The server answers an error of these endpoints with HTTP 400 and the JSON payload ({error, code, message, terms}),
 * see Core_Controller_Default_Abstract::_sendJson. The controller has to treat that answer, not only a 200 one:
 * "Please enter the licence key of X." must open the licence prompt, not be shown as an error banner.
 *
 *   npm install jsdom            (once, anywhere; pass NODE_PATH or run it from that directory)
 *   node tests/php-upgrade/tooling/module_updates_prompt.test.js [path of installer.js to test]
 *
 * Exit code 1 when a check fails. Not run by tests/php-upgrade/run.php (no node in the CI image): the regression
 * test of the same name checks the source, this one proves the behaviour.
 */
const fs = require('fs');
const path = require('path');
const {JSDOM} = require('jsdom');

const root = path.resolve(__dirname, '../../../siberian/app/sae/design/desktop/backoffice/js');
const controllerFile = process.argv[2] || path.join(root, 'controllers/installer.js');

let failures = 0;
const check = (ok, label) => {
    console.log((ok ? 'ok   ' : 'FAIL ') + label);
    if (!ok) {
        failures++;
    }
};
// the fake backend answers on a timer; a digest sends the next request and applies the answer, so alternate the two
const pump = async (page, rounds) => { for (let i = 0; i < (rounds || 8); i++) { page.$rootScope.$digest(); await new Promise((resolve) => setTimeout(resolve, 5)); } };

/**
 * One page with the real framework and controller; `answer(method, url, body)` plays the server.
 */
function boot(answer, options) {
    options = options || {};
    const dom = new JSDOM('<!doctype html><html><body></body></html>', {runScripts: 'outside-only', pretendToBeVisual: true});
    const w = dom.window;
    const run = (file) => w.eval(fs.readFileSync(file, 'utf8'));

    run(path.join(root, 'angular.min.js'));
    w.BASE_URL = '';
    w.swalCalls = [];
    if (options.realSwal) {
        run(path.join(root, 'sweetalert.min.js')); // the real dialogs, to see what the administrator would see
    } else {
        w.swal = function (opts, callback) {
            w.swalCalls.push({options: opts, callback});
        };
        w.swal.close = function () {};
        w.swal.showInputError = function () {};
    }
    w.requests = [];

    w.eval(`
        var App = angular.module('App', []);
        // the route provider of ngRoute is not needed to build the controller
        App.provider('$route', function () { this.when = function () { return this; }; this.$get = function () { return {}; }; });
        App.config(function ($provide) {
            $provide.decorator('$httpBackend', function () {
                return function (method, url, post, callback) {
                    window.requests.push({method: method, url: url, post: post});
                    var r = window.answer(method, url, post);
                    setTimeout(function () { callback(r.status, typeof r.body === 'string' ? r.body : JSON.stringify(r.body), 'content-type: application/json', ''); }, 0);
                };
            });
        });
        App.factory('Url', function () { return {get: function (p, q) { return '/' + p + (q ? '?' + $.param(q) : ''); }}; });
        App.factory('Label', function () { return {uploader: {error: {general: 'General error', only_one_at_a_time: 'One', type: {zip: 'Zip'}}}}; });
        App.factory('Backoffice', function () { return {clearCache: function () { return {success: function () {}}; }}; });
        App.factory('AdvancedTools', function () { return {}; });
        App.factory('Header', function () { return function () { this.button = {left: {}}; }; });
        App.factory('FileUploader', function () { return function () { this.filters = []; this.queue = []; }; });
    `);
    w.$ = {param: (o) => Object.keys(o).map((k) => k + '=' + o[k]).join('&')};
    // the screen loads its words first (ModuleController asks installer/backoffice_module/load)
    w.answer = (method, url, post) => url.indexOf('backoffice_module/load') !== -1 ?
        {status: 200, body: {success: true, words: {confirmLicense: 'Save', cancelLicense: 'Cancel', emptyLicense: 'Empty key'}, ini: {max_size: 1e9}}} :
        answer(method, url, post);
    run(path.join(root, 'factories/installer.js'));
    w.eval(fs.readFileSync(controllerFile, 'utf8'));

    const injector = w.angular.injector(['ng', 'App']);
    const $rootScope = injector.get('$rootScope');
    const $scope = $rootScope.$new();
    $scope.message = {
        shown: [],
        setText(text) { this.text = text; return this; },
        isError(value) { this.error = value; return this; },
        show() { this.shown.push({text: this.text, error: this.error}); return this; }
    };
    injector.get('$controller')('ModuleController', {$scope});

    return {w, $scope, $rootScope, injector};
}

const updatesList = {
    success: true, updates: [{code: 'phonebook', name: 'Phonebook', installed: '1.0.0', available: '1.1.0', license_missing: true, compatible: true}],
    stale: false, fetched_at: 1, terms_accepted: true, terms_hash: 'h'
};

(async function () {
    // 1. no saved key: the server answers 400 license_required, the prompt opens (the reported bug)
    {
        const answers = [];
        const page = boot((method, url, post) => {
            if (url.indexOf('checkupdates') !== -1) {
                return {status: 200, body: updatesList};
            }
            if (url.indexOf('updatemodule') !== -1) {
                answers.push(post);
                if (answers.length === 1) {
                    return {status: 400, body: {error: true, code: 'license_required', message: 'Please enter the licence key of Phonebook.'}};
                }
                return {status: 200, body: {success: true, filename: 'phonebook.zip', package_details: {name: 'Phonebook', version: '1.1.0'}}};
            }
            return {status: 200, body: {}};
        });
        const s = page.$scope;
        page.w.swalCalls.length = 0;
        s.showPackageDetails = function () { s.shown = true; };
        await pump(page); // the list loads
        check(s.module_updates.updates.length === 1, 'the update list loads');
        s.module_updates.terms_accepted = true;

        s.updateModule(s.module_updates.updates[0], '');
        await pump(page);
        const prompt = page.w.swalCalls[0];
        check(!!prompt && prompt.options.type === 'prompt', '400 license_required opens the licence prompt');
        check(!!prompt && prompt.options.title === 'Phonebook' && prompt.options.text === 'Please enter the licence key of Phonebook.', 'the prompt names the module and carries the message');
        check(s.message.shown.length === 0, 'and no error banner is shown');
        check(s.module_updates.busy_code === null && s.check_for_updates.loader_is_visible === false, 'the loader is released');

        // the administrator types a key: the update is retried with it in the POST body
        if (prompt) {
            prompt.callback('ABCD-1234-EFGH');
            await pump(page);
        }
        check(answers.length === 2 && String(answers[1]).indexOf('license=ABCD-1234-EFGH') !== -1, 'the key typed is sent with the retry');
        check(s.shown === true && s.check_for_updates.check === false, 'the regular installer takes over once the package arrives');
    }

    // 2. a refused key: 400 module_license opens the prompt again
    {
        const page = boot((method, url) => url.indexOf('checkupdates') !== -1 ? {status: 200, body: updatesList}
            : {status: 400, body: {error: true, code: 'module_license', message: 'This licence key is not valid.'}});
        const s = page.$scope;
        await pump(page);
        s.module_updates.terms_accepted = true;
        page.w.swalCalls.length = 0;
        s.updateModule(s.module_updates.updates[0], 'WRONG-KEY-123');
        await pump(page);
        check(page.w.swalCalls.length === 1 && page.w.swalCalls[0].options.text === 'This licence key is not valid.', '400 module_license asks for another key');
    }

    // 3. terms changed meanwhile: 400 with the terms opens the terms
    {
        const page = boot((method, url) => url.indexOf('checkupdates') !== -1 ? {status: 200, body: updatesList}
            : {status: 400, body: {error: true, code: 'terms_changed', message: 'Changed', terms: {hash: 'new', version: '2', html: '<p>terms</p>'}}});
        const s = page.$scope;
        await pump(page);
        s.module_updates.terms_accepted = true;
        page.w.swalCalls.length = 0;
        s.updateModule(s.module_updates.updates[0], '');
        await pump(page);
        check(s.module_updates.terms_accepted === false && page.w.swalCalls.length === 1 && /Terms of download \(v2\)/.test(page.w.swalCalls[0].options.title), '400 with terms shows the new terms');
    }

    // 4. terms never accepted: the terms are fetched (400 or 200), then the update goes on
    {
        const page = boot((method, url) => url.indexOf('checkupdates') !== -1 ? {status: 200, body: Object.assign({}, updatesList, {terms_accepted: false})}
            : url.indexOf('/terms') !== -1 ? {status: 200, body: {success: true, terms: {hash: 'h', version: '1', html: '<p>t</p>'}}} : {status: 200, body: {}});
        const s = page.$scope;
        await pump(page);
        page.w.swalCalls.length = 0;
        s.updateModule(s.module_updates.updates[0], '');
        await pump(page);
        check(page.w.swalCalls.length === 1 && /Terms of download/.test(page.w.swalCalls[0].options.title), 'terms not accepted yet: they are shown');
    }
    {
        const page = boot((method, url) => url.indexOf('checkupdates') !== -1 ? {status: 200, body: Object.assign({}, updatesList, {terms_accepted: false})}
            : {status: 400, body: {error: true, code: 'unavailable', message: 'The terms of download are not reachable, please try again later.'}});
        const s = page.$scope;
        await pump(page);
        s.updateModule(s.module_updates.updates[0], '');
        await pump(page);
        check(s.message.shown.length === 1 && /not reachable/.test(s.message.shown[0].text) && s.message.shown[0].error === true, 'terms unreachable (400): the message is shown, not swallowed');
    }

    // 5. other errors keep showing the banner, with the server message
    {
        const page = boot((method, url) => url.indexOf('checkupdates') !== -1 ? {status: 200, body: updatesList}
            : {status: 400, body: {error: true, code: 'siberian_license', message: 'Your Siberian licence was refused.'}});
        const s = page.$scope;
        await pump(page);
        s.module_updates.terms_accepted = true;
        page.w.swalCalls.length = 0;
        s.updateModule(s.module_updates.updates[0], '');
        await pump(page);
        check(s.message.shown.length === 1 && s.message.shown[0].text === 'Your Siberian licence was refused.' && page.w.swalCalls.length === 0, 'another error (400) is shown as a banner with its message');
    }

    // 6. an answer that is not the payload (proxy page, 502): the general message
    {
        const page = boot((method, url) => url.indexOf('checkupdates') !== -1 ? {status: 200, body: updatesList}
            : {status: 502, body: '<html>Bad gateway</html>'});
        const s = page.$scope;
        await pump(page);
        s.module_updates.terms_accepted = true;
        s.updateModule(s.module_updates.updates[0], '');
        await pump(page);
        check(s.message.shown.length === 1 && s.message.shown[0].text === 'General error', 'a 502 page gives the general message');
    }

    // 7. the list itself: a 400 from checkupdates shows the server message in the screen
    {
        const page = boot(() => ({status: 400, body: {error: true, code: 'disabled', message: 'Module updates are disabled.'}}));
        await pump(page);
        check(page.$scope.module_updates.error === 'Module updates are disabled.' && page.$scope.module_updates.loading === false, 'the list shows the server message when it is refused (400)');
    }

    // 8. the whole first update, with the real sweetalert: terms, agree, then the licence prompt
    {
        const answers = [];
        const page = boot((method, url, post) => {
            if (url.indexOf('checkupdates') !== -1) {
                return {status: 200, body: Object.assign({}, updatesList, {terms_accepted: false})};
            }
            if (url.indexOf('acceptterms') !== -1) {
                return {status: 200, body: {success: true}};
            }
            if (url.indexOf('backoffice_module/terms') !== -1) {
                return {status: 200, body: {success: true, terms: {hash: 'h', version: '1', html: '<p>The terms</p>'}}};
            }
            if (url.indexOf('updatemodule') !== -1) {
                answers.push(post);
                return answers.length === 1 ?
                    {status: 400, body: {error: true, code: 'license_required', message: 'Please enter the licence key of Phonebook.'}} :
                    {status: 200, body: {success: true, filename: 'phonebook.zip', package_details: {name: 'Phonebook', version: '1.1.0'}}};
            }
            return {status: 200, body: {}};
        }, {realSwal: true});
        const s = page.$scope;
        const doc = page.w.document;
        const dialog = () => doc.querySelector('.sweet-alert.showSweetAlert');
        const field = () => {
            const d = dialog();
            const input = d && d.querySelector('input[type=text]');
            return input && input.parentNode.style.display !== 'none' ? input : null;
        };
        s.showPackageDetails = function () { s.shown = true; };
        await pump(page);
        s.updateModule(s.module_updates.updates[0], '');
        await pump(page, 20);
        check(!!dialog() && /Terms of download/.test(dialog().querySelector('h2').textContent), 'real swal: the terms are shown first');
        await pump(page, 140); // sweetalert ignores clicks until the dialog has been open for 500 ms
        dialog().querySelector('button.confirm').click();
        await pump(page, 120); // the dialog closes (300 ms), the update is asked again, the server wants the key
        check(!!dialog() && dialog().querySelector('h2').textContent === 'Phonebook', 'real swal: the Phonebook dialog is on screen after the terms');
        check(!!field(), 'real swal: it has the input for the licence key');
        check(/Please enter the licence key of Phonebook\./.test(dialog() ? dialog().querySelector('p').textContent : ''), 'real swal: it carries the message');
        if (field()) {
            await pump(page, 140);
            field().value = 'KEY-ABC-12345';
            dialog().querySelector('button.confirm').click();
            await pump(page, 60);
        }
        check(answers.length === 2 && String(answers[1]).indexOf('license=KEY-ABC-12345') !== -1 && s.shown === true, 'real swal: the key typed is sent and the package arrives');
        check(s.message.shown.length === 0, 'real swal: no error banner at any point');
    }

    console.log(failures ? failures + ' check(s) failed' : 'all checks passed');
    process.exit(failures ? 1 : 0);
})();
