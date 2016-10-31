'use strict';

/**
 * Config for the router
 */
angular.module('app')
  .run(
    [           '$rootScope', '$state', '$stateParams', 'Session',
      function ($rootScope,   $state,   $stateParams, Session) {
          $rootScope.$state = $state;
          $rootScope.$stateParams = $stateParams;
          $rootScope.$on('$stateChangeSuccess', function (event, to, toParams, from, fromParams) {
              $state.myprevious = from.name;
              $state.mypreviousparams = fromParams;
          });

          // before each state change, check if the user is logged in
          // and authorized to move onto the next state
          $rootScope.$on('$stateChangeStart', function (event, next) {
              if (typeof next.data !== 'undefined') {
                  var authorizedRoles = next.data.authorizedRoles;
                  if (Session.isAuthenticated()) {
                      if (!Session.isAuthorized(authorizedRoles)) {
                          $state.go('access.no_access');
                      }
                  } else {
                      // user is not logged in
                      window.location = 'http://localhost/linda/access/signin';
                  }
              } else {
                  if (next.url == '/signin' || next.url=='/home')
                  {
                      if (Session.isAuthenticated()) {
                          event.preventDefault();
                          $state.go('app.home');
                      }
                  }
              }

          });

          $rootScope.logout = function() { Session.logout(); }
      }
    ]
  )
  .config(
    [          '$stateProvider', '$urlRouterProvider', 'USER_ROLES',
      function ($stateProvider,   $urlRouterProvider, USER_ROLES) {
          
          $urlRouterProvider.otherwise('/app/home');

          $stateProvider

              // authentication
              .state('access', {
                  url: '/access',
                  template: '<div ui-view class="fade-in-right-big smooth"></div>'
              })
              .state('access.signin', {
                  url: '/signin',
                  templateUrl: 'tpl/access.html',
                  resolve: {
                      deps: ['$ocLazyLoad',
                          function( $ocLazyLoad ){
                              return $ocLazyLoad.load(['vendor/libs/md5.js','js/controllers/authentication.js']);
                          }]
                  }
              })
              .state('access.404', {
                  url: '/404',
                  templateUrl: 'tpl/access_404.html'
              })
              .state('access.no_access', {
                  url: '/404',
                  templateUrl: 'tpl/access_noaccess.html'
              })

              // application
              .state('app', {
                  abstract: true,
                  url: '/app',
                  templateUrl: 'tpl/app.html',
                  resolve: {
                      deps: ['$ocLazyLoad',
                          function( $ocLazyLoad){
                              return  $ocLazyLoad.load('toaster');
                          }]
                  }
              })
              .state('app.home', {
                  url: '/home',
                  templateUrl: 'tpl/app_home.html',
                  data: {
                      authorizedRoles: [USER_ROLES.admin, USER_ROLES.gfuser, USER_ROLES.ghssupr, USER_ROLES.ghsuser, USER_ROLES.ghscall]
                  },
                  resolve: {
                      deps: ['uiLoad',
                          function( uiLoad ){
                              return uiLoad.load( [
                                  'vendor/libs/moment.min.js',
                                  'vendor/libs/d3.v2.min.js',
                                  'js/controllers/mobile.js',
                                  'js/app/map/load-google-maps.js',
                                  'js/app/map/ui-map.js',
                                  'js/app/map/map.js']).then( function(){ return loadGoogleMaps();
                                   });
                          }]
                  }
              })
              .state('app.dashboard', {
                  url: '/dashboard',
                  templateUrl: 'tpl/app_dashboard.html',
                  data: {
                      authorizedRoles: [USER_ROLES.admin, USER_ROLES.gfuser, USER_ROLES.ghssupr, USER_ROLES.ghsuser, USER_ROLES.ghscall]
                  },
                  resolve: {
                      deps: ['$ocLazyLoad',
                          function( $ocLazyLoad ){
                              return $ocLazyLoad.load(['js/controllers/dashboard.js']);
                          }]
                  }
              })
              .state('app.help', {
                  url: '/help',
                  templateUrl: 'tpl/app_help.html'
              })
              .state('app.profile', {
                  url: '/profile',
                  templateUrl: 'tpl/app_profile.html',
                  data: {
                      authorizedRoles: [USER_ROLES.admin, USER_ROLES.gfuser, USER_ROLES.ghssupr, USER_ROLES.ghsuser, USER_ROLES.ghscall]
                  },
                  resolve: {
                      deps: ['uiLoad',
                          function( uiLoad ){
                              return uiLoad.load( ['js/controllers/profile.js'] );
                          }]
                  }
              })

              // admin
              .state('app.admin', {
                  abstract: true,
                  url: '/admin/{item}/{id}',
                  templateUrl: 'tpl/app_admin.html',
                  controller: function ($scope, $stateParams) {
                      $scope.item = (angular.isDefined($stateParams.item)) ? $stateParams.item : "Hospitals";
                      $scope.id = (angular.isDefined($stateParams.id)) ? $stateParams.id : 0;
                  },
                  data: {
                      authorizedRoles: [USER_ROLES.admin, USER_ROLES.gfuser, USER_ROLES.ghssupr]
                  },
                  resolve: {
                      deps: ['uiLoad',
                          function( uiLoad ){
                              return uiLoad.load( ['js/app/admin/admin.js',
                                  'vendor/libs/moment.min.js'] );
                          }]
                  }
              })

              .state('app.admin.view', {
                  url: '/view/',
                  templateUrl: 'tpl/app_admin_view.html'
              })

              .state('app.admin.edit', {
                  url: '/edit/',
                  templateUrl: 'tpl/app_admin_edit.html'
              })

              .state('app.admin.add', {
                  url: '/add/',
                  templateUrl: 'tpl/app_admin_add.html'
              })
      }
    ]
  );
