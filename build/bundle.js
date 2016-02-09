class BuildError extends Error {
  constructor(message) {
    super(message);
    this.isBuild = true;
  }
}

function render(filepath, context) {
  const ejs = require('ejs');
  const fs = require('fs');
  return ejs.render(fs.readFileSync(filepath, 'utf8'), context);
}

function getVersion(rootPath) {
  const fs = require('fs');

  return fs.promise.readFile(`${rootPath}/package.json`)
    .then(JSON.parse)
    .then(({version}) => version);
}

function getAvailableBrushes(rootPath) {
  const glob = require('glob');

  return glob.promise(`${rootPath}/repos/brush-*`)
    .then(brushes => brushes.map(path => path.match(/brush-(.*)$/)[1]));
}

function getBuildBrushes(rootPath, argv, availableBrushes) {
  const fs = require('fs');
  const path = require('path');
  const Promise = require('songbird');

  if (!argv.brushes) {
    return Promise.resolve([]);
  }

  let buildBrushes = (argv.brushes || '').toString().split(',');

  if (buildBrushes.length === 0 || buildBrushes[0] === 'true') {
    return Promise.reject(new BuildError('Please specify at least one brush or "all".'));
  }

  if (buildBrushes.length === 1 && buildBrushes[0] === 'all') {
    buildBrushes = availableBrushes;
  }

  return Promise.all(buildBrushes.map(function (name) {
    let requirePath = path.resolve(process.cwd(), name);
    let sample;

    return fs.promise.stat(requirePath)
      // handle brushes by full file path
      .then(
        () => fs.promise.readFile(`${path.dirname(requirePath)}/sample.txt`)
          .then(content => sample = content)
          .catch(() => null)
          .then(() => requirePath = path.relative(`${rootPath}/src`, requirePath))
      )

      // handle brushes by name only
      .catch(function () {
        if (availableBrushes.indexOf(name) === -1) {
          return Promise.reject(new BuildError(`Unknown brush "${name}".`));
        }

        requirePath = `brush-${name}`;

        return fs.promise.readFile(`${rootPath}/repos/brush-${name}/sample.txt`, 'utf8')
          .then(content => sample = content)
          .catch(() => null);
      })

      .then(() => sample = sample || 'no sample.txt found')
      .then(() => Promise.props({name, requirePath, sample}));
  }));
}

function buildJavaScript(rootPath, outputPath, buildBrushes, version, compat) {
  const fs = require('fs');
  const webpack = require('webpack');

  const banner = render(`${rootPath}/build/templates/banner.js.ejs`, { version, date: (new Date).toUTCString() });
  const registerBrushes = render(`${rootPath}/build/templates/bundle-register-brushes.js.ejs`, { buildBrushes });
  const core = render(`${rootPath}/src/core.js`, { registerBrushes });
  const corePath = `${rootPath}/src/core.js`;
  const backupCorePath = `${rootPath}/src/core.js.bak`;
  const config = {
    entry: `${rootPath}/src/index.js`,
    output: {
      path: outputPath,
      filename: 'syntaxhighlighter.js'
    },
    externals: [
      'shCore'
    ],
    resolveLoader: {
      modulesDirectories: ['node_modules', 'build'],
    },
    module: {
      loaders: [
        {
          test: /\.js$/,
          loaders: ['babel', 'brush-v3'],
        },
      ],
    },
    plugins: [
      new webpack.optimize.DedupePlugin(),
      // new webpack.optimize.UglifyJsPlugin({ comments: false }),
      new webpack.BannerPlugin(banner),
      new webpack.SourceMapDevToolPlugin({
        filename: 'syntaxhighlighter.js.map',
        append: '\n//# sourceMappingURL=[url]',
      }),
      new webpack.DefinePlugin({
        COMPAT: compat === true,
      }),
    ]
  };

  return fs.promise.rename(corePath, backupCorePath)
    .then(() => fs.promise.writeFile(corePath, core))
    .then(() => webpack.promise(config))
    .then(stats =>
      fs.promise.unlink(corePath)
        .then(() => fs.promise.rename(backupCorePath, corePath))
        .then(() => stats)
    );
}

function buildCSS(rootPath, outputPath, theme, version) {
  const Promise = require('songbird');
  const fs = require('fs');
  const sass = require('node-sass');

  if (!theme) return;

  return fs.promise.stat(theme)
    .then(() => theme, () => `${rootPath}/repos/theme-${theme}/theme.scss`)
    .then(path => fs.promise.readFile(path, 'utf8'))
    .then(data => sass.promise.render({ data, includePaths: [`${rootPath}/node_modules/theme-base`] }))
    .then(results => fs.promise.writeFile(`${outputPath}/theme.css`, results.css))
    ;
}

function copyHtml(rootPath, outputPath, buildBrushes, version) {
  const fs = require('fs');
  const Promise = require('songbird');

  return fs.promise.writeFile(
    `${outputPath}/index.html`,
    render(`${rootPath}/build/templates/index.html.ejs`, { buildBrushes, version })
  );
}

export function bundle(rootPath, destPath, argv) {
  return Promise.all([
    getAvailableBrushes(rootPath),
    getVersion(rootPath),
  ])
  .then(function ([availableBrushes, version]) {
    argv = argv || require('yargs')
      .describe('brushes', 'Comma separated list of brush names or paths to be bundled.')
      .describe('theme', 'Name or path of the CSS theme you want to use.')
      .describe('compat', 'Will include v3 brush compatibility feature. See http://bit.ly/1KCaUq6 for complete details.')
      .default('output', `${rootPath}/dist`).describe('output', 'Output folder for dist files.')
      .epilog(`Available brushes are "all" or ${availableBrushes.join(', ')}.\n\nYou may also pass paths to brush JavaScript files and theme SASS files.`)
      .help('help')
      .argv;

    return getBuildBrushes(rootPath, argv, availableBrushes)
      .then(function (buildBrushes) {
        return Promise.all([
          buildJavaScript(rootPath, argv.output, buildBrushes, version, argv.compat),
          buildCSS(rootPath, argv.output, argv.theme, version),
          copyHtml(rootPath, argv.output, buildBrushes, version),
        ])
        .then(([stats]) => ({ theme: argv.theme, stats, buildBrushes }));
      });
  });
}

export default function (gulp, rootPath) {
  gulp.task(
    'build',
    'Builds distribution files to be used via `<script/>` tags. $ gulp build --brushes value1,value2 --theme value',
    function (done) {
      const gulpUtil = require('gulp-util');

      bundle(rootPath, null)
        .then(function ({ theme, stats, buildBrushes }) {
          const gulpUtil = require('gulp-util');

          if (theme) gulpUtil.log(`Theme: ${theme}`);
          if (buildBrushes) gulpUtil.log(`Brushes: ${buildBrushes.map(brush => brush.name).join(', ')}`);
          gulpUtil.log(stats.toString({ colors: true }));

          done();
        })
        .catch(err => gulpUtil.log(gulpUtil.colors.red(err.isBuild ? err.message : err.stack)))
    }
  );
}
