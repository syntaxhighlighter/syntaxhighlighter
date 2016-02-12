module.exports = function (config) {
  const opts = require('./base.js');

  opts.files = [
    {
      pattern: 'tests/build-dest/*',
      served: true,
      included: false,
    },
    'tests/integration/no-compat.test.js',
    'tests/integration/commonjs.test.js',
  ];

  config.set(opts);
}