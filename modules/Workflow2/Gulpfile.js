var gulp = require('gulp');
var autoprefixer = require('gulp-autoprefixer');
var cleanCSS = require('gulp-clean-css');
var concat = require('gulp-concat');
var jshint = require('gulp-jshint');
var strip = require('gulp-strip-comments');
var uglify = require('gulp-uglify');
var footer = require('gulp-footer');
var header = require('gulp-header');
var sourcemaps = require('gulp-sourcemaps');
var less = require('gulp-less');
var LessAutoprefix = require('less-plugin-autoprefix');
var autoprefix = new LessAutoprefix({ browsers: ['last 2 version', 'safari 5', 'ie 10', 'opera 12.1', 'ios 9'] });

gulp.task('watch', function(){
    gulp.watch('../../layouts/v7/modules/Settings/Workflow2/resources/less/Workflow2.less', gulp.series('backend-less'));
    gulp.watch('../../layouts/v7/modules/Workflow2/resources/css_src/*.css', gulp.series('frontend-css'));
    gulp.watch('./js-templates/*.jst', gulp.series('js_templates_frontend'));
});

gulp.task('frontend-css', function() {
    return gulp.src('../../layouts/v7/modules/Workflow2/resources/css_src/*.css')
        .pipe(autoprefixer('last 2 version', 'safari 5', 'ie 8', 'ie 9', 'opera 12.1', 'ios 6', 'android 4'))
        .pipe(cleanCSS({compatibility: 'ie8'}))
        .pipe(gulp.dest('../../layouts/v7/modules/Workflow2/resources/css/'));
});

gulp.task('backend-less', function() {
    return gulp.src('../../layouts/v7/modules/Settings/Workflow2/resources/less/Workflow2.less')
        .pipe(less({
            plugins: [autoprefix]
        }))
        .pipe(cleanCSS({compatibility: 'ie10'}))
        .pipe(gulp.dest('../../layouts/v7/modules/Settings/Workflow2/resources/'));
});

gulp.task('js_templates_frontend', function() {
    return gulp.src(['./js-templates/Main.jst', './js-templates/RedooUtils.jst', './js-templates/_*.jst' ])
        .pipe(concat('./frontend.js', {newLine: ';'}))
        .pipe(header("(function () {\n"))
        .pipe(sourcemaps.write())
        .pipe(footer("}())\n/** HANDLER START **/"))
        //.pipe(strip())
        // .pipe(uglify())

        .pipe(gulp.dest('./js'));
});

gulp.task('js_templates_backend', function() {
    return gulp.src(['../../modules/Settings/Workflow2/views/resources/js-templates/Main.jst', '../../modules/Settings/Workflow2/views/resources/js-templates/ElementSelection.jst', '../../modules/Settings/Workflow2/views/resources/js-templates/Utils.jst'])
        .pipe(concat('./Workflow2.js', {newLine: ';'}))
        //.pipe(strip())
        //.pipe(uglify())
        //.pipe(jslint())
        //.pipe(jslint.reporter('default'))
        .pipe(gulp.dest('../../modules/Settings/Workflow2/views/resources/'));

});
