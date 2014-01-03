module.exports = (grunt) ->
  grunt.loadNpmTasks 'grunt-contrib-uglify'
  grunt.loadNpmTasks 'grunt-contrib-watch'
  grunt.loadNpmTasks 'grunt-karma'
  grunt.loadNpmTasks 'grunt-browserify'
  grunt.loadNpmTasks 'grunt-sass'

  grunt.config.init
    karma:
      options:
        configFile: 'karma.conf.coffee'

      background:
        background: true

      auto:
        autoWatch: true

      single:
        singleRun: true

    watch:
      options: spawn: false

      test:
        files: ['dist/**/*.*', 'test/**/*.spec.coffee']
        tasks: ['karma:background:run']

      js:
        files: ['src/**/*.js']
        tasks: ['build:js']

      css:
        files: ['sass/**/*.scss']
        tasks: ['build:css']

    browserify:
      core:
        files:
          'dist/syntaxhighlighter.js': 'src/syntaxhighlighter.js'
        options:
          transform: ['coffeeify']
          extensions: ['.coffee']
          shim:
            xregexp:
              path: 'components/xregexp/src/xregexp.js'
              exports: 'XRegExp'

    uglify:
      core:
        files:
          'dist/syntaxhighlighter.min.js': 'dist/syntaxhighlighter.js'
        options:
          banner: BANNER

      brushes:
        files: [
          expand: true,
          cwd: 'src/brushes',
          src: '**/*.js',
          dest: 'dist/brushes'
        ]
        options:
          banner: BANNER

    sass:
      themes:
        files: [
          expand: true
          cwd: 'sass'
          src: '**/*.scss'
          dest: 'dist/css'
          ext: '.css'
        ]

  grunt.registerTask 'express', 'Launches basic HTTP server for tests', ->
    express = require 'express'
    app = express()
    dir = "#{__dirname}/tests"

    app.use express.static dir
    app.use express.directory dir
    app.use '/dist', express.static "#{dir}/../dist"
    app.use '/components', express.static "#{dir}/../components"

    app.listen 3000
    grunt.log.ok 'You can access tests on ' + 'http://localhost:3000'.blue + ' (Ctrl+C to stop)'

  grunt.registerTask 'build:js', ['browserify', 'uglify']
  grunt.registerTask 'build:css', ['sass']
  grunt.registerTask 'build', ['build:js', 'build:css']
  grunt.registerTask 'test', ['build', 'karma:single']
  # grunt.registerTask 'inspect', ['express', 'watch']
  grunt.registerTask 'dev', ['karma:background:start', 'watch']



TEST = """
  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Hello SyntaxHighlighter</title>
    <script type="text/javascript" src="scripts/shCore.js"></script>
    <script type="text/javascript" src="scripts/shBrushJScript.js"></script>
    <link type="text/css" rel="stylesheet" href="styles/shCoreDefault.css">
    <script type="text/javascript">SyntaxHighlighter.all();</script>
    <style type="text/css">
      body {
        background: white;
        font-family: helvetica;
      }
    </style>
  </head>

  <body>

  <h1>Hello SyntaxHighlighter</h1>

  <pre class="brush: js;">
    function helloSyntaxHighlighter()
    {
      return "hi!";
    }
  </pre>

  </html>
"""


BANNER = """
  /**
   * SyntaxHighlighter
   * http://alexgorbatchev.com/SyntaxHighlighter
   *
   * SyntaxHighlighter is donationware. If you are using it, please donate.
   * http://alexgorbatchev.com/SyntaxHighlighter/donate.html
   *
   * @version
   * <%= version %> (<%= date %>)
   *
   * @copyright
   * Copyright (C) 2004-2013 Alex Gorbatchev.
   *
   * @license
   * Dual licensed under the MIT and GPL licenses.
   */

 """
