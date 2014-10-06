module.exports = (config) ->
  config.set
    frameworks: ['mocha', 'chai-jquery', 'chai', 'jquery-2.1.0']
    browsers: [process.env.TRAVIS and 'Firefox' or 'Chrome']

    preprocessors:
      'test/**/*.spec.coffee': ['coffee']

    browserify:
      transform: ['coffeeify']
      extensions: ['.coffee']
      watch: true
      debug: true

    files: [
      'dist/syntaxhighlighter.js'
      'dist/brushes/xml.js'
      'test/compat_brush.js'
      'test/compat_html_brush.js'
      'test/**/*.spec.coffee'
    ]
