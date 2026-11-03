// Drives the real dashboard controller (design/desktop/backoffice/js/controllers/homepage.js) and the real card
// "Errors on app calls" of the dashboard template (PHP echoes resolved) in jsdom, and checks the console stays clean.
// Only the services behind the controller (HTTP factories) are stubbed.
//
//   NODE_PATH=<dir with jsdom> node tests/jsdom/dashboard-app-errors.js
const fs = require('fs');
const path = require('path');
const { JSDOM, VirtualConsole } = require('jsdom');

const base = path.resolve(__dirname, '../../siberian/app/sae/design/desktop/backoffice');
const read = (p) => fs.readFileSync(path.join(base, p), 'utf8');

// The card, from its <div> to the line that says the reports are off.
const full = read('template/backoffice/index/index.phtml');
const marker = full.indexOf('id="app-errors"');
const start = full.lastIndexOf('<div', marker);
const endMarker = full.indexOf('appErrors.enabled === false', marker);
const end = full.indexOf('</p>', endMarker) + 4;
if (marker < 0 || endMarker < 0) {
    console.log('FAIL the card is not in the dashboard template');
    process.exit(1);
}
const card = full.slice(start, end)
    .replace(/<\?php echo p__js\('backoffice', '([^']*)', "'"\); \?>/g, '$1')
    .replace(/<\?php echo p__\('backoffice', '([^']*)'(?:, [^;]*?)?\); \?>/g, (m, text) => text.replace('%s', '5'))
    .replace(/<\?php[\s\S]*?\?>/g, '');

const consoleErrors = [];
const virtualConsole = new VirtualConsole();
virtualConsole.on('jsdomError', (e) => consoleErrors.push('jsdomError: ' + (e.detail || e.message)));
virtualConsole.on('error', (...x) => consoleErrors.push('console.error: ' + x.join(' ')));

const dom = new JSDOM('<!doctype html><body><div ng-app="T"><div ng-controller="HomepageController" id="root">' +
    card + '</div></div></body>', { runScripts: 'outside-only', pretendToBeVisual: true, virtualConsole });
const w = dom.window;
w.addEventListener('error', (e) => consoleErrors.push('window error: ' + e.message));

w.eval(read('js/angular.min.js'));
w.eval(read('js/angular/angular-route.min.js'));
w.eval('var BASE_URL = "";');
w.eval(`
  var legacy = function (p) { p.success = function (f) { p.then(function (r) { f(r.data !== undefined ? r.data : r); }); return p; };
      p.error = function (f) { p.then(null, function (r) { f(r && r.data !== undefined ? r.data : r); }); return p; };
      var fin = p.finally; p.finally = function (f) { return legacy(fin.call(p, f)); }; return p; };
  window.__pending = {};
  window.__calls = [];
  window.__confirm = true;
  window.confirm = function () { return window.__confirm; };
  // every service method parks a deferred under its name, the test settles the ones it cares about
  var parked = function ($q, service) {
      return new Proxy({}, {get: function (t, name) {
          return function () {
              window.__calls.push([service, String(name), Array.prototype.slice.call(arguments)]);
              var d = $q.defer();
              (window.__pending[name] = window.__pending[name] || []).push(d);
              return legacy(d.promise);
          };
      }});
  };
  var App = angular.module('T', ['ngRoute'])
   .factory('Header', function () { return function () { this.button = {left: {}}; }; })
   .factory('Backoffice', function ($q) { return parked($q, 'Backoffice'); })
   .factory('Advanced', function ($q) { return parked($q, 'Advanced'); })
   .run(function ($rootScope) { $rootScope.message = {setText: function (t) { window.__last = {text: t}; return this; }, isError: function (e) { window.__last.error = e; return this; }, show: function () { window.__last.shown = true; return this; }}; });
`);
w.eval(read('js/controllers/homepage.js').replace(/^App\./m, 'angular.module("T").'));

const results = [];
const check = (name, ok, extra) => { results.push([name, !!ok, extra]); };
const $ = (s) => w.document.querySelector(s);
const $$ = (s) => Array.from(w.document.querySelectorAll(s));
const visible = (s) => { const e = $(s); return !!e && !e.classList.contains('ng-hide'); };
let scope;
const tick = () => new Promise((r) => setTimeout(() => { scope.$digest(); setTimeout(r, 30); }, 10));
const settle = async (name, kind, value) => {
    const d = w.__pending[name].shift();
    scope.$apply(() => (kind === 'ok' ? d.resolve({ data: value }) : d.reject({ data: value })));
    await tick();
};

