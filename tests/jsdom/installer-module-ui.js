// Drives the real ModuleController (design/desktop/backoffice/js/controllers/installer.js), the real template
// (template/installer/module.phtml, PHP echoes resolved) and the real angular-file-upload in jsdom, and checks the
// console stays clean. Only the services behind the controller (HTTP factories, swal, XHR) are stubbed.
//
//   NODE_PATH=<dir with jsdom> node tests/jsdom/installer-module-ui.js
const fs = require('fs');
const path = require('path');
const { JSDOM, VirtualConsole } = require('jsdom');

const base = path.resolve(__dirname, '../../siberian/app/sae/design/desktop/backoffice');
const read = (p) => fs.readFileSync(path.join(base, p), 'utf8');

const template = read('template/installer/module.phtml')
    .replace(/<\?php echo __title\("([^"]*)"\); \?>/g, '$1')
    .replace(/<\?php echo __\("([^"]*)"\);? ?\?>/g, '$1')
    .replace(/<\?php echo p__\('backoffice', '([^']*)'\); \?>/g, '$1')
    .replace(/<\?php echo Siberian_Ftp::DEFAULT_PORT \?>/g, '21')
    .replace(/<\?php[\s\S]*?\?>/g, '');

const consoleErrors = [];
const virtualConsole = new VirtualConsole();
virtualConsole.on('jsdomError', (e) => consoleErrors.push('jsdomError: ' + (e.detail || e.message)));
virtualConsole.on('error', (...x) => consoleErrors.push('console.error: ' + x.join(' ')));

const dom = new JSDOM('<!doctype html><body><div ng-app="T"><div ng-controller="ModuleController" id="root">' +
    template + '</div></div></body>', { runScripts: 'outside-only', pretendToBeVisual: true, virtualConsole });
const w = dom.window;
w.addEventListener('error', (e) => consoleErrors.push('window error: ' + e.message));

// Fake XHR for the uploader: tests answer it by hand.
w.eval(`
  window.__xhrs = [];
  window.XMLHttpRequest = function () {
      var x = this; window.__xhrs.push(x);
      x.upload = {}; x.headers = {};
      x.open = function (m, u) { x.method = m; x.url = u; };
      x.send = function (form) { x.form = form; x.sent = true; };
      x.setRequestHeader = function () {};
      x.getAllResponseHeaders = function () { return ''; };
      x.abort = function () {};
  };
  window.__swals = [];
  window.swal = function (opts, cb) { window.__swals.push({opts: opts, cb: cb}); };
  window.swal.close = function () {}; window.swal.showInputError = function () {};
`);
w.eval(read('js/angular.min.js'));
w.eval(read('js/angular/angular-route.min.js'));
w.eval(read('js/angular/angular-file-upload.min.js'));
w.eval('var BASE_URL = "";');
w.eval(`
  var legacy = function (p) { p.success = function (f) { p.then(function (r) { f(r.data !== undefined ? r.data : r); }); return p; };
      p.error = function (f) { p.then(null, function (r) { f(r && r.data !== undefined ? r.data : r); }); return p; };
      var fin = p.finally; p.finally = function (f) { return legacy(fin.call(p, f)); }; return p; };
  window.__pending = {};
  var App = angular.module('T', ['ngRoute', 'angularFileUpload'])
   .directive('sbSection', function () { return {restrict: 'E', transclude: true, scope: {title: '='}, template: '<section><div ng-transclude></div></section>'}; })
   .directive('sbLoader', function () { return {restrict: 'E', scope: {is_visible: '='}, template: ''}; })
   .filter('trusted_html', function () { return function (x) { return x; }; })
   .factory('Header', function () { return function () { this.button = {left: {}}; }; })
   .factory('Url', function () { return {get: function (p) { return '/' + p; }}; })
   .factory('Label', function () { return {uploader: {error: {general: 'GENERAL', only_one_at_a_time: 'ONE', type: {zip: 'ZIP'}}}}; })
   .factory('AdvancedTools', function () { return {}; })
   .factory('Backoffice', function ($q) { return {clearCache: function () { return legacy($q.when({message: 'cleared'})); }}; })
   .factory('Installer', function ($q) {
      var f = {filename: null, reinstall: null};
      // each call parks a deferred under its name, the test settles it
      ['loadData', 'downloadUpdate', 'checkModuleLicense', 'setModuleLicense', 'checkPermissions', 'copy', 'install', 'checkModuleUpdates']
        .forEach(function (n) { f[n] = function () { var d = $q.defer(); (window.__pending[n] = window.__pending[n] || []).push(d); return legacy(d.promise); }; });
      return f;
   })
   .run(function ($rootScope) { $rootScope.message = {setText: function (t) { window.__last = {text: t}; return this; }, isError: function (e) { window.__last.error = e; return this; }, show: function () { window.__shown = (window.__shown || 0) + 1; return this; }}; });
`);
w.eval(read('js/controllers/installer.js').replace(/^App\./m, 'angular.module("T").'));

