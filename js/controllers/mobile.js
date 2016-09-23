app.controller('MobileCtrl', ['$scope', '$stateParams', '$interval', 'toaster', 'Session', 'AppService',
    function($scope, $stateParams, $interval, toaster, Session, AppService) {

    $scope.beds = [];
    $scope.hospital = null;
    $scope.beds_totl = 0;
    $scope.beds_open = 0;
    $scope.wards = [];
    $scope.or = 0;

    var switchBedStatus = function(key) {
        $scope.beds[key].status = ($scope.beds[key].status=='Available') ? 'Occupied' : 'Available';
    };

    var updateInfo = function() {
        AppService.getHospitalsForNurse(Session.getUserId()).then(function(res) {
            $scope.beds = res.hospital.beds;
            $scope.wards = res.hospital.wards;
            $scope.hospital = res.hospital;
            $scope.beds_totl = res.hospital.numbeds;
            $scope.beds_open = res.hospital.openbeds;
            $scope.or =  res.hospital.current_or;
        });
    };

    // Get ward name
    $scope.wardName = function(wardid) {
         var ward = 'unknown';
         angular.forEach($scope.wards, function(item, key) {
            if (item.id==wardid) {
                ward = item.name;
            }
         });
        console.log("called");
        return ward;
    };

    // Change bed status.
    $scope.changeBedStatus = function(bedid) {
        angular.forEach($scope.beds, function (item, key) {
            if (item.id==bedid) {
                switchBedStatus(key);

                var load = {'id': bedid, 'status': $scope.beds[key].status, key: key};

                AppService.toggleBedStatus(load).then( function (res) {
                    if (res.error) {
                        switchBedStatus(res.params.key);
                        toaster.pop('error', 'Error', 'Could not update bed: ' + res.message);
                    } else {
                        updateInfo();
                    }
                });
            }
        });
    };

    updateInfo();

    var stop = $interval(updateInfo, 60000);

    $scope.$on('$destroy', function() { $interval.cancel(stop); });
}]);

