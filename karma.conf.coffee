module.exports = (config) ->
  config.set
    reporters: ['mocha']
    frameworks: ['mocha', 'chai']
    browsers: ['Chrome']
    files: [
      'dist/4.x/syntaxhighlighter.js'
      'dist/4.x/brushes/*.js'
      'test/**/*.spec.coffee'
    ]
