/*
 * SIMPLE GRUNT
 * ------------
 * Version:     0.3
 * Description: Simple Grunt tasks for compiling frontend sources.
 * Requires:    Node JS & Grunt
 * Usage:       See "package.json" for Registered grunt tasks.
 *
 */


module.exports = function(grunt) {
    // Require tasks
    require('load-grunt-tasks')(grunt);

    // Measures the time each task takes
    require('time-grunt')(grunt);

    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        // JavaScript compiling
        uglify: {
            options: {
                banner: '/*! <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd") %> */\n',
                sourceMap: true,
                beautify: true,
                compress: false
            },

            // Compile all JavaScript files to one JavaScript file
            dist: {
                files: {
                    '../../Public/JavaScript/App.js': [
                        '../JavaScript/Components/**/*.js',
                        '../JavaScript/Vendor/**/*.js',
                        '../JavaScript/Customer/**/*.js'
                    ]
                }
            }
        },

        // SASS compiling
        sass: {
            options: {
                outputStyle: 'expanded',
                sourceMap: true,
                includePaths: [
                    'node_modules/bootstrap/scss'
                ]
            },
            dist: {
                files: {
                    '../../Public/Css/App.css': '../Sass/App.scss'
                }
            }
        },

        // Watching SASS & JavaScript changes
        watch: {
            options: {
            },
            sass: {
                files: '../Sass/**/*.scss',
                tasks: ['compile-sass']
            },
            js: {
                files: '../JavaScript/**/*.js',
                tasks: ['compile-js']
            }
        },

        notify: {
            watch: {
                options: {
                    title: 'Compiling SASS and JavaScript Complete',
                    message: 'Build finished successfully.'
                }
            },
            sass: {
                options: {
                    title: 'Compiling Sass Complete',
                    message: 'Build finished successfully.'
                }
            },
            js: {
                options: {
                    title: 'Compiling JavaScript complete',
                    message: 'Build finished successfully.'
                }
            }
        }
    });

    // Grunt build Task for development usage
    grunt.registerTask('notify_hooks');
    grunt.registerTask('default', ['uglify', 'sass','notify:watch']);
    grunt.registerTask('watcher', ['default', 'watch']);
    grunt.registerTask('compile-js-dev', ['uglify','notify:js']);
    grunt.registerTask('compile-sass-dev', ['sass','notify:sass']);

    // Grunt build Task for production usage
    grunt.registerTask('compile-js', 'uglify', function () {
        grunt.config.merge({
            uglify: {
                options: {
                    sourceMap: true,
                    beautify: false,
                    compress: true
                }
            }
        });
        grunt.task.run('uglify');
        grunt.task.run('notify:js');
    });

    grunt.registerTask('compile-sass', 'sass', function () {
        grunt.config.merge({
            sass: {
                options: {
                    outputStyle: 'compressed'
                }
            }
        });
        grunt.task.run('sass');
        grunt.task.run('notify:sass');
    });

    grunt.registerTask('build', 'Grunt build Task for production usage', function () {
        grunt.task.run('compile-sass');
        grunt.task.run('compile-js');
        grunt.task.run('notify:watch');
        grunt.log.writeln('√ '['green'] + 'Compiled SASS and JavaScript files for production usage.'['green']);
    })
};