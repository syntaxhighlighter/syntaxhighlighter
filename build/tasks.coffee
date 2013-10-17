path      = require 'path'
async     = require 'async'
fs        = require 'fs'
minimatch = require 'minimatch'
less      = require 'less'
vm        = require 'vm'
shelljs   = require 'shelljs'
uglify    = require 'uglify-js'
sass      = require 'node-sass'

compressCss = (file, source, callback) ->
  parser = new less.Parser paths: [path.dirname(file)], optimization: 2
  parser.parse source, (err, tree) ->
    callback err, tree.toCSS(compress: true)

compressJs = (source) ->
  {code, map} = uglify.minify source
  code

compileSass = (file, callback) ->
  data = fs.readFileSync file, 'utf8'
  sass.render data, callback, include_paths: [sourceSassDir], output_style: null

isDirectory = (dir) ->
  fs.existsSync(dir) and fs.statSync(dir).isDirectory()

findFilesIn = (dir, pattern) ->
  shelljs.find(dir)
    .filter (file) ->
      minimatch file, pattern, matchBase: true
    .filter (file) ->
      not isDirectory file

loadFilesIntoVariables = (dir) ->
  result = {}

  for file in findFilesIn dir, '*.*'
    varName = path.basename(file, path.extname(file))
    result[varName] = fs.readFileSync(file, "utf8")

  result

baseDir       = __dirname
sourceDir     = path.resolve baseDir, '..', 'src'
outputDir     = path.resolve baseDir, '..', 'pkg'
componentsDir = path.resolve baseDir, '..', 'components'
includesDir   = path.resolve baseDir, 'includes'
sourceJsDir   = path.resolve sourceDir, 'js'
sourceSassDir = path.resolve sourceDir, 'sass'
outputJsDir   = path.resolve outputDir, 'scripts'
outputCssDir  = path.resolve outputDir, 'styles'

variables         = loadFilesIntoVariables includesDir
variables.version = JSON.parse(fs.readFileSync path.resolve baseDir, '../package.json').version
variables.date    = new Date().toUTCString()
variables.about   = variables.about.replace(/\n|\t/g, "").replace(/"/g, "\\\"")

module.exports = (grunt) ->
  grunt.registerTask "build", "clean compile_sass copy add_header process_variables validate".split(/\s/g)

  grunt.registerTask "clean", "Cleans the build folder", ->
    shelljs.rm '-fr', outputDir
    shelljs.mkdir '-p', outputDir

  grunt.registerTask "compile_sass", ->
    files = findFilesIn sourceSassDir, "**/*.scss"
    shelljs.mkdir '-p', outputCssDir

    jobs = files.map (sassFile) ->
      (done) ->
        cssFile = path.join(outputCssDir, path.basename sassFile).replace /\.scss$/, '.css'

        return done() if isDirectory(sassFile) or /theme_template/.test sassFile

        compileSass sassFile, (err, css) ->
          fs.writeFileSync cssFile, css
          done()

    async.parallel jobs, @async()

  grunt.registerTask "copy", ->
    shelljs.mkdir '-p', outputJsDir

    shelljs.cp "#{baseDir}/index.html", outputDir
    shelljs.cp "#{sourceDir}/*-LICENSE", outputDir
    shelljs.cp "#{sourceJsDir}/sh*.js", outputJsDir

    core    = path.join sourceJsDir, "shCore.js"
    xregexp = path.join componentsDir, "xregexp", "xregexp-all.js"

    fs.writeFileSync path.join(outputJsDir, "shCore.js"), fs.readFileSync(xregexp, "utf8") + fs.readFileSync(core, "utf8")

  grunt.registerTask "pack", ->
    core = path.join outputJsDir, "shCore.js"

    fs.writeFileSync core, compressJs core

    findFilesIn(outputCssDir, "**/*.css").forEach (file) ->
      compressCss file, fs.readFileSync(file, "utf8"), (err, source) ->
        fs.writeFileSync file, source

  grunt.registerTask "add_header", ->
    files = findFilesIn(outputDir, "**/*.js").concat(findFilesIn(outputDir, "**.css"))

    files.forEach (file) ->
      fs.writeFileSync file, variables.header + fs.readFileSync(file, "utf8")  unless isDirectory(file)

  grunt.registerTask "process_variables", ->
    process = (str) ->
      for key of variables
        str = str.replace("@" + key.toUpperCase() + "@", variables[key])
      str

    files = findFilesIn(outputDir, "**/*.js").concat(findFilesIn(outputDir, "**/*.css"))

    files.forEach (file) ->
      fs.writeFileSync file, process(fs.readFileSync(file, "utf8")) unless isDirectory(file)

  grunt.registerTask "validate", ->
    context = {}
    coreFile = path.join(outputJsDir, "shCore.js")
    vm.runInNewContext fs.readFileSync(coreFile, "utf8"), context, coreFile

    findFilesIn(outputDir, "**.js").forEach (file) ->
      return  if /shCore/.test(file)
      vm.runInNewContext fs.readFileSync(file, "utf8"), context, file

