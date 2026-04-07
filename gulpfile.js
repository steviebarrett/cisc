const gulp = require("gulp");
const sass = require("gulp-sass")(require("sass"));
const sourcemaps = require("gulp-sourcemaps");
const path = require("path");

// Paths
const projectRoot = __dirname;
const paths = {
    scss: path.join(projectRoot, "assets/scss/**/*.scss"),
    scssEntry: path.join(projectRoot, "assets/scss/main.scss"),
    cssOutput: path.join(projectRoot, "public/assets/css"),
};

const sassIncludePaths = [path.join(projectRoot, "assets/scss")];

// Compile SCSS with sourcemaps
gulp.task("sass", function () {
    return gulp
        .src(paths.scssEntry)
        .pipe(sourcemaps.init())
        .pipe(
            sass({
                outputStyle: "expanded",
                includePaths: sassIncludePaths,
            }).on("error", sass.logError),
        )
        .pipe(sourcemaps.write("."))
        .pipe(gulp.dest(paths.cssOutput));
});

// Minified production build
gulp.task("sass:prod", function () {
    return gulp
        .src(paths.scssEntry)
        .pipe(
            sass({
                outputStyle: "compressed",
                includePaths: sassIncludePaths,
            }).on("error", sass.logError),
        )
        .pipe(gulp.dest(paths.cssOutput));
});

// Watch SCSS files for changes
gulp.task("watch", function () {
    gulp.watch(paths.scss, gulp.series("sass"));
    console.log("Watching SCSS files for changes...");
});

// Build task
gulp.task("build", gulp.series("sass:prod"));

// Default task (compile once)
gulp.task("default", gulp.series("sass"));