process.on('uncaughtException', (e) => {
    results.forEach(([n, ok, x]) => console.log((ok ? 'ok   ' : 'FAIL ') + n + (ok ? '' : ' -> ' + x)));
    console.log('ABORTED: ' + e.stack.split('\n').slice(0, 3).join(' '));
    process.exit(1);
});

const modules = [
    { name: 'Xdelivery', fatals: 7, errors: 2, last: 1900000000, endpoints: 3, message: 'Call to undefined method', recent_fatals: 6, warning: true, quarantined: false },
    { name: 'Chatty', fatals: 0, errors: 4, last: 1899990000, endpoints: 1, message: 'Undefined index', recent_fatals: 0, warning: false, quarantined: false },
];

w.angular.element(w.document).ready(async () => {
    scope = w.angular.element($('#root')).scope();
    await tick();

    check('the page asks for the errors on app calls', w.__calls.some((c) => c[0] === 'Advanced' && c[1] === 'appErrors'));
    check('nothing is shown before the answer', !visible('#app-errors') || $('#app-errors') === null);

    await settle('appErrors', 'ok', { enabled: true, since: 1, threshold: 5, window: 600, modules });
    check('the card is shown', visible('#app-errors'));
    check('one row per module', $$('#app-errors tr[ng-repeat]').length === 2);
    check('a warning turns the card red', $('#app-errors').classList.contains('alert-danger'));
    check('the warning names the threshold', /5 fatal errors or more/.test($('#app-errors').textContent));
    check('the module with the warning is flagged', /Warning/.test($$('#app-errors tr[ng-repeat]')[0].textContent));
    check('the calm module is not flagged', !/Warning/.test($$('#app-errors tr[ng-repeat]')[1].textContent));
    check('one Quarantine now button per module', $$('#app-errors button').length === 2);
    check('the module name and message are there', /Xdelivery/.test($('#app-errors').textContent) && /Call to undefined method/.test($('#app-errors').textContent));

    // Cancelled confirmation: nothing is sent
    w.__confirm = false;
    $$('#app-errors button')[0].click();
    await tick();
    check('a cancelled confirmation sends nothing', !w.__calls.some((c) => c[1] === 'quarantineNow'));

    // Confirmed: the module goes to the factory, the busy flag holds the buttons
    w.__confirm = true;
    $$('#app-errors button')[0].click();
    await tick();
    const sent = w.__calls.filter((c) => c[1] === 'quarantineNow');
    check('Quarantine now sends the module', sent.length === 1 && sent[0][2][0] === 'Xdelivery');
    check('the buttons wait for the answer', $$('#app-errors button').every((b) => b.disabled));

    await settle('quarantineNow', 'ok', {
        success: true,
        message: 'The module is in quarantine.',
        summary: { quarantined: 1, total: 3, items: [] },
        app_errors: { enabled: true, threshold: 5, window: 600, modules: [Object.assign({}, modules[0], { quarantined: true }), modules[1]] },
    });
    check('the answer is shown as a message', w.__last && w.__last.text === 'The module is in quarantine.' && !w.__last.error);
    check('the module says it is in quarantine', /In quarantine/.test($$('#app-errors tr[ng-repeat]')[0].textContent));
    check('its button is gone, the other stays', $$('#app-errors button').length === 1);
    check('a quarantined module no longer turns the card red', !$('#app-errors').classList.contains('alert-danger'));
    check('the buttons work again', $$('#app-errors button').every((b) => !b.disabled));

    // A failure is an error message
    $$('#app-errors button')[0].click();
    await tick();
    await settle('quarantineNow', 'err', { error: true, message: 'The quarantine is switched off.' });
    check('a failure is shown as an error', w.__last.text === 'The quarantine is switched off.' && w.__last.error === true);

    // Reports off: the card goes, a line says why
    scope.$apply(() => { scope.appErrors = { enabled: false, modules: [] }; });
    await tick();
    check('reports off: no card', !visible('#app-errors') || $('#app-errors') === null);
    check('reports off: a line says nothing is recorded', /not recorded/.test(w.document.body.textContent));

    check('the console stays clean', consoleErrors.length === 0, consoleErrors.join(' | '));

    results.forEach(([n, ok, x]) => console.log((ok ? 'ok   ' : 'FAIL ') + n + (ok ? '' : ' -> ' + x)));
    process.exit(results.every((r) => r[1]) ? 0 : 1);
});
