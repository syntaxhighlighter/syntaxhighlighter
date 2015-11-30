module.exports = function(config) {
  config.set({
    colors: true,
    basePath: '',
    frameworks: ['mocha'],
    reporters: ['mocha'],
    browsers: ['PhantomJS'],
    singleRun: true,

    files: [
      'tests/**/*.test.js',
    ],

    preprocessors: {
      '**/*.js': ['webpack']
    },

    webpack: {
      devtool: '#inline-source-map',
      module: {
        loaders: [
          {
            test: /\.js$/,
            exclude: /node_modules/,
            loaders: ['babel'],
          },
        ],
      }
    },

    webpackMiddleware: {
      noInfo: true,
      quiet: true,
    },
  });
}