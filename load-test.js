#!/usr/bin/env node

const autocannon = require('autocannon');

const args = process.argv.slice(2);
const url = args[0] || 'http://127.0.0.1:8000';
const connections = parseInt(args[1], 10) || 10;
const duration = parseInt(args[2], 10) || 20;

console.log(`Starting load test for ${url} with ${connections} connections for ${duration} seconds...`);

const instance = autocannon({
  url,
  connections,
  duration,
  method: 'GET',
  headers: {
    'Cache-Control': 'no-cache',
  },
  requests: [
    { method: 'GET', path: '/' },
    { method: 'GET', path: '/profile' },
    { method: 'GET', path: '/student/schedule' },
  ],
}, (err, result) => {
  if (err) {
    console.error('Load test error:', err);
    process.exit(1);
  }
  console.log('Load test complete.');
  console.log(result);
});

autocannon.track(instance, { renderProgressBar: true });
