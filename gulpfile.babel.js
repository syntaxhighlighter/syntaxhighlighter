import _gulp from 'gulp';
import gulpHelp from 'gulp-help';
import setupProject from './build/gulpfile/setup-project.js';

const gulp = gulpHelp(_gulp, { hideDepsMessage: true });

setupProject(gulp, __dirname);
