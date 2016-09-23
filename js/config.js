// config

var app =  
angular.module('app')

  .constant('USER_ROLES', {
        all     : '*',
        admin   : 'Super Admin',
        gfuser  : 'GF User',
        ghssupr : 'GHS Supervisor',
        ghsteam : 'GHS Local Management Team',
        ghscall : 'GHS Call Center',
        ghsuser : 'GHS User'
    })

  .constant('API_URL', '/linda/api/v1/')

  .config(
    [        '$controllerProvider', '$compileProvider', '$filterProvider', '$provide',
    function ($controllerProvider,   $compileProvider,   $filterProvider,   $provide) {
        
        // lazy controller, directive and service
        app.controller = $controllerProvider.register;
        app.directive  = $compileProvider.directive;
        app.filter     = $filterProvider.register;
        app.factory    = $provide.factory;
        app.service    = $provide.service;
        app.constant   = $provide.constant;
        app.value      = $provide.value;
    }
  ])

    // Allow use of base url (note that hash bang values for href might not work)
  .config(function($locationProvider) {
        $locationProvider.html5Mode(true);
    })

  .config(function ($authProvider) {
        $authProvider.baseUrl = '/linda/';
        $authProvider.loginOnSignup = true;
        $authProvider.loginRedirect = '/app/home';
        $authProvider.signupRedirect = '/app/home';
        $authProvider.loginUrl = '/api/auth/login';
        $authProvider.signupUrl = '/api/auth/signup';
    })

  .config(['$translateProvider', function($translateProvider){
    // Register a loader for the static files
    // So, the module will search missing translation tables under the specified urls.
    // Those urls are [prefix][langKey][suffix].
    $translateProvider.useStaticFilesLoader({
      prefix: 'l10n/',
      suffix: '.js'
    });
    // Tell the module what language to use by default
    $translateProvider.preferredLanguage('en');
    // Tell the module to store the language in the local storage
    $translateProvider.useLocalStorage();
  }]);