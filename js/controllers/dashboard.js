'use strict';

/* Controllers */
app.controller('DashboardCtrl', ['$scope', 'AppService', 'Session', function($scope, AppService, Session) {

    $scope.regions = [];
    $scope.hospitals = [];
    $scope.months = $scope.getMonths();
    $scope.years = $scope.getYears();

    /* Spline graph */
    $scope.bregion = {};
    $scope.bhospital = {};
    $scope.byear = new Date().getFullYear();

    var ado = [];
    var pbo = [];
    var dr = [];

    $scope.splineType = "ado";
    $scope.spline = [[1,0],[2,0],[3,0],[ 4,0],[ 5,0],[ 6,0],
                     [7,0],[8,0],[9,0],[10,0],[11,0],[12,0]];

    $scope.refreshSplineData = function(dataType) {
      $('li.np').removeClass('active');
      $('#'+dataType).addClass("active");
      if (dataType=="ado") { $scope.spline = ado; }
      else if (dataType=="pbo") { $scope.spline = pbo; }
      else if (dataType=="dr") { $scope.spline = dr; }
      else { $scope.spline = ado; }
      $scope.splineType = dataType;
    };

    var updateSplineGraph = function(info) {
        ado = [];
        pbo = [];
        dr = [];
        var idx = 1;
        angular.forEach(info, function (r) {
            ado.push([idx, r.avg_daily_oc]);
            pbo.push([idx, r.avg_bed_oc]);
            dr.push([idx, r.avg_death_rate]);

            idx++;
        });
        $scope.refreshSplineData($scope.splineType);
    };

    var getSplineReport = function() {
      var params = {'region': $scope.bregion.location_id, 'hospital':$scope.bhospital.id, 'year':$scope.byear};
      AppService.getSplineReport(params).then(updateSplineGraph);
    };

    $scope.$watch('bregion', function (newVal, oldVal) {if ((newVal.region != oldVal.region)) { updateHospitalList('spline'); } }, false);
    $scope.$watch('bhospital', function (newVal, oldVal) { if ((newVal != oldVal)) { getSplineReport(); } }, true);
    $scope.$watch('byear', function (newVal, oldVal) { if ((newVal != oldVal)) { getSplineReport(); } }, true);

    /* End Spline Graph */

    /* Bed utilization report */
    $scope.region = {};
    $scope.hospital = {};
    $scope.month =  $scope.months[(new Date().getMonth())];
    $scope.year = new Date().getFullYear();

    $('#report-export').click(function(e) {
        $('#report-table').tableExport({type:'excel',escape:'false'});
    });

    var updateTable = function(info) {
        var html = "";
        var totals = [0,0,0,0,0,0];
        angular.forEach(info, function (r) {
            html += "<tr>";
            html += "<td>"+r.hospital+"</td>";
            html += "<td>"+r.ward+"</td>";
            html += "<td>"+r.num_days+"</td>";
            html += "<td>"+r.bed_compliment+"</td>";
            html += "<td>"+r.admissions+"</td>";
            html += "<td>"+r.discharges+"</td>";
            html += "<td>"+r.deaths+"</td>";
            html += "<td>"+r.patient_days+"</td>";
            html += "<td>"+r.avail_bed_days+"</td>";
            html += "<td>"+r.avg_death_rate+"</td>";
            html += "<td>"+r.alos+"</td>";
            html += "<td>"+r.toi+"</td>";
            html += "<td>"+r.avg_daily_oc+"</td>";
            html += "<td>"+r.avg_bed_oc+"</td>";
            html += "<td>"+r.turnover_per_bed+"</td>";
            html += "</tr>";
            totals[0] = r.num_days;
            totals[1] = totals[1] + r.bed_compliment;
            totals[2] = totals[2] + r.admissions;
            totals[3] = totals[3] + r.discharges;
            totals[4] = totals[4] + r.deaths;
            totals[5] = totals[5] + r.patient_days;
        });
        totals[6] = totals[0] * totals[1];
        totals[7] = (totals[4] / (totals[3]+totals[4])*100);
        totals[8] = (totals[5] / (totals[3]+totals[4]),2);
        totals[9] = ((totals[6]-totals[5]) / (totals[3]+totals[4]));
        totals[10] = (totals[5] / totals[0]);
        totals[11] = (totals[5] / totals[6]*100);
        totals[12] = ((totals[3]+totals[4]) / totals[1]);
        //html += "<tr><td colspan='2'><b>Total<b></td>";
        //for(var i=0; i<totals.length; i++) { html += "<td><b>" + totals[i] + "</b></td>"; }
        //html += "</tr>"
        $('#table-body').html(html);
    };

    var getReport = function() {
        var params = {'region': $scope.region.location_id, 'hospital':$scope.hospital.id,
                      'month': $scope.months.indexOf($scope.month) + 1, 'year':$scope.year};
        AppService.getBedUtilizationReport(params).then(updateTable);
    };

    $scope.$watch('region', function (newVal, oldVal) {if ((newVal.region != oldVal.region)) { updateHospitalList('utility'); } }, false);
    $scope.$watch('hospital', function (newVal, oldVal) { if ((newVal != oldVal)) { getReport(); } }, true);
    $scope.$watch('month', function (newVal, oldVal) { if ((newVal != oldVal)) { getReport(); } }, true);
    $scope.$watch('year', function (newVal, oldVal) { if ((newVal != oldVal)) { getReport(); } }, true);
    /* End Bed utilization report */

    /* Common methods */
    var updateHospitalList = function(caller) {
        if ($scope.regions.length) {
          if ($scope.hospitals.length==0) {
              var h = [];
              angular.forEach($scope.regions, function (r) {
                  if (r.region != 'All') {
                      angular.forEach(r.districts, function (d) {h = h.concat(d.hospitals);});
                  }
              });
              $scope.hospitals = h.sort($scope.sortByName);
              $scope.hospitals.unshift({'id': 0, 'name': 'All', 'wards':[]});
          }
        }
              
        if (caller=="utility") {
            $scope.hospital = $scope.hospitals[0];
        } else {
           $scope.bhospital = $scope.hospitals[0];
        }
    };

    AppService.getHospitalsByUser(Session.getUserId()).then(function(res) {
        $scope.regions = res.sort($scope.sortByRegion);
        $scope.regions.unshift({'region':'All', 'location_id': 0, 'districts':[] });
        $scope.region = $scope.regions[0];
        $scope.bregion = $scope.regions[0];
    });
}]);
