const { watch, src, dest, series } = require("gulp"),
    webpack = require('webpack-stream'),
	merge = require('merge-stream'),
	clean = require('gulp-clean'),
    header = require('gulp-header'),
    fs = require('fs'),
	uglify = require('gulp-terser'),
	concat = require('gulp-concat'),
    gulpif = require('gulp-if'),
    useref = require('gulp-useref'),
    sass = require('gulp-sass'),
	minifyCss = require('gulp-clean-css'),
	composer = require("gulp-composer");

sass.compiler = require('node-sass');

let deletedist = function () {
    return src('v5', {
			read: false,
			allowEmpty: true})
        .pipe(clean());
}

let composerinstall = function () {
    return composer({
		"self-install": true,
		"async": false
    });
}

let nodejs = function () {
    return src(['./dev/scripts.js'])
        .pipe(webpack({
            mode: 'production',
            output: {
                filename: 'ol.js'
            }
        }))
        .pipe(header(fs.readFileSync('./dev/copyright.txt', 'utf8')))
        .pipe(dest('v5/js'));
}
let adminjs = function () {
    return src('dev/js/admin/*.js')
		.pipe(concat('admin.min.js'))
		.pipe(uglify({
            mangle: false,
            ecma: 6
        }))
        .pipe(header(fs.readFileSync('./dev/copyright.txt', 'utf8')))
        .pipe(dest('v5/js'));
}
let buildjs = function () {
    return src('dev/js/*.js')
        .pipe(uglify({
            mangle: false,
            ecma: 6
        }))
        .pipe(header(fs.readFileSync('./dev/copyright.txt', 'utf8')))
        .pipe(dest('v5/js'));
}
let buildcss = function () {
    return src('dev/css/styles.scss')
        .pipe(sass())
        .pipe(gulpif('*.css', minifyCss()))
        .pipe(dest('v5/css/'));
}


let movephp = function () {
    return src('dev/**/*.php')
        .pipe(useref())
        .pipe(dest('v5/'));
}
let movehtml = function () {
    return src('dev/**/*.html')
        .pipe(useref())
        .pipe(dest('v5/'));
}

let movefiles = function () {
    var paths = [
        { src: 'dev/vendor/**/*', dest: './v5/vendor/' },
        { src: 'dev/img/*', dest: './v5/img/' },
        { src: 'dev/pdf/images/*', dest: './v5/pdf/images/' },
        { src: 'dev/typ/*', dest: './v5/typ/' },
        { src: 'dev/orglogos/*', dest: './v5/orglogos/' },
        { src: 'dev/datenschutz/*', dest: './v5/datenschutz/' },
        { src: 'dev/include/.htaccess', dest: './v5/include/' },
        { src: 'dev/lizenz/*', dest: './v5/lizenz/' }
    ];

    var tasks = paths.map(function (path) {
        return src(path.src).pipe(dest(path.dest));
    });

    return merge(tasks);
}

let buildstructure = function () {
    return src('*.*', { read: false })
        .pipe(dest('v5/gpximport/'));
}

var _, index = process.argv.indexOf("--watch");
if (index > -1) {
    watch(['dev/css/*.scss', 'dev/**/*.js', 'dev/**/*.php', 'dev/**/*.html'], series(nodejs, adminjs, buildjs, buildcss, movephp, movehtml, movefiles));
}

exports.default = series(deletedist, composerinstall, nodejs, adminjs, buildjs, buildcss, movephp, movehtml, movefiles, buildstructure,);	