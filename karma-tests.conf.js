module.exports = function (config) {
  const opts = require('./karma-base.js');

  opts.files = [
    'tests/**/!(build).test.js',
  ];

  config.set(opts);
}