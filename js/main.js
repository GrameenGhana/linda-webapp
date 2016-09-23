'use strict';

/* Controllers */

angular.module('app')
  .controller('AppCtrl', ['$scope','$rootScope', '$state', '$translate', '$localStorage', '$window', 'Session', 'USER_ROLES',
    function(              $scope,  $rootScope,  $state, $translate,   $localStorage,   $window, Session, USER_ROLES ) {
      // add 'ie' classes to html
      var isIE = !!navigator.userAgent.match(/MSIE/i);
      isIE && angular.element($window.document.body).addClass('ie');
      isSmartDevice( $window ) && angular.element($window.document.body).addClass('smart');

      // config
      $scope.app = {
        name: 'Linda',
        version: '1.0.0',
        base_url: 'http://chnonthego.org/linda/',
        // for chart colors
        color: {
          primary: '#7266ba',
          info:    '#23b7e5',
          success: '#27c24c',
          warning: '#fad733',
          danger:  '#f05050',
          light:   '#e8eff0',
          dark:    '#3a3f51',
          black:   '#1c2b36'
        },
        settings: {
          themeID: 14,
          navbarHeaderColor: 'bg-dark',
          navbarCollapseColor: 'bg-dark',
          asideColor: 'bg-light',
          headerFixed: true,
          asideFixed: true,
          asideFolded: false,
          asideDock: true,
          container: false
        }
      }

      // save settings to local storage
      if ( angular.isDefined($localStorage.settings) ) {
        $scope.app.settings = $localStorage.settings;
      } else {
        $localStorage.settings = $scope.app.settings;
      }

      $scope.$watch('app.settings', function(){
        if( $scope.app.settings.asideDock  &&  $scope.app.settings.asideFixed ){
          // aside dock and fixed must set the header fixed.
          $scope.app.settings.headerFixed = true;
        }
        // save to local storage
        $localStorage.settings = $scope.app.settings;
      }, true);

      // angular translate
      $scope.lang = { isopen: false };
      //$scope.langs = {en:'English', de_DE:'German', it_IT:'Italian'};
        $scope.langs = {en:'English'};
        $scope.selectLang = $scope.langs[$translate.proposedLanguage()] || "English";
      $scope.setLang = function(langKey, $event) {
        // set the current lang
        $scope.selectLang = $scope.langs[langKey];
        // You can change the language during runtime
        $translate.use(langKey);
        $scope.lang.isopen = !$scope.lang.isopen;
      };

      function isSmartDevice( $window )
      {
          // Adapted from http://www.detectmobilebrowsers.com
          var ua = $window['navigator']['userAgent'] || $window['navigator']['vendor'] || $window['opera'];
          // Checks for iOs, Android, Blackberry, Opera Mini, and Windows mobile devices
          return (/iPhone|iPod|iPad|Silk|Android|BlackBerry|Opera Mini|IEMobile/).test(ua);
      }

      $scope.userRoles = USER_ROLES;
      $scope.currentUser = Session.getUser();

      $scope.isAdmin = function() { return Session.isAdmin(); };
      $scope.isSuperAdmin = function() { return Session.isSuperAdmin(); };
      $scope.isNurse = function () { return Session.isNurse(); }  ;
      $scope.logout = function() { Session.logout(); $state.go('access.signin'); };


    // Common
    $scope.indexOf = function(myArray, searchTerm, property) {
        for(var i = 0, len = myArray.length; i < len; i++) {
            if (myArray[i][property] === searchTerm) return i;
        }
        return -1;
    };

    $scope.getYears = function(startYear) {
        var currentYear = new Date().getFullYear(), years = [];
        startYear = startYear || 2015;
        while ( currentYear >= startYear) { years.push(currentYear--); }
        return years;
    };

    $scope.getMonths = function() {
        return ['January','February','March','April','May','June','July','August',
                'September','October','November','December'];
    };

    $scope.sortByName = function(a,b) {
        var aName = a.name.toLowerCase();
        var bName = b.name.toLowerCase();
        return ((aName < bName) ? -1 : ((aName > bName) ? 1 : 0));
    };

    $scope.sortByRegion = function(a,b) {
        var aName = a.region.toLowerCase();
        var bName = b.region.toLowerCase();
        return ((aName < bName) ? -1 : ((aName > bName) ? 1 : 0));
        };

      var goHome = function() { $state.go('app.home'); };

      var setCurrentUser = function() {
        $scope.currentUser=Session.getUser();
      };

      $rootScope.$on('current-user-updated', setCurrentUser);
      $rootScope.$on('go-home', goHome);
  }]);
