module.exports = (config) ->
  config.set
    frameworks: ['browserify', 'mocha', 'chai-jquery', 'chai', 'jquery-2.1.0']
    browsers: if process.env.TRAVIS then ['Firefox'] else ['Chrome']

    preprocessors:
      'src/**/*.js': ['browserify']
      'test/**/*.spec.coffee': ['browserify']

    browserify:
      transform: ['coffeeify']
      extensions: ['.coffee']
      watch: true
      debug: true

    files: [
      'test/**/*.spec.coffee'
    ]
