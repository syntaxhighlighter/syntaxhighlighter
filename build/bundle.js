export default function bundle(gulp, rootPath) {
  function render(filepath, context) {
    const ejs = require('ejs');
    const fs = require('fs');
    return ejs.render(fs.readFileSync(filepath, 'utf8'), context);
  }

  function getVersion() {
    const fs = require('fs');

    return fs.promise.readFile(`${rootPath}/package.json`)
      .then(JSON.parse)
      .then(({version}) => version);
  }

  function getAvailableBrushes() {
    const glob = require('glob');

    return glob.promise(`${rootPath}/repos/brush-*`)
      .then(brushes => brushes.map(path => path.match(/brush-(.*)$/)[1]));
  }

  function getBuildBrushes(argv, availableBrushes) {
    const fs = require('fs');
    const gulpUtil = require('gulp-util');
    const Promise = require('songbird');

    let buildBrushes = (argv.brushes || '').toString().split(',');

    if (buildBrushes.length === 0 || buildBrushes[0] === 'true') {
      gulpUtil.log(gulpUtil.colors.red('Please specify at least one brush or "all".'));
      process.exit(-1);
    }

    if (buildBrushes.length === 1 && buildBrushes[0] === 'all') {
      buildBrushes = availableBrushes;
    }

    return Promise.all(buildBrushes.map(brush => {
      if (availableBrushes.indexOf(brush) === -1) {
        gulpUtil.log(gulpUtil.colors.red(`Unknown brush "${brush}".`))
        process.exit(-1);
      }

      return Promise.props({
        name: brush,
        sample: fs.promise.readFile(`${rootPath}/repos/brush-${brush}/sample.txt`, 'utf8'),
      });
    }));
  }

  function buildJavaScript(argv, buildBrushes, version) {
    const fs = require('fs');
    const gulpUtil = require('gulp-util');
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

    gulpUtil.log(`Brushes: ${buildBrushes.map(({name}) => name).join(', ')}`);

    return fs.promise.rename(corePath, backupCorePath)
      .then(() => fs.promise.writeFile(corePath, core))
      .then(() => webpack.promise(config))
      .then(stats => gulpUtil.log(stats.toString({ colors: true })))
      .then(() => fs.promise.unlink(corePath))
      .then(() => fs.promise.rename(backupCorePath, corePath));
  }

  function buildCSS(argv, version) {
    const Promise = require('songbird');
    const fs = require('fs');
    const sass = require('node-sass');
    const gulpUtil = require('gulp-util');
    const {theme} = argv;

    gulpUtil.log(`Theme : ${theme}`);

    return fs.promise.readFile(`${rootPath}/repos/theme-${theme}/theme.scss`, 'utf8')
      .then(data => sass.promise.render({ data, includePaths: [`${rootPath}/node_modules/theme-base`] }))
      .then(results => fs.promise.writeFile(`${rootPath}/dist/theme.css`, results.css))
      ;
  }

  function copyHtml(buildBrushes, version) {
    const fs = require('fs');
    const Promise = require('songbird');

    return fs.promise.writeFile(
      `${rootPath}/dist/index.html`,
      render(`${rootPath}/build/index.ejs`, { buildBrushes, version })
    );
  }

  gulp.task(
    'build',
    'Builds distribution files to be used via `<script/>` tags. $ gulp build --brushes value1,value2 --theme value',
    function (done) {
      Promise.all([
        getAvailableBrushes(),
        getVersion(),
      ])
      .then(function ([availableBrushes, version]) {
        const argv = require('yargs')
          .demand('brushes').describe('brushes', 'Comma separated list of brushes to be bundled.')
          .default('theme', 'default').describe('theme', 'Name of the CSS theme you want to use.')
          .epilog(`Available brushes are "all" or ${availableBrushes.join(', ')}.`)
          .argv;

        return getBuildBrushes(argv, availableBrushes)
          .then(buildBrushes => Promise.all([
            buildJavaScript(argv, buildBrushes, version),
            buildCSS(argv, version),
            copyHtml(buildBrushes, version)
          ]))
          .then(() => done());
      })
      .catch(err => console.error(err.stack));
    }
  );
}
