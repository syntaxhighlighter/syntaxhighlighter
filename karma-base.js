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
    module: {
      loaders: [
        {
          test: /\.js$/,
          // exclude: /node_modules/,
          include: [
            /syntaxhighlighter-.*/,
            /brush-.*/,
            `${__dirname}/src`,
            `${__dirname}/tests`,
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
