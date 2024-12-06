const gulp = require('gulp'),
  browserSync = require('browser-sync'),
  // extender = require('gulp-html-extend'),
  // inline_base64 = require('gulp-inline-base64'),
  config = require('./gulp.config')(),
  sourcemaps = require('gulp-sourcemaps'),
  assetsToBase64 = require('./src/package/gulp-assets-to-base64'),
  // htmlValidator = require('./src/package/gulp-html-validator'),
  // jsHint = require('gulp-jshint'),
  // sassLint = require('gulp-sass-lint'),
  merge = require('merge-stream'),
  webpackStream = require('webpack-stream'),
  sass = require('gulp-sass')(require('sass'));

$ = require('gulp-load-plugins')({lazy: true});


function css() {
  let tasks = config.sass.composant.map(composant => {
    return gulp
      .src(config.sass.src + composant + '.scss')
      .pipe(sourcemaps.init())
      .pipe(sass().on('error', sass.logError))
      .pipe(assetsToBase64({
        baseDir: config.src,
        maxSize: 14 * 1024,
        debug: true
      }))
      .pipe($.autoprefixer('last 2 versions', 'safari 12', 'ios 12', 'android 6'))
      .pipe($.concat(composant + '.css'))
      .pipe(gulp.dest(config.sass.dest + composant))
      .pipe(sass().on('error', sass.logError))
      .pipe(assetsToBase64({
        baseDir: config.src + 'base64/',
        maxSize: 14 * 1024,
        debug: false
      }))
      .pipe($.autoprefixer('last 2 versions', 'safari 12', 'ios 12', 'android 6'))
      .pipe($.cleanCss())
      .pipe($.rename({suffix: '.min'}))
      .pipe(sourcemaps.write('./'))
      .pipe(gulp.dest(config.sass.dest + composant))
    // .pipe(browserSync.reload({stream: true}));
  });

  return merge(tasks);
}


gulp.task('build:css', css);


// Optimize JavaScript.
// --------------------------------------------------
function js() {
  let tasks = config.js.composant.map(composant => {
    return gulp
      .src(config.js.src + composant + '/index.js')
      // .pipe(webpackStream({
      //   entry: config.js.src + composant + '/index.js',
      //   output: {
      //     filename: composant + '.js',
      //   },
      //   mode: 'development',
      // }))
      .pipe(gulp.dest(config.js.dest + composant));
  });

  return merge(tasks);
}


gulp.task('build:js', js);


// Optimizes the images that exist.
// --------------------------------------------------
gulp.task('images:clean', function () {
  return gulp
    .src(config.img.dest)
    .pipe($.clean());
});

gulp.task('images:min', function () {
  return gulp
    .src(config.img.src)
    .pipe($.changed('images'))
    .pipe($.imagemin({
      // Lossless conversion to progressive JPGs
      progressive: true,
      // Interlace GIFs for progressive rendering
      interlaced: true
    }))
    .pipe(gulp.dest(config.img.dest))
    .pipe($.size({title: 'images'}));
});

gulp.task('images', gulp.series('images:clean', 'images:min'));


// Copy & clean fonts.
// --------------------------------------------------
gulp.task('fonts:copy', function () {
  return gulp
    .src(config.fonts.src)
    .pipe(gulp.dest(config.fonts.dest));
});

gulp.task('fonts:clean', function () {
  return gulp
    .src(config.fonts.dest)
    .pipe($.clean());
});

gulp.task('fonts', gulp.series('fonts:clean', 'fonts:copy'));


gulp.task('browser-sync', gulp.series('build:css', 'build:js', function () {
  browserSync({
    server: {
      baseDir: config.dist,
      injectChanges: true // this is new
    },
    port: 8080,
    open: false
  });
}));

// Optimize JavaScript & Sass styles.
// --------------------------------------------------
gulp.task('build', gulp.series('build:css', 'build:js'));


// Init project (only the first time).
// --------------------------------------------------
gulp.task('init', gulp.series('build', 'fonts:copy', 'images:min'));

// Deploy project > js + css + fonts + images.
// --------------------------------------------------
gulp.task('deploy', gulp.series('build', 'fonts', 'images'));
