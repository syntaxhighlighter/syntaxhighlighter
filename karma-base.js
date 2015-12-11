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
    resolveLoader: {
      modulesDirectories: ['node_modules', 'build'],
    },
    module: {
      loaders: [
        {
          test: /xregexp/,
          loader: 'xregexp-loader',
        },
        {
          test: /\.js$/,
          exclude: /node_modules/,
          loader: 'babel',
        },
      ],
    }
  },

  webpackMiddleware: {
    noInfo: true,
    quiet: true,
  },
};
