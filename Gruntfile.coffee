module.exports = (grunt) ->
  grunt.loadNpmTasks 'grunt-contrib-uglify'
  grunt.loadNpmTasks 'grunt-contrib-watch'
  grunt.loadNpmTasks 'grunt-browserify'
  grunt.loadNpmTasks 'grunt-sass'

  grunt.config.init
    watch:
      js:
        files: ['src/**/*.js']
        tasks: ['build:js']
        options: spawn: false
      css:
        files: ['sass/**/*.scss']
        tasks: ['build:css']
        options: spawn: false

    browserify:
      core:
        files:
          'dist/3.x/shCore.js': 'src/syntaxhighlighter-3.x.js'
          'dist/4.x/syntaxhighlighter.js': 'src/syntaxhighlighter.js'
        options:
          shim:
            xregexp:
              path: 'components/xregexp/src/xregexp.js'
              exports: 'XRegExp'

    uglify:
      core:
        files:
          'dist/3.x/shCore.min.js': 'dist/3.x/shCore.js'
          'dist/4.x/syntaxhighlighter.min.js': 'dist/4.x/syntaxhighlighter.js'
        options:
          banner: '<%= grunt.file.read("build/includes/header.txt") %>'

      brushes:
        files: [{
          expand: true,
          cwd: 'src/brushes',
          src: '**/*.js',
          dest: 'dist/4.x/brushes'
          }]
        options:
          banner: '<%= grunt.file.read("build/includes/header.txt") %>'

    sass:
      themes:
        files: [{
          expand: true,
          cwd: 'sass',
          src: '**/*.scss',
          dest: 'dist/4.x/css'
          ext: '.css'
          }]

  grunt.registerTask 'express', 'Launches basic HTTP server for tests', ->
    express = require 'express'
    app = express()
    dir = "#{__dirname}/tests"

    app.use express.static dir
    app.use express.directory dir
    app.use '/dist', express.static "#{dir}/../dist/4.x"
    app.use '/components', express.static "#{dir}/../components"

    app.listen 3000
    grunt.log.ok 'You can access tests on ' + 'http://localhost:3000'.blue + ' (Ctrl+C to stop)'

  grunt.registerTask 'build:js', ['browserify', 'uglify']
  grunt.registerTask 'build:css', ['sass']
  grunt.registerTask 'build', ['build:js', 'build:css']
  # grunt.registerTask 'test', ['build', 'express']
  grunt.registerTask 'dev', ['build', 'express', 'watch']
