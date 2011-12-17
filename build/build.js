var path = require('path'),
	jake = require('jake')
	;

var FileList = jake.FileList;

desc('Default task, builds SyntaxHighlighter');
task('default', [ 'build' ]);

var baseDir             = __dirname,
	sourceDir           = path.join(baseDir, '..'),
	sourceScriptsDir    = path.join(sourceDir, 'scripts'),
	sourceStylesDir     = path.join(sourceDir, 'styles'),
	includesDir         = path.join(baseDir, 'includes'),
	outputDir           = path.join(baseDir, 'output'),
	outputSrcScriptsDir = path.join(outputDir, 'src'),
	outputBinScriptsDir = path.join(outputDir, 'scripts'),
	outputStylesDir     = path.join(outputDir, 'styles'),
	fileHeader          = path.join(includesDir, 'header.txt'),
	fileAbout           = path.join(includesDir, 'about.html'),
	variables           = getIncludes(includesDir)
	;

task('build', 'clean copy apply_variables pack apply_header'.split(/ /g), function()
{
	console.log('DONE');
});

task('clean', function()
{
	console.log('Cleaning build folder');
	var files = new FileList(path.join(outputDir, '**/*')).toArray();
	console.log(files);
	// <delete dir="${output.dir}" />
});

task('copy', function()
{
});

task('apply_variables', function()
{
});

task('pack', function()
{
});

task('apply_header', function()
{
});

function getIncludes(dir)
{
	var files = new FileList(path.join(dir, '**/*')).toArray(),
		result = {}
		;
	
	for(var i = 0; i < files.length; i++)
	{
		var file = files[i];
		result[path.basename(file, path.extname(file))] = fs.readFileSync(file);
	}

	return result;
}
