import _gulp from 'gulp';
import gulpHelp from 'gulp-help';
import setupProject from './build/gulpfile/setup-project.js';
import build from './build/gulpfile/build.js';

const gulp = gulpHelp(_gulp, { hideDepsMessage: true });

setupProject(gulp, __dirname);
build(gulp, __dirname);
