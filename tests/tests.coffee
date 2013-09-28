desc 'Launch test server'
task 'test', [ 'build' ], ->
  express = require 'express'
  app = express()

  app.use express.static __dirname + '/'
  app.use express.directory __dirname + '/'
  app.use '/sh', express.static __dirname + '/../pkg'
  app.use '/bower_components', express.static __dirname + '/../bower_components'

  app.listen 2010
  jake.logger.log 'You can access tests on http://localhost:2010 (Ctrl+C to stop)'
