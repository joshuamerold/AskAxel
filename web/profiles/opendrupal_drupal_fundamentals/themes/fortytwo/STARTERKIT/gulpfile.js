/**
 * @file
 * Gulpfile for fortytwo.
 */

var gulp = require('gulp');
var $ = require('gulp-load-plugins')();
var del = require('del');
var autoprefixer = require('autoprefixer');

/**
 * @task sass-lint
 * Lint sass, abort calling task on error
 */
gulp.task('sass-lint', function () {
  return gulp.src('static/sass/**/*.s+(a|c)ss')
  .pipe($.sassLint({configFile: '.sass-lint.yml'}))
  .pipe($.sassLint.format())
  .pipe($.sassLint.failOnError());
});

gulp.task('sass-compile', ['sass-lint'], function () {
  // postCss plugins and processes
  var pcPlug = {
    autoprefixer: require('autoprefixer'),
    mqpacker: require('css-mqpacker'),
    flexbugs: require('postcss-flexbugs-fixes')
  };
  var pcProcess = [
    pcPlug.autoprefixer(),
    pcPlug.mqpacker(),
    pcPlug.flexbugs()
  ];

  var stream = gulp.src('static/sass/**/*.s+(a|c)ss') // Gets all files ending
  .pipe($.sourcemaps.init())
  .pipe($.sass())
  .pipe($.postcss(pcProcess))
  .pipe($.sourcemaps.write('maps'))
  .pipe(gulp.dest('static/css'))
  .on('error', $.util.log);

  if (config.enable_livereload && config.livereload_hard_refresh) {
    stream.pipe($.livereload());
  }
  
  return stream;
});

/**
 * @task js
 *
 */
gulp.task('js', function () {
  var stream = gulp.src(['static/js/**/*.js'])
  .pipe($.jscs())
  .pipe($.jscs.reporter())
  .pipe(gulp.dest('./static/js'))
  .on('error', $.util.log);
  
  if (config.enable_livereload && config.livereload_hard_refresh) {
    stream.pipe($.livereload());
  }

  return stream;
});

/**
 * @task clean
 * Clean the dist folder.
 */
gulp.task('clean', function () {
  return del(['static/css/*']);
});

/**
 * @task load-config
 * Load the local configuration.
 */
gulp.task('load-config', function() {
  try {
    console.log('Loading config.json. Change the values in gulp.config.json to suit your needs.');
    config = require('./gulp.config.json');
  } catch (error) {
    console.log('No local config.json found. Using the defaults.');
    console.log('Debug info: ' + error.code + ' => ' + error);
  }
});

/**
 * @task watch
 * Watch files and do stuff.
 */
gulp.task('watch', ['clean', 'sass-compile', 'js'], function () {
  gulp.watch('static/sass/**/*.+(scss|sass)', ['sass-compile']);
  gulp.watch('static/js/**/*.js', ['js']);

  if (config.enable_livereload) {
    console.log('Using live reload. Please enable your livereload browser plugin.');
    $.livereload.listen();

    gulp.watch('static/css/**/*.css', function(file) {
      $.livereload.changed(file.path);
    });

    gulp.watch('static/js/**/*.js', function(file){
      $.livereload.changed(file.path);
    });
  }
});

/**
 * @task default
 * Watch files and do stuff.
 */
gulp.task('default', ['load-config', 'watch']);

