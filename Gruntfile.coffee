module.exports = (grunt) ->
  grunt.loadNpmTasks 'grunt-browserify'
  grunt.loadNpmTasks 'grunt-contrib-uglify'

  grunt.config.init
    browserify:
      core:
        files:
          'pkg/3.x/shCore.js': 'src/js/syntaxhighlighter-3.x.js'
          'pkg/4.x/syntaxhighlighter.js': 'src/js/syntaxhighlighter.js'
        options:
          shim:
            xregexp:
              path: 'components/xregexp/src/xregexp.js'
              exports: 'XRegExp'

    uglify:
      core:
        files:
          'pkg/3.x/shCore.min.js': 'pkg/3.x/shCore.js'
          'pkg/4.x/syntaxhighlighter.min.js': 'pkg/4.x/syntaxhighlighter.js'
        options:
          banner: '<%= grunt.file.read("build/includes/header.txt") %>'

      brushes:
        files: [{
          expand: true,
          cwd: 'src/js/brushes',
          src: '**/*.js',
          dest: 'pkg/4.x/brushes'
        }]
        options:
          banner: '<%= grunt.file.read("build/includes/header.txt") %>'

  require('./tests/tasks')(grunt)

  grunt.registerTask 'build', ['browserify', 'uglify']
