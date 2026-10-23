// Happy path for bin/src/cli.js "rebuild manifest": axios.get with
// responseType json and a Basic Authorization header, response.data.success.
// Run with the axios the repo's package.json asks for (see tooling.sh).
const http = require('http');
const axios = require('axios');

const server = http.createServer((req, res) => {
    const ok = req.url === '/backoffice/api_options/manifest'
        && req.headers.authorization === 'Basic ' + Buffer.from('dummy@example.com:secret').toString('base64');
    res.writeHead(ok ? 200 : 401, {'Content-Type': 'application/json'});
    res.end(JSON.stringify(ok ? {success: true, message: 'Manifest rebuilt'} : {success: false, message: 'denied'}));
});

server.listen(0, '127.0.0.1', async () => {
    const base = 'http://127.0.0.1:' + server.address().port;
    let failed = null;
    try {
        const response = await axios.get(base + '/backoffice/api_options/manifest', {
            responseType: 'json',
            headers: {'Authorization': 'Basic ' + Buffer.from('dummy@example.com:secret').toString('base64')}
        });
        if (response.data.success !== true || response.data.message !== 'Manifest rebuilt') {
            failed = 'unexpected body ' + JSON.stringify(response.data);
        }
        // Error path the CLI relies on: .catch gets an object with .message
        try {
            await axios.get(base + '/nope', {responseType: 'json'});
            failed = failed || 'expected a rejection on 401';
        } catch (error) {
            if (typeof error !== 'object' || !Object.prototype.hasOwnProperty.call(error, 'message')) {
                failed = failed || 'rejection has no message';
            }
        }
    } catch (e) {
        failed = e.message;
    }
    server.close();
    if (failed) {
        console.error('FAIL axios: ' + failed);
        process.exit(1);
    }
    console.log('ok   axios ' + require('axios/package.json').version);
});
