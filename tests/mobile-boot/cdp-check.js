// Asks the WebView of the running app what it rendered, over the Chrome DevTools protocol that
// `adb forward tcp:9222 localabstract:webview_devtools_remote_<pid>` exposes. Node 22 (global WebSocket and fetch).
//
//   node cdp-check.js <out.json>
//
// Exit 0 when AngularJS booted and a state is active; the evidence is written to <out.json> either way.
const fs = require('fs');

const out = process.argv[2] || 'cdp.json';
const deadline = Date.now() + 120000;
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

const PROBE = `(() => {
  const r = { href: location.href, title: document.title, text: (document.body.innerText || '').trim().slice(0, 600),
              nodes: document.getElementsByTagName('*').length, images: document.images.length };
  try {
    const inj = window.angular && angular.element(document.body).injector();
    r.angular = !!inj;
    if (inj) {
      const st = inj.get('$state');
      r.state = st.current && st.current.name;
      r.stateUrl = st.current && st.current.url;
    }
  } catch (e) { r.probeError = String(e); }
  r.native = typeof IS_NATIVE_APP !== 'undefined' ? IS_NATIVE_APP : null;
  r.domain = typeof DOMAIN !== 'undefined' ? DOMAIN : null;
  return JSON.stringify(r);
})()`;

async function target() {
  const list = await (await fetch('http://127.0.0.1:9222/json')).json();
  return list.find((t) => t.type === 'page' && t.webSocketDebuggerUrl);
}

function session(url) {
  return new Promise((resolve, reject) => {
    const ws = new WebSocket(url);
    const pending = new Map();
    const events = [];
    let id = 0;
    ws.onmessage = (m) => {
      const msg = JSON.parse(m.data);
      if (msg.id && pending.has(msg.id)) { pending.get(msg.id)(msg); pending.delete(msg.id); }
      else if (msg.method) events.push(msg);
    };
    ws.onerror = reject;
    ws.onopen = () => resolve({
      events,
      send: (method, params = {}) => new Promise((res) => { const n = ++id; pending.set(n, res); ws.send(JSON.stringify({ id: n, method, params })); }),
      close: () => ws.close(),
    });
  });
}

(async () => {
  let result = { ok: false, reason: 'no page target' };
  let s = null;
  while (Date.now() < deadline) {
    try {
      const t = await target();
      if (t) {
        s = s || await session(t.webSocketDebuggerUrl);
        await s.send('Runtime.enable');
        const ev = await s.send('Runtime.evaluate', { expression: PROBE, returnByValue: true });
        const probe = JSON.parse(ev.result.result.value);
        const exceptions = s.events.filter((e) => e.method === 'Runtime.exceptionThrown')
          .map((e) => (e.params.exceptionDetails.exception && e.params.exceptionDetails.exception.description) || e.params.exceptionDetails.text);
        result = { ok: !!(probe.angular && probe.state), probe, exceptions };
        if (result.ok) break;
      }
    } catch (e) {
      result = { ok: false, reason: String(e) };
      s = null;
    }
    await sleep(3000);
  }
  fs.writeFileSync(out, JSON.stringify(result, null, 2));
  console.log(JSON.stringify(result, null, 2));
  if (s) s.close();
  process.exit(result.ok ? 0 : 1);
})();
