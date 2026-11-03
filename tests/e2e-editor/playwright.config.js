// Editor visual tests, run against the local rig (/srv/data/pw-editor/rig, port 28850). Test credentials only.
const { defineConfig } = require('@playwright/test');

module.exports = defineConfig({
  testDir: './tests',
  timeout: 90000,
  workers: 1,
  retries: 0,
  reporter: [['list']],
  outputDir: './results',
  use: {
    baseURL: process.env.SIB_URL || 'http://127.0.0.1:28850',
    locale: 'en-US',
    timezoneId: 'UTC',
    screenshot: 'off',
  },
});
