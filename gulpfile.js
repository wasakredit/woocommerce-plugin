/* globals require */
var gulp = require('gulp');
var sort = require('gulp-sort');
var wpPot = require('gulp-wp-pot');

var translateFiles = '**/*.php';

gulp.task('makePOT', function () {
	return gulp.src('**/*.php')
		.pipe(sort())
		.pipe(wpPot({
			domain: 'wasa-kredit-checkout',
			destFile: 'languages/wasa-kredit-checkout.pot',
			package: 'wasa-kredit-checkout',
			bugReport: 'http://krokedil.se',
			lastTranslator: 'Krokedil <info@krokedil.se>',
			team: 'Krokedil <info@krokedil.se>'
		}))
		.pipe(gulp.dest('languages/wasa-kredit-checkout.pot'));
});

gulp.task('watch', function() {
    gulp.watch(translateFiles, ['makePOT']);
});