const results = [];
const check = (name, ok, extra) => { results.push([name, !!ok, extra]); };
const $ = (s) => w.document.querySelector(s);
const visible = (s) => { const e = $(s); return !!e && !e.classList.contains('ng-hide'); };
const text = (s) => ($(s) ? $(s).textContent.replace(/\s+/g, ' ').trim() : null);
let scope;
const tick = () => new Promise((r) => setTimeout(() => { scope.$digest(); setTimeout(r, 30); }, 10));
const settle = async (name, kind, value) => {
    const d = w.__pending[name].shift();
    scope.$apply(() => (kind === 'ok' ? d.resolve({ data: value }) : d.reject({ data: value })));
    await tick();
};
const answerXhr = async (status, body) => {
    const x = w.__xhrs[w.__xhrs.length - 1];
    x.status = status; x.response = x.responseText = typeof body === 'string' ? body : JSON.stringify(body);
    scope.$apply(() => (status < 400 ? x.onload() : x.onerror ? x.onload() : x.onload()));
    await tick();
};
const file = (name, size) => new w.File([new Uint8Array(size || 10)], name);
const drop = async (f) => {
    const ev = new w.Event('drop', { bubbles: true, cancelable: true });
    ev.dataTransfer = { files: [f], types: ['Files'] };
    scope.$apply(() => $('#module_zone').dispatchEvent(ev));
    await tick();
};

process.on('uncaughtException', (e) => {
    results.forEach(([n, ok, x]) => console.log((ok ? 'ok   ' : 'FAIL ') + n + (ok ? '' : ' -> ' + x)));
    console.log('ABORTED: ' + e.stack.split('\n').slice(0, 3).join(' '));
    process.exit(1);
});

