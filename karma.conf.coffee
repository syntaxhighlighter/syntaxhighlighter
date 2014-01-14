module.exports = (config) ->
  config.set
    frameworks: ['mocha', 'chai-jquery', 'chai', 'browserify']
    browsers: if process.env.TRAVIS then ['Firefox'] else ['Chrome']

    preprocessors:
      'test/**/*.spec.coffee': 'coffee'

    browserify:
      transform: ['coffeeify']
      extensions: ['.coffee']
      watch: true
      debug: true

    files: [
      'bower_components/jquery.min.js'
      'dist/syntaxhighlighter.js'
      'dist/brushes/*.js'
      'test/compat_brush.js'
      'test/compat_html_brush.js'
      'test/**/*.spec.coffee'
    ]
