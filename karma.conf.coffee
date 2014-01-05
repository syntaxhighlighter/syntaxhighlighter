module.exports = (config) ->
  config.set
    reporters: ['mocha']
    frameworks: ['mocha', 'chai-jquery', 'chai', 'browserify']
    browsers: ['Chrome']

    preprocessors:
      'test/integrations/**/*.spec.coffee': 'coffee'
      'test/unit/**/*.spec.coffee': 'browserify'

    browserify:
      transform: ['coffeeify']
      extensions: ['.coffee']
      watch: true
      debug: true

    files: [
      'test/unit/**/*.spec.coffee'

      'dist/syntaxhighlighter.js'
      'dist/brushes/*.js'
      'test/integrations/3.x-compat/compat_brush.js'
      'test/integrations/3.x-compat/compat_html_brush.js'

      'test/integrations/**/*.spec.coffee'
    ]
