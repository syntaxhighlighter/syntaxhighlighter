module.exports = function (config) {
  const opts = require('./base.js');

  opts.files = [
    'tests/smoke.test.js',
  ];

  config.set(opts);
}