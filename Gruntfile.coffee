module.exports = (grunt) ->
  require('./build/tasks')(grunt)
  require('./tests/tasks')(grunt)

