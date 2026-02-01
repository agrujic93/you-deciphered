const { src, dest, watch } = require("gulp");
const sass = require('gulp-sass')(require('sass'));
const sourcemaps = require('gulp-sourcemaps'); // Add sourcemaps import

function globalFrontCSS(cb) {
	src('./assets/css/sass/custom/frontend-custom-style.scss')
		.pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
		.pipe(dest('./assets/css'));
	cb();
}

function globalBackCSS(cb) {
	src('./assets/css/sass/custom/backend-custom-style.scss')
		.pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
		.pipe(dest('./assets/css'));
	cb();
}

function uikitCSS(cb) {
	src('./assets/css/sass/uikit-style.scss')
		.pipe(sourcemaps.init()) // Initialize sourcemaps before Sass compilation
		.pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
		.pipe(sourcemaps.write('./maps')) // Write sourcemaps in a 'maps' directory next to CSS
		.pipe(dest('./assets/css'));
	cb();
}

function blocksCSS(cb) {
	src('./parts/blocks/**/*.scss')
		.pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
		.pipe(dest('./parts/blocks'));
	cb();
}

function watchFiles(cb) {
	watch('assets/css/sass/custom/frontend-custom-style.scss', globalFrontCSS);
	watch('assets/css/sass/custom/backend-custom-style.scss', globalBackCSS);
	watch('assets/css/sass/uikit/**/*.scss', uikitCSS);
	watch('assets/css/sass/custom/_custom-variables.scss', uikitCSS);
	watch('parts/blocks/**/**.scss', blocksCSS);
}

exports.watch = watchFiles;
