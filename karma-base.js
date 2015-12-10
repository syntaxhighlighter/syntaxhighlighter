module.exports = {
  colors: true,
  basePath: '',
  frameworks: ['mocha'],
  reporters: ['mocha'],
  browsers: ['PhantomJS'],
  singleRun: false,

  preprocessors: {
    '**/*.js': ['webpack', 'sourcemap']
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
};
