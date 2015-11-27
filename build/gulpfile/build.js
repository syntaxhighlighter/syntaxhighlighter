import gulpUtil from 'gulp-util';
import fs from 'fs';
import webpack from 'webpack';
import ejs from 'ejs';

export default function (gulp, rootPath) {
  const REPOS_CACHE = `${rootPath}/.projects-cache.json`;
  const REPOS_DIR = `${rootPath}/repos`;

  gulp.task('build', 'Builds distribution files to be used via `<script/>` tags. $ gulp build brush-a brush-b', function (done) {
    const version = JSON.parse(fs.readFileSync(`${__dirname}/../../package.json`)).version;
    let banner = fs.readFileSync(`${__dirname}/../banner.ejs`, 'utf8');

    banner = ejs.render(banner, { version: version, date: (new Date).toUTCString() });

    webpack({
      entry: `${rootPath}/src/core.js`,
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
      if(err) throw err;
      gulpUtil.log(stats.toString());
      done();
    });
  });
}
