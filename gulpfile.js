var gulp  = require('gulp');
var sass  = require('gulp-sass');
var paths = {
    styles : ['assets/scss/udate.scss'],
};

// CSS Tasks.
gulp.task('styles', function() {
    return gulp.src(paths.styles)
        .pipe(sass({outputStyle: 'compressed'}))
        .on('error', sass.logError )
        .pipe(gulp.dest('./assets/css/'));
});

// Return the task when a file changes
gulp.task('watch', function() {
    gulp.watch( paths.styles, ['styles'] );
});

// The default task (called when you run `gulp` from cli)
gulp.task('default', ['watch', 'styles']);
