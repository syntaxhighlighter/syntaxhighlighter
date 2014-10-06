module.exports = (grunt) ->
  fs = require 'fs'

  grunt.loadNpmTasks 'grunt-contrib-uglify'
  grunt.loadNpmTasks 'grunt-contrib-watch'
  grunt.loadNpmTasks 'grunt-karma'
  grunt.loadNpmTasks 'grunt-browserify'
  grunt.loadNpmTasks 'grunt-contrib-clean'
  grunt.loadNpmTasks 'grunt-contrib-copy'

  grunt.config.init
    clean:
      build: 'dist'

    copy:
      build:
        src: 'index.html'
        dest: 'dist/index.html'

      brushes:
        src: 'node_modules/brush-*/lib/brush-*.js'
        dest: 'dist/brushes/'
        flatten: true
        expand: true
        rename: (dest, src) ->
          src = src.replace /-javascript/, '-jscript' # a minor exception
          dest + src.replace /^brush-/, ''

      themes:
        src: 'node_modules/theme-*/css/theme-*.css'
        dest: 'dist/css/'
        flatten: true
        expand: true
        rename: (dest, src) -> dest + src.replace /^theme-/, ''

    karma:
      options: configFile: 'karma.conf.coffee'
      background: background: true
      single: singleRun: true

    watch:
      options: spawn: false

      test:
        tasks: ['karma:background:run']
        files: [
          'dist/**/*.*'
          'test/**/*.spec.coffee'
        ]

      js:
        tasks: ['build:js', 'karma:background:run']
        files: [
          'src/**/*.js'
          '../*/lib/*.js'
        ]

      css:
        tasks: ['copy:themes', 'karma:background:run']
        files: ['../theme-*/css/*.css']

    browserify:
      core:
        # files:
        src: 'src/syntaxhighlighter.js'
        dest: 'dist/syntaxhighlighter.js'
          # 'dist/syntaxhighlighter.js': 'src/syntaxhighlighter.js'

    uglify:
      core:
        files:
          'dist/syntaxhighlighter.min.js': 'dist/syntaxhighlighter.js'
        options:
          banner: BANNER

      # brushes:
      #   files: [
      #     expand: true,
      #     cwd: 'src/brushes',
      #     src: '**/*.js',
      #     dest: 'dist/brushes'
      #   ]
      #   options:
      #     banner: BANNER

  grunt.registerTask 'jssize', ->
    stats = fs.statSync "#{__dirname}/dist/syntaxhighlighter.min.js"
    grunt.log.ok "syntaxhighlighter.min.js #{Math.round stats.size / 1024} KB".blue.bold

  grunt.registerTask 'express', 'Launches basic HTTP server for tests', ->
    express = require 'express'
    staticMiddleware = require 'serve-static'
    indexMiddleware = require 'serve-index'

    done = @async()
    app = express()
    dir = "#{__dirname}/demos"

    app.use staticMiddleware dir
    app.use indexMiddleware dir
    app.use '/dist', staticMiddleware "#{dir}/../dist"
    app.use '/components', staticMiddleware "#{dir}/../components"

    app.listen 3000
    grunt.log.ok 'You can access tests on ' + 'http://localhost:3000'.blue + ' (Ctrl+C to stop)'

  grunt.registerTask 'build:js', ['browserify', 'uglify', 'jssize']
  grunt.registerTask 'build', ['clean', 'copy', 'build:js']
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
