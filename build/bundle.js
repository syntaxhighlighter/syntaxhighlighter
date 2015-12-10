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
  const Promise = require('songbird');

  let buildBrushes = (argv.brushes || '').toString().split(',');

  if (buildBrushes.length === 0 || buildBrushes[0] === 'true') {
    return Promise.reject(new BuildError('Please specify at least one brush or "all".'));
  }

  if (buildBrushes.length === 1 && buildBrushes[0] === 'all') {
    buildBrushes = availableBrushes;
  }

  return Promise.all(buildBrushes.map(brush => {
    if (availableBrushes.indexOf(brush) === -1) {
      return Promise.reject(new BuildError(`Unknown brush "${brush}".`));
    }

    return Promise.props({
      name: brush,
      sample: fs.promise.readFile(`${rootPath}/repos/brush-${brush}/sample.txt`, 'utf8'),
    });
  }));
}

function buildJavaScript(rootPath, argv, buildBrushes, version) {
  const fs = require('fs');
  const webpack = require('webpack');

  const banner = render(`${rootPath}/build/banner.ejs`, { version, date: (new Date).toUTCString() });
  const registerBrushes = render(`${rootPath}/build/bundle-register-brushes.ejs`, { buildBrushes });
  const core = render(`${rootPath}/src/core.js`, { registerBrushes });
  const corePath = `${rootPath}/src/core.js`;
  const backupCorePath = `${rootPath}/src/core.js.bak`;
  const config = {
    entry: `${rootPath}/src/index.js`,
    output: {
      path: `${rootPath}/dist`,
      filename: 'syntaxhighlighter.js'
    },
    module: {
      loaders: [
        {
          test: /\.js$/,
          exclude: /node_modules/,
          loaders: ['babel'],
        },
      ],
    },
    plugins: [
      new webpack.optimize.DedupePlugin(),
      new webpack.optimize.UglifyJsPlugin({ comments: false }),
      new webpack.SourceMapDevToolPlugin({
        filename: 'syntaxhighlighter.js.map',
        append: '\n//# sourceMappingURL=[url]',
      }),
      new webpack.BannerPlugin(banner),
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

function buildCSS(rootPath, theme, version) {
  const Promise = require('songbird');
  const fs = require('fs');
  const sass = require('node-sass');

  return fs.promise.readFile(`${rootPath}/repos/theme-${theme}/theme.scss`, 'utf8')
    .then(data => sass.promise.render({ data, includePaths: [`${rootPath}/node_modules/theme-base`] }))
    .then(results => fs.promise.writeFile(`${rootPath}/dist/theme.css`, results.css))
    ;
}

function copyHtml(rootPath, buildBrushes, version) {
  const fs = require('fs');
  const Promise = require('songbird');

  return fs.promise.writeFile(
    `${rootPath}/dist/index.html`,
    render(`${rootPath}/build/index.ejs`, { buildBrushes, version })
  );
}

export function bundle(rootPath, destPath, argv) {
  return Promise.all([
    getAvailableBrushes(rootPath),
    getVersion(rootPath),
  ])
  .then(function ([availableBrushes, version]) {
    argv = argv || require('yargs')
      .demand('brushes').describe('brushes', 'Comma separated list of brushes to be bundled.')
      .default('theme', 'default').describe('theme', 'Name of the CSS theme you want to use.')
      .epilog(`Available brushes are "all" or ${availableBrushes.join(', ')}.`)
      .argv;

    return getBuildBrushes(rootPath, argv, availableBrushes)
      .then(function (buildBrushes) {
        return Promise.all([
          buildJavaScript(rootPath, argv, buildBrushes, version),
          buildCSS(rootPath, argv.theme, version),
          copyHtml(rootPath, buildBrushes, version),
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

          gulpUtil.log(`Theme: ${theme}`);
          gulpUtil.log(`Brushes: ${buildBrushes.map(brush => brush.name).join(', ')}`);
          gulpUtil.log(stats.toString({ colors: true }));

          done();
        })
        .catch(err => gulpUtil.log(gulpUtil.colors.red(err.isBuild ? err.message : err.stack)))
    }
  );
}
