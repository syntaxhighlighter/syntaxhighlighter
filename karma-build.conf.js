module.exports = function (config) {
  const opts = require('./karma-base.js');

  opts.files = [
    {
      pattern: 'tests/build-dest/*',
      served: true,
      included: false,
    },
    'tests/**/build.test.js',
  ];

  config.set(opts);
}