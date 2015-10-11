var gulp = require('gulp');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var less = require('gulp-less');
var expect = require('gulp-expect-file');

var paths = {
    vendor_scripts: [
        'bower_components/jquery/dist/jquery.min.js',
        'bower_components/highlightjs/highlight.pack.min.js',
        'bower_components/owl-carousel/owl-carousel/owl.carousel.min.js'
    ],
    scripts: [
        'scripts/**/*.js'
    ],
    styles: [
        'less/*.less'
    ],
    vendor_fonts: [
        'bower_components/font-awesome/fonts/*'
    ]
};

gulp.task('vendor_scripts', [], function () {
    return gulp.src(paths.vendor_scripts)
        .pipe(expect(paths.vendor_scripts))
        .pipe(concat('vendor.js'))
        .pipe(gulp.dest('build'));
});

gulp.task('scripts', [], function () {
    return gulp.src(paths.scripts)
        .pipe(expect(paths.scripts))
        .pipe(uglify())
        .pipe(concat('app.js'))
        .pipe(gulp.dest('build'));
});

gulp.task('styles', [], function () {
    return gulp.src('less/main.less')
        .pipe(less())
        .pipe(concat('app.css'))
        .pipe(gulp.dest('build'));
});

gulp.task('vendor_fonts', [], function () {
    return gulp.src(paths.vendor_fonts)
        .pipe(gulp.dest('build/fonts'));
});

gulp.task('watch', ['default'], function () {
    gulp.watch(paths.vendor_scripts, ['vendor_scripts']);
    gulp.watch(paths.styles, ['styles']);
    gulp.watch(paths.scripts, ['scripts']);
});

gulp.task('default', ['vendor_scripts', 'styles', 'vendor_fonts', 'scripts']);