w.angular.element(w.document).ready(async () => {
    scope = w.angular.element($('#root')).scope();
    await tick();
    await settle('loadData', 'ok', { title: 't', icon: 'i', words: { licenseNeeded: 'LICENCE NEEDED' }, ini: { max_size: 1000000 } });
    await settle('checkModuleUpdates', 'ok', { updates: [], stale: false, fetched_at: 1, terms_accepted: true, terms_hash: 'h' });

    // 1. structure: one area, one input, no second drop zone, a labelled button
    check('one file input', w.document.querySelectorAll('input[type=file]').length === 1);
    check('one drop zone', w.document.querySelectorAll('.drop-zone').length === 1);
    check('no separate "Drop your module here" block', !/Drop your module here|Click here to send your module/.test(w.document.body.textContent));
    check('zone is a keyboard-reachable button', $('#module_zone').getAttribute('role') === 'button' && $('#module_zone').getAttribute('tabindex') === '0');
    check('idle text shown', visible('#module_zone_status span:nth-child(1)') && /Click to choose/.test(text('#module_zone_status')));
    check('check button has a label and an icon', /Check for updates/.test(text('#check_for_updates')) && !!$('#check_for_updates .fa-refresh'));

    // 2. picker: click, Enter, Space open the file dialog once each, nothing when busy
    let opened = 0;
    $('#file_uploader').click = () => { opened++; };
    $('#module_zone').click(); await tick();
    const key = (k) => { const e = new w.Event('keydown', { bubbles: true, cancelable: true }); e.key = k; e.which = k === 'Enter' ? 13 : 32; $('#module_zone').dispatchEvent(e); return e.defaultPrevented; };
    const p1 = key('Enter'), p2 = key(' ');
    check('click, Enter and Space open the picker', opened === 3 && p1 && p2, 'opened=' + opened + ' p1=' + p1 + ' p2=' + p2);

    // 3. drop: sent at once, loader and file name shown
    await drop(file('mod.zip'));
    check('drop starts the upload without another click', w.__xhrs.length === 1 && w.__xhrs[0].sent === true);
    check('zone shows sending state and the file name', scope.zoneState() === 'sending' && text('.module-zone-file') === 'mod.zip' && visible('.module-zone-progress'));
    $('#module_zone').click(); await tick();
    check('picker ignored while sending', opened === 3, 'opened=' + opened);
    await drop(file('other.zip'));
    check('second file ignored while sending', w.__xhrs.length === 1);

    // 4. success without licence: package details shown, zone done
    await answerXhr(200, { success: true, filename: 'bW9k', package_details: { name: 'Mod Update', version: '1', code: false, item_id: false }, release_note: { url: false, show: false } });
    check('success: zone done and package details visible', scope.zoneState() === 'done' && visible('.package-details-section'));

    // 5. refusal by the server (JSON), then a replacement file goes through
    await drop(file('bad.zip'));
    check('a sent file can be replaced', w.__xhrs.length === 2 && !visible('.package-details-section'));
    await answerXhr(400, { error: true, message: '#19-010: The package you have uploaded is invalid.' });
    check('refusal: message toast and error state', scope.zoneState() === 'error' && /#19-010/.test(w.__last.text) && w.__last.error === true);

    // 6. non JSON answer (proxy page, size limit): general message, no exception
    await drop(file('big.zip'));
    await answerXhr(413, '<html>Request Entity Too Large</html>');
    check('non JSON error: general message', scope.zoneState() === 'error' && w.__last.text === 'GENERAL');

    // 7. too large for PHP: refused before sending
    const before = w.__xhrs.length;
    await drop(file('huge.zip', 2000000));
    check('too large: nothing sent, error state', w.__xhrs.length === before && scope.zoneState() === 'error');

    // 8. licence: checking state, then cancel explains
    await drop(file('lic.zip'));
    await answerXhr(200, { success: true, filename: 'bGlj', package_details: { name: 'Lic', version: '1', code: 'lic', item_id: 7 }, release_note: { url: false, show: false } });
    check('licence being checked: loader state', scope.zoneState() === 'checking' && !visible('.package-details-section'));
    await settle('checkModuleLicense', 'err', { error: true, code: 'license_empty' });
    check('licence prompt opened', w.__swals.length === 1);
    w.__swals[0].cb(false); await tick();
    check('cancelled licence: explained, zone says so', scope.zoneState() === 'license' && w.__last.text === 'LICENCE NEEDED' && !visible('.package-details-section'));

    // 9. check for updates: one request, loader, then the result in place
    const click = async (s) => { $(s).click(); await tick(); };
    await click('#check_for_updates');
    check('check: loader text and disabled button', /Checking for updates/.test(text('#check_for_updates')) && $('#check_for_updates').disabled);
    await click('#check_for_updates');
    check('check: no second request while running', w.__pending.downloadUpdate.length === 1);
    await settle('downloadUpdate', 'ok', { success: true, message: 'Your system is up to date.' });
    check('check: up to date shown next to the button', /Your system is up to date/.test(text('.check-updates-result')) && visible('#check_for_updates'));
    await click('#check_for_updates');
    await settle('downloadUpdate', 'err', undefined);
    check('check: unreachable answer shows an error, no exception', /GENERAL/.test(text('.check-updates-result')) && scope.check_for_updates.result === 'error');
    await click('#check_for_updates');
    await settle('downloadUpdate', 'err', { error: true, message: 'Unable to check for updates now.' });
    check('check: server message shown', /Unable to check/.test(text('.check-updates-result')));
    await click('#check_for_updates');
    await settle('downloadUpdate', 'ok', { success: true, filename: 'dXA=', package_details: { name: 'Siberian Update', version: '9', code: false, item_id: false }, release_note: { url: false, show: false } });
    check('check: update downloaded, package details shown', scope.check_for_updates.result === 'downloaded' && visible('.package-details-section') && $('#check_for_updates').parentNode.classList.contains('ng-hide'));

    // 10. copy failure keeps its message (it used to show an empty toast)
    scope.$apply(() => { w.angular.element($('#root')).injector().get('Installer').filename = 'dXA='; scope.copyModule(); });
    await settle('copy', 'err', { error: 1, message: '#19-013: The folder /x is not writable.' });
    check('copy error shows the server message', /#19-013/.test(w.__last.text));

    check('no console error, no uncaught exception', consoleErrors.length === 0, consoleErrors.join(' | '));

    let failed = 0;
    for (const [n, ok, extra] of results) { if (!ok) { failed++; } console.log((ok ? 'ok   ' : 'FAIL ') + n + (ok || !extra ? '' : '  -> ' + extra)); }
    console.log(failed === 0 ? 'console clean on every action, ' + results.length + ' checks' : failed + ' failed');
    process.exit(failed === 0 ? 0 : 1);
});
