const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const seedFile = path.join(__dirname, '..', 'seed', 'activities.seed.js');
const code = fs.readFileSync(seedFile, 'utf8');
const sandbox = {
  module: { exports: {} },
  exports: {},
  console,
};

vm.runInNewContext(code, sandbox, { filename: seedFile });

module.exports = sandbox.module.exports;
