path      = require 'path'
async     = require 'async'
fs        = require 'fs'
minimatch = require 'minimatch'
less      = require 'less'
vm        = require 'vm'
uglify    = require 'uglify-js'
sass      = require 'node-sass'

compressCss = (file, source, callback) ->
  parser = new less.Parser(
    paths: [path.dirname(file)]
    optimization: 2
  )
  parser.parse source, (err, tree) ->
    callback err, tree.toCSS(compress: true)

compressJs = (source) ->
  { code, map } = uglify.minify source
  code

compileSass = (file, callback) ->
  data = fs.readFileSync file, 'utf8'
  sass.render data, callback, include_paths: [ sourceSassDir ], output_style: null

copy = (src, dest, pattern) ->
  glob(src, pattern).forEach (file) ->
    mkdir path.join(dest, path.dirname(file))
    sourceFile = path.join(src, file)
    unless isDir(sourceFile)
      buf = fs.readFileSync(sourceFile)
      fs.writeFileSync path.join(dest, file), buf

isDir = (dir) ->
  fs.existsSync(dir) and fs.statSync(dir).isDirectory()

mkdir = (dirToMake) ->
  return if fs.existsSync(dir)
  dir = "/"

  for part in dirToMake.split("/")
    dir = path.join dir, part
    fs.mkdirSync dir unless fs.existsSync(dir)

rmdir = (dir) ->
  return  unless fs.existsSync(dir)

  glob(dir, "**").reverse().forEach (file) ->
    file = path.join(dir, file)
    try
      fs.unlinkSync file
    catch e
      fs.rmdirSync file

  fs.rmdirSync dir

glob = (dir, pattern) ->
  return [] unless fs.existsSync(dir)
  result = fs.readdirSync(dir)
  i = 0

  while i < result.length
    subdir = path.join(dir, result[i])
    if fs.lstatSync(subdir).isDirectory()
      subItems = glob(subdir, pattern).map (item) -> path.join result[i], item
      left = result.slice(0, i)
      right = result.slice(i)
      result = left.concat(subItems).concat(right)
      i += subItems.length
    i++

  result = result.filter(minimatch.filter(pattern, dot: true))
  result.sort()
  result

loadFilesIntoVariables = (dir) ->
  result = {}

  for file in glob(dir, "*")
    varName = path.basename(file, path.extname(file))
    result[varName] = fs.readFileSync(path.join(dir, file), "utf8")

  result

baseDir       = __dirname
sourceDir     = path.join baseDir, '../src'
outputDir     = path.join baseDir, '../pkg'
componentsDir = path.join baseDir, '../bower_components'
includesDir   = path.join baseDir, 'includes'
sourceJsDir   = path.join sourceDir, 'js'
sourceSassDir = path.join sourceDir, 'sass'
outputJsDir   = path.join outputDir, 'scripts'
outputCssDir  = path.join outputDir, 'styles'

variables         = loadFilesIntoVariables(includesDir)
variables.version = "3.0.83"
variables.date    = new Date().toUTCString()
variables.about   = variables.about.replace(/\n|\t/g, "").replace(/"/g, "\\\"")

desc "Builds SyntaxHighlighter"
task "default", ["build"]

task "build", "clean compile_sass copy pack add_header process_variables validate".split(RegExp(" ", "g")), ->
  jake.logger.log "DONE"

desc "Cleans the build folder"
task "clean", ->
  jake.logger.log "Cleaning build folder"
  rmdir outputDir
  mkdir outputDir

task "compile_sass", async: true, ->
  jake.logger.log "Compiling SASS"

  files = glob sourceSassDir, "**/*.scss"
  mkdir outputCssDir

  jobs = files.map (filename) ->
    (done) ->
      sassFile = path.join sourceSassDir, filename
      cssFile = path.join(outputCssDir, filename).replace /\.scss$/, '.css'

      return done() if isDir(sassFile) or /theme_template/.test sassFile

      compileSass sassFile, (err, css) ->
        fs.writeFileSync cssFile, css
        done()

  async.parallel jobs, complete

task "copy", ->
  jake.logger.log "Copying files"

  copy baseDir, outputDir, "index.html"
  copy sourceDir, outputDir, "*-LICENSE"
  copy sourceJsDir, outputJsDir, "sh*.js"

  core    = path.join sourceJsDir, "shCore.js"
  xregexp = path.join componentsDir, "xregexp", "xregexp-all.js"

  fs.writeFileSync path.join(outputJsDir, "shCore.js"), fs.readFileSync(xregexp, "utf8") + fs.readFileSync(core, "utf8")

task "pack", ->
  jake.logger.log "Packing source files"

  core = path.join outputJsDir, "shCore.js"

  fs.writeFileSync core, compressJs core

  glob(outputCssDir, "**/*.css").forEach (file) ->
    file = path.join(outputCssDir, file)
    compressCss file, fs.readFileSync(file, "utf8"), (err, source) ->
      fs.writeFileSync file, source

task "add_header", ->
  jake.logger.log "Adding copyright header"

  files = glob(outputDir, "**/*.js").concat(glob(outputDir, "**.css"))

  files.forEach (file) ->
    file = path.join(outputDir, file)
    fs.writeFileSync file, variables.header + fs.readFileSync(file, "utf8")  unless isDir(file)

task "process_variables", ->
  process = (str) ->
    for key of variables
      str = str.replace("@" + key.toUpperCase() + "@", variables[key])
    str

  jake.logger.log "Processing variables"

  files = glob(outputDir, "**/*.js").concat(glob(outputDir, "**/*.css"))

  files.forEach (file) ->
    file = path.join(outputDir, file)
    fs.writeFileSync file, process(fs.readFileSync(file, "utf8")) unless isDir(file)

task "validate", ->
  jake.logger.log "Validating JavaScript files"

  context = {}
  coreFile = path.join(outputJsDir, "shCore.js")
  vm.runInNewContext fs.readFileSync(coreFile, "utf8"), context, coreFile

  glob(outputDir, "**.js").forEach (file) ->
    return  if /shCore/.test(file)

    file = path.join(outputDir, file)
    vm.runInNewContext fs.readFileSync(file, "utf8"), context, file

