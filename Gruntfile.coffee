module.exports = (grunt) ->
  grunt.loadNpmTasks 'grunt-browserify'
  grunt.loadNpmTasks 'grunt-contrib-uglify'

  grunt.config.init
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

  grunt.registerTask 'express', 'Launches basic HTTP server for tests', ->
    app = express()

    app.use express.static __dirname + '/'
    app.use express.directory __dirname + '/'
    app.use '/dist', express.static __dirname + '/../dist/4.x'
    app.use '/components', express.static __dirname + '/../components'

    app.listen 3000
    grunt.log.ok 'You can access tests on ' + 'http://localhost:3000'.blue + ' (Ctrl+C to stop)'
    @async()

  grunt.registerTask 'build', ['browserify', 'uglify']
  grunt.registerTask 'test', ['build', 'express']
