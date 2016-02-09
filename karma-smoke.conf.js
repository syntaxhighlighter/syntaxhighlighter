module.exports = function (config) {
  const opts = require('./karma-base.js');

  opts.files = [
    'tests/smoke.test.js',
  ];

  config.set(opts);
}