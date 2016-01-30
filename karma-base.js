module.exports = {
  colors: true,
  basePath: '',
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
    resolve: {
      extensions: ['', '.js', '.es6'],
      alias: {
        'xregexp': 'xregexp/src/xregexp',
      },
    },
    module: {
      loaders: [
        {
          test: [/\.js$/, /\.es6$/],
          exclude: /node_modules/,
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
