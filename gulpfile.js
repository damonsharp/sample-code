// Load plugins and define paths
var gulp = require('gulp'),
    del = require('del'),
    plugins = require('gulp-load-plugins')({ camelize: true }),
    server = require('tiny-lr')(),
    paths = {
        source: 'source/',
        assets: 'assets/',
        components: 'bower_components/'
    };

// Compile SASS
gulp.task('sass', function() {

    return gulp.src([
            paths.source + 'sass/_template.scss',
            paths.source + 'sass/twbs/_bootstrap.scss',
            paths.source + 'sass/_style.scss'
        ])
        .pipe(plugins.plumber())
        .pipe(plugins.sourcemaps.init())
        .pipe(plugins.concat('temp.scss'))
        .pipe(plugins.sass({
            outputStyle: 'compressed', // nested, compressed
            includePaths: [
                // unfortunately globbing files here doesn't work, so we have to include every path
                // included by using gulp.src above.
                paths.source + 'sass/',
                paths.source + 'sass/twbs/',
                paths.source + 'sass/twbs/bootstrap/',
                paths.source + 'sass/twbs/bootstrap/mixins/'
            ],
            errLogToConsole: true,
            onError: function(err){
                console.log(err);
            }
        }))
        .pipe(plugins.autoprefixer('last 2 versions', 'ie 8', 'ios 6', 'android 4'))
        .pipe(plugins.sourcemaps.write())
        .pipe(plugins.rename('style.css'))
        .pipe(gulp.dest('./'))
        .pipe(plugins.livereload());

});

// Copy Modernizr
gulp.task('copy-twbs', function() {

    return gulp.src([
        paths.components + 'bootstrap-sass-official/assets/stylesheets/**/*'
    ])
    .pipe(gulp.dest( paths.source + 'sass/twbs/' ));

});

// Copy Modernizr
gulp.task('modernizr', function() {

    return gulp.src([
            paths.components + 'modernizr/modernizr.js'
        ])
        .pipe(plugins.plumber())
        .pipe(plugins.jshint())
        .pipe(plugins.concat('modernizr.min.js'))
        .pipe(plugins.uglify())
        .pipe(gulp.dest( paths.assets + 'js' ))
        .pipe(plugins.livereload());
});

// Concat scripts
gulp.task('scripts', function() {

    return gulp.src([
            paths.components + 'bootstrap-sass-official/assets/javascripts/bootstrap.js',
            paths.source + 'js/main.js'
        ])
        .pipe(plugins.plumber())
        .pipe(plugins.jshint())
        .pipe(plugins.concat('main.min.js'))
        //.pipe(plugins.uglify())
        .pipe(gulp.dest( paths.assets + 'js' ))
        .pipe(plugins.livereload());
});

// Images
gulp.task('images', function() {

    return gulp.src( paths.source + 'img/**/*' )
        .pipe(plugins.changed(paths.assets + 'img/**/*'))
        .pipe(plugins.plumber())
        .pipe(plugins.imagemin({ optimizationLevel: 3, progressive: false, interlaced: true }))
        .pipe(gulp.dest(paths.assets + 'img'))
        .pipe(plugins.livereload());

});


// Clean
gulp.task('clean', function() {
    del([paths.assets], function(err, deletedFiles){});
});

// Default task
gulp.task('default',['clean'], function() {

    gulp.start(
        'sass',
        'modernizr',
        'scripts',
        'images',
        'watch'
    );

});

// Watch
gulp.task('watch', function() {

    // Live reload
    plugins.livereload.listen(server);

    // Watch sass files
    gulp.watch( paths.source + 'sass/**/*.scss', ['sass'] );

    // Watch javascript files
    gulp.watch( paths.source + 'js/**/*.js', ['scripts']);

    // Watch image files
    gulp.watch( paths.source + 'img/**/*', ['images']);

});