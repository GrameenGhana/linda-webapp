'use strict';

/* Controllers */

app.controller('DashboardOldCtrl', ['$scope', 'AppService', function($scope, AppService) {


    $scope.d = [ [1,6.5],[2,6.5],[3,7],[4,8],[5,7.5],[6,7],[7,6.8],[8,7],[9,7.2],[10,7],[11,6.8],[12,7] ];

    $scope.d0_1 = [ [0,7],[1,6.5],[2,12.5],[3,7],[4,9],[5,6],[6,11],[7,6.5],[8,8],[9,7] ];

    $scope.d0_2 = [ [0,4],[1,4.5],[2,7],[3,4.5],[4,3],[5,3.5],[6,6],[7,3],[8,4],[9,3] ];

    $scope.d1_1 = [ [10, 120], [20, 70], [30, 70], [40, 60] ];

    $scope.d1_2 = [ [10, 50],  [20, 60], [30, 90],  [40, 35] ];

    $scope.d1_3 = [ [10, 80],  [20, 40], [30, 30],  [40, 20] ];

    $scope.d2 = [];

    var updateData = function(period) {
        AppService.getHospitalsPeriodData(period).then(function(res) {
            console.log(res);
            $scope.d0_1 = res;
            //[[1,50.51],[2,49.88],[3,50.08],[4,49.84],[5,50.79],[6,49.88],[7,50.91],[8,48.5],[9,51.78],[10,47.87],
            //     [11,50.24],[12,51.11],[13,49.17],[14,51.5],[15,49.45],[16,51.49],[17,48.61],[18,50.7],[19,49.78],
              //   [20,50.83],[21,50.61],[22,49.04],[23,50.7],[24,48.96]];
        });
    };

    updateData('day');

    for (var i = 0; i < 20; ++i) {
      $scope.d2.push([i, Math.sin(i)]);
    }   

    $scope.d3 = [ 
      { label: "iPhone5S", data: 40 }, 
      { label: "iPad Mini", data: 10 },
      { label: "iPad Mini Retina", data: 20 },
      { label: "iPhone4S", data: 12 },
      { label: "iPad Air", data: 18 }
    ];

    $scope.getDaysData = function() { updateData('day'); };
    $scope.getMonthsData = function() { updateData('month'); };
    $scope.getYearsData = function() { updateData('year'); }

    $scope.refreshData = function(){
      $scope.d0_1 = $scope.d0_2;
    };

    $scope.getRandomData = function() {
      var data = [],
      totalPoints = 150;
      if (data.length > 0)
        data = data.slice(1);
      while (data.length < totalPoints) {
        var prev = data.length > 0 ? data[data.length - 1] : 50,
          y = prev + Math.random() * 10 - 5;
        if (y < 0) {
          y = 0;
        } else if (y > 100) {
          y = 100;
        }
        data.push(y);
      }
      // Zip the generated y values with the x values
      var res = [];
      for (var i = 0; i < data.length; ++i) {
        res.push([i, data[i]])
      }
      return res;
    }

    $scope.d4 = $scope.getRandomData();
  }]);