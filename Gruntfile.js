module.exports = function(grunt) {
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-phplint');
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-watch');

	grunt.initConfig({
			pkg: grunt.file.readJSON('package.json'),
			phplint: {
				all: ['src/**/*.php']
			},
			watch: {
				dist: {
					options: {
						livereload: true
					},
					files: ['src/**/*'],
					tasks: ['phplint']
				}
			}
	});

	grunt.registerTask('default', [
		'phplint'
	]);
};
