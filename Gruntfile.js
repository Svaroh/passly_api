/**
 * Passbolt ~ Open source password manager for teams
 * Copyright (c) Passbolt SA (https://www.passbolt.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Passbolt SA (https://www.passbolt.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.passbolt.com Passbolt(tm)
 * @since         2.0.0
 */
var path = require('path');

/**
 * This Gruntfile provides tasks and commands to build and distribute the project
 *
 * @param grunt object
 */
module.exports = function(grunt) {

  /**
   * Path shortcuts
   * @type object
   */
  var paths = {
    node_modules: 'node_modules/',
    node_modules_styleguide: 'node_modules/passbolt-styleguide/',
    webroot: 'webroot/',
    img: 'webroot/img/',
    css: 'webroot/css/',
    js: 'webroot/js/',
    locales: 'resources/locales/',
    cakephp_locales: 'vendor/cakephp/localized/resources/locales/'
  };

  /**
   * Import package.json file content
   * Allow to get access to version and project name for example
   */
  var pkg = grunt.file.readJSON('package.json');

  /**
   * Load baseline NPM tasks
   */
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-contrib-watch');

  /**
   * Register project specific grunt tasks
   */
  grunt.registerTask('default', ['dependencies-update', 'styleguide-update']);
  grunt.registerTask('styleguide-update', ['copy:styleguide', 'copy:passly_brand', 'passly-inline-brand']);
  grunt.registerTask('styleguide-watch', ['watch:node-modules-styleguide']);
  grunt.registerTask('dependencies-update', 'copy:dependencies');

  grunt.registerTask('passly-inline-brand', function() {
    var files = [
      'api-account-recovery.js',
      'api-feedback.js',
      'api-recover.js',
      'api-setup.js',
      'api-triage.js'
    ].map(function(file) {
      return paths.js + 'app/' + file;
    });
    var inlineLogoPattern = /const ([A-Za-z_$][\w$]*)=function\(e\)\{return ([A-Za-z_$][\w$]*)\.createElement\("svg",([A-Za-z_$][\w$]*)\(\{xmlns:"http:\/\/www\.w3\.org\/2000\/svg",width:151,height:27,fill:"none","aria-labelledby":"logo-title logo-description",viewBox:"0 0 151 27"\},e\),.*?This is the logo of passbolt\..*?\)\};/;

    files.forEach(function(file) {
      if (!grunt.file.exists(file)) {
        return;
      }

      var contents = grunt.file.read(file);
      var replaced = false;
      contents = contents.replace(inlineLogoPattern, function(match, componentName, reactName, assignName) {
        replaced = true;
        var createElement = reactName + '.createElement';
        return 'const ' + componentName + '=function(e){return ' +
          createElement + '("svg",' + assignName + '({xmlns:"http://www.w3.org/2000/svg",width:151,height:27,fill:"none","aria-labelledby":"logo-title logo-description",viewBox:"0 0 151 27"},e),' +
          createElement + '("title",{id:"logo-title"},"Passly logo"),' +
          createElement + '("desc",{id:"logo-description"},"This is the logo of Passly."),' +
          createElement + '("path",{fill:"#17413F",d:"M12.5 1L22.273 4.864V18.159L18.295 23.045L12.5 26L6.705 23.045L2.727 18.159V4.864Z"}),' +
          createElement + '("path",{fill:"#FAFBF7",d:"M7.727 11.568L12.5 7.932L17.273 11.568L12.5 19.295Z"}),' +
          createElement + '("circle",{cx:7.727,cy:11.568,r:1.705,fill:"#17413F"}),' +
          createElement + '("circle",{cx:12.5,cy:7.932,r:1.705,fill:"#17413F"}),' +
          createElement + '("circle",{cx:17.273,cy:11.568,r:1.705,fill:"#17413F"}),' +
          createElement + '("circle",{cx:12.5,cy:19.295,r:1.705,fill:"#17413F"}),' +
          createElement + '("path",{fill:"none",stroke:"#8BBF45",strokeLinecap:"round",strokeLinejoin:"round",strokeWidth:.91,d:"M7.727 11.568L12.5 7.932L17.273 11.568M7.727 11.568L12.5 19.295L17.273 11.568"}),' +
          createElement + '("text",{x:31,y:20.5,fontFamily:"Arial, Avenir Next, Segoe UI, sans-serif",fontSize:18,fontWeight:700,fill:"var(--icon-color)"},"Passly"))};';
      });

      if (replaced) {
        grunt.file.write(file, contents);
      } else if (contents.indexOf('This is the logo of passbolt.') !== -1) {
        grunt.fail.warn('Unable to replace inline Passbolt logo in ' + file);
      }
    });
  });

  /**
   * Tasks definition
   */
  grunt.initConfig({
    pkg: pkg,

    copy: {
      dependencies: {
        files: [{
          // Openpgp
          cwd: paths.node_modules + 'openpgp/dist',
          src: ['openpgp.min.js'],
          dest: paths.js + 'vendors',
          expand: true
        }]
      },
      styleguide: {
        files: [{
          // Fonts
          cwd: paths.node_modules_styleguide + 'src/fonts',
          src: '*',
          dest: paths.webroot + 'fonts',
          expand: true
        }, {
          // Images for webroots (favicons, etc.)
          cwd: paths.node_modules_styleguide + 'src/img/webroot',
          src: '*',
          dest: paths.webroot,
          expand: true
        }, {
          // Images
          cwd: paths.node_modules_styleguide + 'src/img',
          src: [
            // Default Avatars
            'avatar/**',
            // Passly logos
            'logo/logo.png', 'logo/logo.svg', 'logo/logo_white.svg',
            // Image for inputs and controls
            'controls/check_black.svg',
            'controls/check_tick.svg',
            'controls/chevron-down_black.svg',
            'controls/chevron-down_blue.svg',
            'controls/dot_white.svg',
            'controls/dot_red.svg',
            'controls/dot_black.svg',
            'controls/infinite-bar.gif',
            'controls/loading_light.svg',
            'controls/loading_dark.svg',
            'controls/overlay-opacity-50.png',
            'controls/success.svg',
            'controls/fail.svg',
            'controls/warning.svg',
            'controls/attention.svg',
            // Login page 3rd party logo
            'third_party/FirefoxAMO_black.svg',
            'third_party/FirefoxAMO_white.svg',
            'third_party/ChromeWebStore_black.svg',
            'third_party/ChromeWebStore_white.svg',
            'third_party/appstore.svg',
            'third_party/edge-addon-black.svg',
            'third_party/edge-addon-white.svg',
            'third_party/firefox.svg',
            'third_party/chrome.svg',
            'third_party/edge.svg',
            'third_party/brave.svg',
            'third_party/vivaldi.svg',
            'third_party/safari.svg',

            // Smtp provider 3rd party logo
            'third_party/aws-ses.svg',
            'third_party/elastic-email.svg',
            'third_party/gmail.svg',
            'third_party/mailgun.svg',
            'third_party/mailjet.svg',
            'third_party/mandrill.svg',
            'third_party/sendgrid.svg',
            'third_party/sendinblue.svg',
            'third_party/zoho.svg',
            'third_party/outlook.svg',
            'third_party/office365.svg',

            // Setup
            'illustrations/email.png',
            // Themes preview
            'themes/*.png',
            // Totp images
            'diagrams/totp.svg',
            'third_party/duo.svg',
            'third_party/google-authenticator.svg',
            'third_party/yubikey.svg',
          ],
          dest: paths.webroot + 'img',
          expand: true
        }, {
          // CSS
          cwd: paths.node_modules_styleguide + 'build/css/themes/default',
          src: ['api_main.min.css', 'api_authentication.min.css', 'ext_authentication.min.css'],
          dest: paths.webroot + 'css/themes/default',
          expand: true
        }, {
          // Midgar css theme
          cwd: paths.node_modules_styleguide + 'build/css/themes/midgar',
          src: ['api_main.min.css', 'api_authentication.min.css', 'ext_authentication.min.css'],
          dest: paths.webroot + 'css/themes/midgar',
          expand: true
        }, {
          // Solarized light css theme
          cwd: paths.node_modules_styleguide + 'build/css/themes/solarized_light',
          src: ['api_main.min.css', 'api_authentication.min.css', 'ext_authentication.min.css'],
          dest: paths.webroot + 'css/themes/solarized_light',
          expand: true
        }, {
          // Solarized dark css theme
          cwd: paths.node_modules_styleguide + 'build/css/themes/solarized_dark',
          src: ['api_main.min.css', 'api_authentication.min.css', 'ext_authentication.min.css'],
          dest: paths.webroot + 'css/themes/solarized_dark',
          expand: true
        },{
          // Translation files
          cwd: paths.node_modules_styleguide + 'src/locales',
          src: ['**'],
          dest: paths.webroot + 'locales',
          expand: true
        }, {
          // Javascript applications
          cwd: paths.node_modules_styleguide + 'build/js/dist',
          src: ['api-account-recovery.js', 'api-app.js', 'api-recover.js', 'api-setup.js', 'api-triage.js', 'api-vendors.js', 'api-feedback.js'],
          dest: paths.js + 'app',
          expand: true
        },]
      },
      passly_brand: {
        files: [{
          cwd: 'resources/brand/passly',
          src: ['**'],
          dest: paths.webroot,
          expand: true
        }]
      },
      locales: {
        // CakePHP Locale Resources
        files: [{
          cwd: paths.cakephp_locales,
          src: ['fr_FR/*.po'],
          dest: paths.locales,
          expand: true
        }]
      }
    },

    watch: {
      'node-modules-styleguide': {
        files: [paths.node_modules_styleguide + 'build/**/*'],
        tasks: ['styleguide-update']
      }
    }
  });
};
