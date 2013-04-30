desc "Launch test server"
task "test", ->
  express = require 'express'
  app = express()

  app.use express.static __dirname + '/'
  app.use express.directory __dirname + '/'
  app.use '/sh/scripts', express.static __dirname + '/../scripts'
  app.use '/sh/styles', express.static __dirname + '/../styles'

  app.listen 2010
  jake.logger.log 'You can access tests on http://localhost:2010 (Ctrl+C to stop)'