export default function bundle(gulp, rootPath) {
  gulp.task('build', 'Builds distribution files to be used via `<script/>` tags. $ gulp build --brushes name1 name2', function (done) {
    const fs = require('fs');
    const gulpUtil = require('gulp-util');
    const ejs = require('ejs');
    const webpack = require('webpack');
    const glob = require('glob');

    const render = (filepath, context) => ejs.render(fs.readFileSync(filepath, 'utf8'), context);
    const availableBrushes = glob.sync(`${rootPath}/repos/brush-*`).map(path => path.match(/brush-(.*)$/)[1]);

    const argv = require('yargs')
      .demand('brushes')
      .describe('brushes', 'Comma separated list of brushes to be bundled.')
      .epilog(`Available brushes are "all" or ${availableBrushes.join(', ')}.`)
      .argv;

    let buildBrushes = (argv.brushes || '').toString().split(',');

    if (buildBrushes.length === 0 || buildBrushes[0] === 'true') {
      gulpUtil.log(gulpUtil.colors.red('Please specify at least one brush or "all".'));
      process.exit(-1);
    }

    if (buildBrushes.length === 1 && buildBrushes[0] === 'all') {
      buildBrushes = availableBrushes;
    }

    buildBrushes.forEach(brush => {
      if (availableBrushes.indexOf(brush) === -1) {
        gulpUtil.log(gulpUtil.colors.red(`Unknown brush "${brush}".`))
        process.exit(-1);
      }
    });

    const version = JSON.parse(fs.readFileSync(`${rootPath}/package.json`)).version;
    const banner = render(`${rootPath}/build/banner.ejs`, { version: version, date: (new Date).toUTCString() });
    const registerBrushes = render(`${rootPath}/build/bundle-register-brushes.ejs`, { buildBrushes });
    const core = render(`${rootPath}/src/core.js`, { registerBrushes });
    const customCorePath = `${rootPath}/src/core-custom.js`;

    fs.writeFileSync(customCorePath, core);

    webpack({
      entry: customCorePath,
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
        new webpack.optimize.UglifyJsPlugin({
          comments: false,
        }),
        new webpack.SourceMapDevToolPlugin({
          filename: 'syntaxhighlighter.js.map',
          append: '\n//# sourceMappingURL=[url]',
        }),
        new webpack.BannerPlugin(banner),
      ]
    }, function (err, stats) {
      fs.unlinkSync(customCorePath);

      if(err) throw err;
      gulpUtil.log(stats.toString({ colors: true }));
      done();
    });
  });
}
