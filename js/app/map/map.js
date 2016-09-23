/* global console:false, google:false */
/*jshint unused:false */
'use strict';

app.controller('MapCtrl', ['$rootScope', '$scope', '$interval', 'AppService', '$http',
    function ($rootScope, $scope, $interval, AppService, $http) {

    var getBarColor = function(occupancy) {
        if (occupancy >= 85) { return $scope.app.color.danger; }
        if (occupancy >= 50) { return $scope.app.color.warning; }
        return $scope.app.color.success;
    };

    var getPBarColor = function(occupancy) {
        if (occupancy >= 85) { return 'progress-bar-danger' }
        if (occupancy >= 50) { return 'progress-bar-warning' }
        return 'progress-bar-success';
    };

    var getImage = function(occupancy) {
        if (occupancy >= 85) { return 'img/hospital-almost-full.png'; }
        if (occupancy >= 50) { return 'img/hospital-semi-full.png'; }
        return 'img/hospital-not-full.png';
    };

    var updateMap = function() {
        $scope.hospitals = 0;
        $scope.beds_totl = 0;
        $scope.beds_open = 0;
        $scope.or = 0;

        AppService.getHospitalsForMap().then(function (res) {
            angular.forEach(res, function (item) {
                var image = getImage(item.current_or);

                $scope.hospitals++;
                $scope.beds_totl += item.numbeds;
                $scope.beds_open += item.openbeds;
                //console.log("Increasing open bed by "+item.openbeds + " to " + $scope.beds_open);
                if ($scope.beds_totl != 0) {
                    $scope.or = 100 - Math.round(($scope.beds_open/$scope.beds_totl)*100,2);
                }
                $scope.myMarkers.push(new google.maps.Marker({
                    map: $scope.myMap,
                    title: item.name,
                    icon: image,
                    beds_open: item.openbeds,
                    beds_totl: item.numbeds,
                    or: item.current_or,
                    pbcolor: getPBarColor(item.current_or),
                    updated_at: item.lastupdate.date,
                    position: new google.maps.LatLng(item.lat, item.long)
                }));
            });

            //if ($('#statChart')) {
            //    $('#statChart').data('easyPieChart').update($scope.or);
           //     $('#statChart').data('easyPieChart').options['barColor'] = getBarColor($scope.or);
            //}
        });
    };

    $scope.myMarkers = [];
    $scope.hospitals = 0;
    $scope.beds_totl = 0;
    $scope.beds_open = 0;
    $scope.tree = {};
    $scope.orig={};
    $scope.or = 0;


    updateMap();

    var stop = $interval(updateMap, 120000);

    $scope.statOptions = {
        percent: 0,
        lineWidth: 3,
        trackColor: '#e8eff0',
        barColor: getBarColor($scope.or),
        scaleColor: '#fff',
        size: 50,
        lineCap: 'butt',
        animate: 1000
    };

    $scope.mapOptions = {
        center: new google.maps.LatLng(5.822, 0.200),
        zoom: 9,
        scrollwheel: false,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };


    $scope.formatName = function( name ) {
        return name;
    }

    $scope.onDetail = function( node ) {
        //console.log($scope.tree);
    }

    //$http.get('http://www.chnonthego.org/api/v1/hospital/tree')
    //$http({method: "GET", url: "ihttp://localhost/api/v1/hospital/tree" }).success(function(data) {
    AppService.getHospitalsForTree().then(function (data) {
        $scope.tree = data;
        $scope.orig = data;
    });

    $scope.$on('$destroy', function() { $interval.cancel(stop); });

    $scope.resetTree = function (a,b) {
        $scope.tree = $scope.orig;
        $scope.myInfoWindow.close();
    };

    $scope.openMarkerInfo = function (marker) {
      $scope.currentMarker = marker;
      $scope.myInfoWindow.open($scope.myMap, marker);

      // Zoom to Hospital
      var idx = $scope.indexOf($scope.orig['children'], marker.title, 'name');
      if (idx != -1) {
          $scope.tree = $scope.orig['children'][idx];
      }
    };
}]);

app.directive('uiEvent', ['$parse',
  function ($parse) {
    return function ($scope, elm, attrs) {
      var events = $scope.$eval(attrs.uiEvent);
      angular.forEach(events, function (uiEvent, eventName) {
        var fn = $parse(uiEvent);
        elm.bind(eventName, function (evt) {
          var params = Array.prototype.slice.call(arguments);
          //Take out first paramater (event object);
          params = params.splice(1);
          fn($scope, {$event: evt, $params: params});
          if (!$scope.$$phase) {
            $scope.$apply();
          }
        });
      });
    };
  }
]);
