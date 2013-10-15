express = require 'express'

module.exports = (grunt) ->
  grunt.registerTask 'test', [ 'build', 'express' ]

  grunt.registerTask 'express', 'Launches basic HTTP server for tests', ->
    app = express()

    app.use express.static __dirname + '/'
    app.use express.directory __dirname + '/'
    app.use '/src', express.static __dirname + '/../src'
    app.use '/pkg', express.static __dirname + '/../pkg'
    app.use '/components', express.static __dirname + '/../components'

    app.listen 3000
    grunt.log.ok 'You can access tests on ' + 'http://localhost:3000'.blue + ' (Ctrl+C to stop)'
    @async()
