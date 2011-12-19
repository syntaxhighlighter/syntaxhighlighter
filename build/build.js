var path      = require('path'),
	fs        = require('fs'),
	minimatch = require('minimatch'),
	less      = require('less'),
	vm        = require('vm')
	;

desc('Default task, builds SyntaxHighlighter');
task('default', [ 'build' ]);

var baseDir      = __dirname,
	sourceDir    = path.join(baseDir, '..'),
	sourceJsDir  = path.join(sourceDir, 'scripts'),
	sourceCssDir = path.join(sourceDir, 'styles'),
	outputDir    = path.join(baseDir, 'output'),
	outputJsDir  = path.join(outputDir, 'scripts'),
	outputCssDir = path.join(outputDir, 'styles'),
	includesDir  = path.join(baseDir, 'includes'),
	variables    = getVars(includesDir)
	;

variables.version = '3.0.83';
variables.date    = new Date().toUTCString();
variables.about   = variables.about.replace(/\n|\t/g, '').replace(/"/g, '\\\"');

task('build', 'clean copy pack add_header process_variables validate'.split(/ /g), function()
{
	console.log('DONE');
});

task('clean', function()
{
	console.log('Cleaning build folder');

	rmdir(outputDir);
	mkdir(outputDir);
});

task('copy', function()
{
	console.log('Copying files');

	//
	// Copy all files
	//
	copy(baseDir, outputDir, 'index.html');
	copy(sourceDir, outputDir, '*-LICENSE');
	copy(sourceJsDir, outputJsDir, 'sh*.js');
	copy(sourceCssDir, outputCssDir, '**.css');

	// 
	// Append XRegExp to the core
	//
	var core    = path.join(sourceJsDir, 'shCore.js'),
		xregexp = path.join(sourceJsDir, 'XRegExp.js')
		;

	fs.writeFileSync(
		path.join(outputJsDir, 'shCore.js'),
		fs.readFileSync(xregexp, 'utf8') + fs.readFileSync(core, 'utf8')
	);
});

task('pack', function()
{
	console.log('Packing source files');

	var core = path.join(outputJsDir, 'shCore.js');

	fs.writeFileSync(core, compressJs(fs.readFileSync(core, 'utf8')));

	glob(outputCssDir, '**.css').forEach(function(file)
	{
		file = path.join(outputCssDir, file);

		compressCss(file, fs.readFileSync(file, 'utf8'), function(err, source)
		{
			fs.writeFileSync(file, source);
		});
	});
});

task('add_header', function()
{
	console.log('Adding copyright header');

	var files = glob(outputDir, '**.js').concat(glob(outputDir, '**.css'));

	files.forEach(function(file)
	{
		file = path.join(outputDir, file);

		if(!isDir(file))
			fs.writeFileSync(file, variables.header + fs.readFileSync(file, 'utf8'));
	});
});

task('process_variables', function()
{
	console.log('Processing variables');

	function process(str)
	{
		for(var key in variables)
			str = str.replace('@' + key.toUpperCase() + '@', variables[key]);

		return str;
	}

	var files = glob(outputDir, '**.js').concat(glob(outputDir, '**.css'));

	files.forEach(function(file)
	{
		file = path.join(outputDir, file);

		if(!isDir(file))
			fs.writeFileSync(file, process(fs.readFileSync(file, 'utf8')));
	});
});

task('validate', function()
{
	console.log('Validating JavaScript files');

	var context  = {},
		coreFile = path.join(outputJsDir, 'shCore.js')
		;

	// first run shCore and create proper context so that other files won't error out falsly
	vm.runInNewContext(fs.readFileSync(coreFile, 'utf8'), context, coreFile);

	glob(outputDir, '**.js').forEach(function(file)
	{
		if(/shCore/.test(file))
			return;

		file = path.join(outputDir, file);
		vm.runInNewContext(fs.readFileSync(file, 'utf8'), context, file);
	});
});

function compressCss(file, source, callback)
{
	var parser = new less.Parser({
		paths        : [ path.dirname(file) ],
		optimization : 2
	});

	parser.parse(source, function(err, tree)
	{
		callback(err, tree.toCSS({ compress : true }));
	});
}

function compressJs(source)
{
	var parser = require('uglify-js').parser,
		uglify = require('uglify-js').uglify,
		opts   = {},
		ast    = parser.parse(source)
		;

	ast = uglify.ast_mangle(ast, opts);
	ast = uglify.ast_squeeze(ast);

	return uglify.gen_code(ast);
}

function copy(src, dest, pattern)
{
	glob(src, pattern).forEach(function(file)
	{
		mkdir(path.join(dest, path.dirname(file)));

		var sourceFile = path.join(src, file);

		if(!isDir(sourceFile))
		{
			var buf = fs.readFileSync(sourceFile);
			fs.writeFileSync(path.join(dest, file), buf);
		}
	});
}

function isDir(dir)
{
	return fs.statSync(dir).isDirectory();
}

function mkdir(dir)
{
	if(path.existsSync(dir))
		return;

	var parts = dir.split('/');

	dir = '/';

	for(var i = 0; i < parts.length; i++)
	{
		dir = path.join(dir, parts[i]);

		if(!path.existsSync(dir))
			fs.mkdirSync(dir);
	}
}

function rmdir(dir)
{
	if(!path.existsSync(dir))
		return;

	glob(dir, '**').reverse().forEach(function(file)
	{
		file = path.join(dir, file);

		try { fs.unlinkSync(file); }
		catch(e) { fs.rmdirSync(file); }
	});

	fs.rmdirSync(dir);
}

function glob(dir, pattern)
{
	var result = [];

	if(!path.existsSync(dir))
		return result;

	result = fs.readdirSync(dir);

	for(var i = 0; i < result.length; i++)
	{
		var subdir = path.join(dir, result[i]);

		if(fs.lstatSync(subdir).isDirectory())
		{
			var subItems = glob(subdir, pattern).map(function(item) { return path.join(result[i], item); }),
				left     = result.slice(0, i),
				right    = result.slice(i)
				;

			result = left.concat(subItems).concat(right);
			i += subItems.length;
		}
	}

	result = result.filter(minimatch.filter(pattern, { dot : true }));
	result.sort();

	return result;
}

function getVars(dir)
{
	var files  = glob(dir, '*'),
		result = {},
		file, varName, i
		;
	
	for(i = 0; i < files.length; i++)
	{
		file            = files[i];
		varName         = path.basename(file, path.extname(file));
		result[varName] = fs.readFileSync(path.join(dir, file), 'utf8');
	}

	return result;
}
