module.exports = function(grunt) {
    grunt.initConfig({
        less: {
            development: {
                options: {
                    paths: ["www/less"],
                    yuicompress: true
                },
                files: {
                    "www/css/style.css": "www/less/style.less"
                }
            }
        },
        watch: {
            files: "www/less/*",
            tasks: ["less"]
        }
    });
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.registerTask('default', 'watch'); // registrace defaultní úlohy
};