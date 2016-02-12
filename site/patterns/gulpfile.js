var gulp   = require('gulp');
var concat = require('gulp-concat');
var rename = require('gulp-rename');
var uglify = require('gulp-uglify');
var cssmin = require('gulp-cssmin');
var sass   = require('gulp-sass');
var image  = require('gulp-image');

gulp.task('js', function() {

  return gulp.src([
      // array of js files. i.e.:
      // 'site/patterns/js/jquery/jquery.js',
      // 'site/patterns/gallery/fotorama/fotorama.js'
    ])
    .pipe(concat('index.js'))
    .pipe(gulp.dest('assets/js'))
    .pipe(rename('index.min.js'))
    .pipe(uglify())
    .pipe(gulp.dest('assets/js'));

});

gulp.task('css', function() {

  return gulp.src('site/patterns/site/site.scss')
    .pipe(sass().on('error', sass.logError))
    .pipe(rename('index.css'))
    .pipe(gulp.dest('assets/css'))
    .pipe(rename('index.min.css'))
    .pipe(cssmin())
    .pipe(gulp.dest('assets/css'));

});

gulp.task('images', function() {
  gulp.src('site/patterns/**/*.{jpg,gif,png,svg}')
    .pipe(image())
    .pipe(gulp.dest('assets/images'));
});

gulp.task('default', [
  'css',
  'js',
  'images'
]);

gulp.task('watch', ['default'], function() {
  gulp.watch('site/patterns/**/*.scss', ['css']);
  gulp.watch('site/patterns/**/*.js', ['js']);
  gulp.watch('site/patterns/**/*.{jpg,gif,png,svg}', ['images']);
});
