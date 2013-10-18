path      = require 'path'
async     = require 'async'
fs        = require 'fs'
minimatch = require 'minimatch'
less      = require 'less'
vm        = require 'vm'
shelljs   = require 'shelljs'
ejs       = require 'ejs'
uglify    = require 'uglify-js'
sass      = require 'node-sass'

compressCss = (source, callback) ->
  parser = new less.Parser optimization: 2
  parser.parse source, (err, tree) ->
    callback err, tree.toCSS(compress: true)

compressJs = (source, callback) ->
  {code, map} = uglify.minify source, fromString: true
  callback null, code

compileSass = (file, callback) ->
  data = readFile file
  sass.render data, callback, include_paths: [sourceSassDir], output_style: null

isDirectory = (dir) ->
  fs.existsSync(dir) and fs.statSync(dir).isDirectory()

readFile = (filename) ->
  fs.readFileSync filename, 'utf8'

writeFile = (filename, content) ->
  fs.writeFileSync filename, content, 'utf8'

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
    result[varName] = readFile file

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
variables.version = JSON.parse(readFile path.resolve baseDir, '../package.json').version
variables.date    = new Date().toUTCString()
variables.about   = variables.about.replace(/\r|\n|\t/g, "").replace(/"/g, "\\\"")

module.exports = (grunt) ->
  grunt.registerTask 'build', 'clean compile_sass copy_misc_files build_core pack validate add_header'.split(/\s/g)

  grunt.registerTask 'clean', 'Cleans the build folder', ->
    shelljs.rm '-fr', outputDir
    shelljs.mkdir '-p', outputJsDir, outputCssDir

  grunt.registerTask 'compile_sass', ->
    files = findFilesIn sourceSassDir, "**/*.scss"

    jobs = files.map (sassFile) ->
      (done) ->
        cssFile = path.join(outputCssDir, path.basename sassFile).replace /\.scss$/, '.css'

        # skip the theme template
        return done() if /theme_template/.test sassFile

        compileSass sassFile, (err, css) ->
          writeFile cssFile, css
          done()

    async.parallel jobs, @async()

  grunt.registerTask 'copy_misc_files', ->
    shelljs.cp "#{baseDir}/index.html", outputDir
    shelljs.cp "#{sourceJsDir}/sh*.js", outputJsDir

  grunt.registerTask 'build_core', ->
    variables.about = ejs.render variables.about, variables
    corePath        = path.join outputJsDir, 'shCore.js'
    xregexpSource   = readFile path.join(componentsDir, 'xregexp', 'src', 'xregexp.js')
    coreSource      = ejs.render readFile(corePath), variables

    writeFile corePath, xregexpSource + coreSource

  grunt.registerTask 'pack', ->
    addMinExt = (filename) ->
      ext = path.extname filename
      filename = path.basename filename, ext
      "#{filename}.min#{ext}"

    # this could be changed to minify all JS files, not just core
    findFilesIn(outputJsDir, '**/shCore.js').forEach (file) ->
      compressJs readFile(file), (err, source) ->
        writeFile path.join(outputJsDir, path.basename(addMinExt file)), source

    findFilesIn(outputCssDir, '**/*.css').forEach (file) ->
      compressCss readFile(file), (err, source) ->
        writeFile file, source

  grunt.registerTask 'add_header', ->
    files  = findFilesIn(outputDir, "**/*.js").concat(findFilesIn(outputDir, '**.css'))
    header = ejs.render variables.header, variables

    files.forEach (file) ->
      writeFile file, header + readFile file

  grunt.registerTask 'validate', ->
    context = {}
    coreFile = path.join(outputJsDir, 'shCore.js')
    vm.runInNewContext readFile(coreFile), context, coreFile

    findFilesIn(outputDir, "**.js").forEach (file) ->
      return  if /shCore/.test(file)
      vm.runInNewContext readFile(file), context, file

