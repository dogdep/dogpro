var gulp = require('gulp');
var expect = require('gulp-expect-file');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var less = require('gulp-less');
var ngAnnotate = require('gulp-ng-annotate');
var sourcemaps = require('gulp-sourcemaps');
var gutil = require('gulp-util');
var ini = require('ini');
var fs = require('fs');
var config = ini.parse(fs.readFileSync('./.env', 'utf-8'));
var replace = require('gulp-token-replace');
var templateCache = require('gulp-templatecache');

var paths = {
    vendor_scripts: [
        "resources/vendor/angular/angular.min.js",
        "resources/vendor/angular-ui-router/release/angular-ui-router.min.js",
        "resources/vendor/moment/min/moment.min.js",
        "resources/vendor/angular-moment/angular-moment.min.js",
        "resources/vendor/angular-bootstrap/ui-bootstrap-tpls.min.js",
        "resources/vendor/angular-resource/angular-resource.min.js",
        "resources/vendor/angular-animate/angular-animate.min.js",
        "resources/vendor/AngularJS-Toaster/toaster.min.js",
        'resources/vendor/angular-loading-bar/build/loading-bar.min.js',
        "resources/vendor/ng-sortable/dist/ng-sortable.min.js",
        "resources/vendor/angular-jwt/dist/angular-jwt.min.js",
        "resources/vendor/ifvisible.js/src/ifvisible.min.js",
        "resources/vendor/yamljs/bin/yaml.js",
        "resources/vendor/ngSmoothScroll/angular-smooth-scroll.min.js",
        "resources/vendor/angular-ui-router-anim-in-out/anim-in-out.js",
        "resources/vendor/HTML5-Desktop-Notifications2/desktop-notify.js",
        "resources/vendor/angular-web-notification/angular-web-notification.js"
    ],
    templates: [
        'public/templates/**/*.html'
    ],
    scripts: [
        'resources/assets/js/**/*.js'
    ],
    styles: [
        'resources/assets/less/*.less'
    ],
    vendor_fonts: [
        'resources/vendor/fontawesome/fonts/*'
    ],
    images: [
        'resources/assets/img/*'
    ]
};

gulp.task('templates', [], function () {
    return gulp.src(paths.templates)
        .pipe(templateCache({
            output: 'templates.js',
            strip: 'public',
            moduleName: 'dp',
            minify: {}
        }))
        .pipe(gulp.dest('public/build/js'));
});

gulp.task('vendor_scripts', [], function () {
    return gulp.src(paths.vendor_scripts)
        .pipe(expect(paths.vendor_scripts))
        .pipe(concat('vendor.js'))
        .pipe(gulp.dest('public/build/js'));
});

gulp.task('scripts', [], function () {
    return gulp.src(paths.scripts)
        .pipe(expect(paths.scripts))
        .pipe(replace({global: config, prefix: '{$TOKEN:', suffix: '}'}))
        .pipe(ngAnnotate())
        .pipe(sourcemaps.init())
        .pipe(gutil.env.production ? uglify() : gutil.noop())
        .pipe(concat('app.js'))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest('public/build/js'));
});

gulp.task('styles', [], function () {
    return gulp.src('resources/assets/less/app.less')
        .pipe(sourcemaps.init())
        .pipe(less())
        .pipe(concat('app.css'))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest('public/build'));
});

gulp.task('vendor_fonts', [], function () {
    return gulp.src(paths.vendor_fonts)
        .pipe(gulp.dest('public/build/fonts'));
});

gulp.task('images', [], function () {
    return gulp.src(paths.images)
        .pipe(gulp.dest('public/build/img'));
});


gulp.task('watch', ['default'], function () {
    gulp.watch(paths.templates, ['templates']);
    gulp.watch(paths.vendor_scripts, ['vendor_scripts']);
    gulp.watch(paths.styles, ['styles']);
    gulp.watch(paths.scripts, ['scripts']);
});

gulp.task('default', ['vendor_scripts', 'styles', 'vendor_fonts', 'images', 'scripts', 'templates']);
