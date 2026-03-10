const { src, dest, watch } = require("gulp");
const path = require("path");
const sass = require('gulp-sass')(require('sass'));

function globalFrontCSS() {
	return src('./assets/css/sass/custom/frontend-custom-style.scss', { sourcemaps: true })
		.pipe(sass({ style: 'compressed', loadPaths: [path.resolve(__dirname)] }).on('error', sass.logError))
		.pipe(dest('./assets/css', { sourcemaps: './maps' }));
}

function globalBackCSS() {
	return src('./assets/css/sass/custom/backend-custom-style.scss', { sourcemaps: true })
		.pipe(sass({ style: 'compressed', loadPaths: [path.resolve(__dirname)] }).on('error', sass.logError))
		.pipe(dest('./assets/css', { sourcemaps: './maps' }));
}

function uikitCSS() {
	return src('./assets/css/sass/uikit-style.scss', { sourcemaps: true })
		.pipe(sass({ style: 'compressed', loadPaths: [path.resolve(__dirname), path.resolve(__dirname, 'assets/css/sass')] }).on('error', sass.logError))
		.pipe(dest('./assets/css', { sourcemaps: './maps' }));
}

function blocksCSS() {
	return src('./parts/blocks/**/*.scss')
		.pipe(sass({ style: 'compressed', loadPaths: [path.resolve(__dirname)] }).on('error', sass.logError))
		.pipe(dest('./parts/blocks'));
}

function watchFiles() {
	watch('assets/css/sass/custom/frontend-custom-style.scss', globalFrontCSS);
	watch('assets/css/sass/custom/backend-custom-style.scss', globalBackCSS);
	watch('assets/css/sass/uikit-style.scss', uikitCSS);
	watch('assets/css/sass/uikit/**/*.scss', uikitCSS);
	watch('assets/css/sass/custom/_custom-variables.scss', uikitCSS);
	watch('parts/blocks/**/**.scss', blocksCSS);
}

exports.globalFrontCSS = globalFrontCSS;
exports.globalBackCSS = globalBackCSS;
exports.uikitCSS = uikitCSS;
exports.blocksCSS = blocksCSS;
exports.watch = watchFiles;
