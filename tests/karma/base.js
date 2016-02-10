var path = require('path');

module.exports = {
  colors: true,
  basePath: `${__dirname}/../..`,
  frameworks: ['mocha'],
  reporters: ['mocha'],
  browsers: ['PhantomJS'],
  singleRun: false,
  autoWatchBatchDelay: 500,

  preprocessors: {
    '**/*.js': ['webpack', 'sourcemap']
  },

  webpack: {
    devtool: '#inline-source-map',
    module: {
      loaders: [
        {
          test: /\.js$/,
          include: [
            /syntaxhighlighter-.*/,
            /brush-.*/,
            path.resolve(__dirname, '../../src'),
            path.resolve(__dirname, '..'),
          ],
          loader: 'babel',
        },
      ],
    },
  },

  webpackMiddleware: {
    noInfo: true,
    quiet: true,
  },
};
