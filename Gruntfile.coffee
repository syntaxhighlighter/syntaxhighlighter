module.exports = (grunt) ->
  grunt.loadNpmTasks 'grunt-browserify'
  grunt.loadNpmTasks 'grunt-contrib-uglify'

  grunt.config.init
    browserify:
      build:
        files:
          'pkg/3.x/scripts/shCore.js': 'src/js/syntaxhighlighter-3.x.js'
          'pkg/4.0/scripts/syntaxhighlighter.js': 'src/js/syntaxhighlighter.js'
        options:
          shim:
            xregexp:
              path: 'components/xregexp/src/xregexp.js'
              exports: 'XRegExp'

    uglify:
      build:
        files:
          'pkg/3.x/scripts/shCore.min.js': 'pkg/3.x/scripts/shCore.js'
          'pkg/4.0/scripts/syntaxhighlighter.min.js': 'pkg/4.0/scripts/syntaxhighlighter.js'
        options:
          banner: '<%= grunt.file.read("build/includes/header.txt") %>'

  # require('./tests/tasks')(grunt)

  grunt.registerTask 'build', ['browserify', 'uglify']
