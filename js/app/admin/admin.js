var sortByName = function(a,b) {
          var aName = a.name.toLowerCase();
          var bName = b.name.toLowerCase();
          return ((aName < bName) ? -1 : ((aName > bName) ? 1 : 0));
};

var sortByRegion = function(a,b) {
          var aName = a.region.toLowerCase();
          var bName = b.region.toLowerCase();
          return ((aName < bName) ? -1 : ((aName > bName) ? 1 : 0));
};

app.controller('AdminCtrl', ['$scope',  function($scope) {
    $scope.isItem = function(item) { return ($scope.item==item); };
}]);

app.controller('AdminViewCtrl', ['$scope',  '$stateParams', 'toaster', 'AppService', 'Session',
  function($scope, $stateParams, toaster, AppService, Session) {

    $scope.isItem = function(item) { return ($scope.item==item); };

    if ($scope.item=='Beds') {
        $scope.beds = [];
        $scope.beds_totl = 0;
        $scope.beds_open = 0;

        $scope.wards = [];
        $scope.hospitals = [];
        $scope.regions = [];
        $scope.selectedRegion = {};
        $scope.selectedHosp = {};
        $scope.selectedWard = {};
        $scope.showSpinner = true;

        $scope.deleteBed = function(bed) {
            return AppService.deleteBed(bed.id).then(function(res) {
                if (res.error) {
                    toaster.pop('error', 'Error', res.message);
                } else {
                    var idx = $scope.indexOf($scope.beds, res.bed.id, 'id');
                    if (idx != -1) { $scope.beds.splice(idx, 1); }
                }
            });
        };

        $scope.$watch('selectedRegion', function (newV, old) { updateHospitalInfo(); }, false);
        $scope.$watch('selectedHosp', function (newV, old) {   $scope.selectedWard = {'id':0, 'name':'All'};}, false);
        $scope.$watch('selectedWard', function (newVal, oldVal) { updateBedInfo(); }, false);


        var updateHospitalInfo = function() {
            $scope.showSpinner = true;
            if ($scope.regions.length) {
                var h = [];
                angular.forEach($scope.regions, function (r) {
                    if (r.region != 'All') {
                        angular.forEach(r.districts, function (d) {
                            h = h.concat(d.hospitals);
                        });
                    }
                });

                $scope.hospitals = h.sort(sortByName);
                $scope.hospitals.unshift({'id': 0, 'name': 'All', 'wards':[]});

                // add 'All' to wards
                angular.forEach($scope.hospitals, function (h, hkey) {
                    $scope.hospitals[hkey].wards.unshift({'id': 0, 'name': 'All'});
                });

                $scope.selectedHosp = $scope.hospitals[1];
            }
            $scope.showSpinner = false;
        };

        var updateBedInfo = function() {
            $scope.showSpinner = true;
            AppService.getBedsByLocation($scope.selectedHosp.id, $scope.selectedWard.id).then(function(res) {
                var open = getObjects(res, 'status', 'Available');
                $scope.beds = res.sort(sortByName);
                $scope.beds_totl = res.length;
                $scope.beds_open = open.length;
                $scope.showSpinner = false;
            });
        };

        AppService.getHospitalsByUser(Session.getUserId()).then(function(res){
            $scope.regions = res.sort(sortByRegion);
            $scope.regions.unshift({'region':'All', 'districts':[] });
            $scope.selectedRegion = $scope.regions[1];
        });
    }

    else if ($scope.item=='Wards') {

        $scope.wards = [];
        $scope.hospitals = [];
        $scope.regions = [];
        $scope.selectedRegion = {};
        $scope.selectedHosp = {};
        $scope.showSpinner = true;

        $scope.deleteWard = function(ward) {
            return AppService.deleteWard(ward.id).then(function(res) {
                if (res.error) {
                    toaster.pop('error', 'Error', res.message);
                } else {
                    var idx = $scope.indexOf($scope.wards, res.ward.id, 'id');
                    if (idx != -1) { $scope.wards.splice(idx, 1); }
                }
            });
        };

        $scope.$watch('selectedRegion', function (newV, old) { updateHospitalInfo(); }, false);

        var updateHospitalInfo = function() {
            $scope.showSpinner = true;
            if ($scope.regions.length) {
                var h = [];
                angular.forEach($scope.regions, function (r) {
                    if (r.region != 'All') {
                        angular.forEach(r.districts, function (d) {
                            h = h.concat(d.hospitals);
                        });
                    }
                });

                $scope.hospitals = h.sort(sortByName);
                $scope.selectedHosp = $scope.hospitals[0];
            }
            $scope.showSpinner = false;
        };

        AppService.getHospitalsByUser(Session.getUserId()).then(function(res){
            $scope.regions = res.sort(sortByRegion);
            $scope.selectedRegion = $scope.regions[0];
        });
    }

    else if ($scope.item=='Hospitals') {

        $scope.regions = [];
        $scope.hospitals = [];
        $scope.selectedRegion = {};
        $scope.showSpinner = true;

        $scope.deleteHospital = function(hospital) {
            return AppService.deleteHospital(hospital.id).then(function(res) {
                if (res.error) {
                    toaster.pop('error', 'Error', res.message);
                } else {
                    var idx = $scope.indexOf($scope.hospitals, res.params.id, 'id');
                    if (idx != -1) { $scope.hospitals.splice(idx, 1); }
                }
            });
        };

        $scope.$watch('selectedRegion', function (newV, old) { updateHospitalInfo(); }, false);

        var updateHospitalInfo = function() {
            $scope.showSpinner = true;
            var h = [];
            angular.forEach($scope.regions, function (r) {
                if (r.region != 'All') {
                    angular.forEach(r.districts, function (d) {
                        h = h.concat(d.hospitals);
                    });
                }
            });
            $scope.hospitals = h.sort(sortByName);
            $scope.showSpinner = false;
        };

        AppService.getHospitalsByUser(Session.getUserId()).then(function(res){
            $scope.regions = res.sort(sortByRegion);
            $scope.regions.unshift({'region':'All', 'districts':[] });
            $scope.selectedRegion = $scope.regions[0];
        });
    }


    else if ($scope.item=='Locations') {

        $scope.regions = ['All','Ashanti','Brong Ahafo','Central', 'Eastern', 'Greater Accra',
                          'Northern','Upper East','Upper West','Volta','Western'];
        $scope.locations = [];
        $scope.lfilter = {};
        $scope.selectedRegion = 'All';
        $scope.showSpinner = true;

        $scope.deleteLocation = function(location) {
            return AppService.deleteLocation(location.id).then(function(res) {
                if (res.error) {
                    toaster.pop('error', 'Error', res.message);
                } else {
                    var idx = $scope.indexOf($scope.locations, res.location.id, 'id');
                    if (idx != -1) { $scope.locations.splice(idx, 1); }
                }
            });
        };

        $scope.$watch('selectedRegion', function (newVal, oldVal) {
            $scope.lfilter = (newVal=='All') ? {} : {'region': newVal };
        }, false);

        AppService.getLocations().then(function(res){
            $scope.locations = res;
            $scope.showSpinner = false;
        });
    }
    else if ($scope.item=='Users') {

        $scope.regions = [];
        $scope.hospitals = [];
        $scope.users = [];
        $scope.ufilter = {};
        $scope.selectedHosp = {};
        $scope.selectedRegion= {};
        $scope.showSpinner = true;

        $scope.deleteUser = function(user) {
            return AppService.deleteUser(user.id).then(function(res) {
                if (res.error) {
                    toaster.pop('error', 'Error', res.message);
                } else {
                    var idx = $scope.indexOf($scope.users, res.user.id, 'id');
                    if (idx != -1) { $scope.users.splice(idx, 1); }
                }
            });
        };

        $scope.$watch('selectedRegion', function (newVal, oldVal) { updateHospitalInfo(); }, false);
        $scope.$watch('selectedHosp', function (newVal, oldVal) {

            $scope.showSpinner = true;

            if (angular.isDefined($scope.selectedRegion.region)) {
                $scope.ufilter = ($scope.selectedRegion.region == 'All') ? {} : {'region': $scope.selectedRegion.region};
            }

            if (newVal.name != 'All') {
                $scope.ufilter['hospital'] =  { 'name': newVal.name.replace('Hospital','') };
            }

            $scope.showSpinner = false;

        }, false);

        var updateHospitalInfo = function() {
            var h = [];

            angular.forEach($scope.regions, function (r) {
                if (r.region != 'All') {
                    angular.forEach(r.districts, function (d) {
                        h = h.concat(d.hospitals);
                    });
                }
            });

            $scope.hospitals = h.sort(sortByName);
            $scope.hospitals.unshift({'id':0, 'name':'All', 'wards':[] });
            $scope.selectedHosp = $scope.hospitals[0];
        };

        AppService.getHospitalsByUser(Session.getUserId()).then(function(res){
            $scope.regions = res.sort(sortByRegion);
            $scope.regions.unshift({'region':'All', 'districts':[] });
            $scope.selectedRegion = $scope.regions[0];
            AppService.getUsers().then(function(res){ $scope.users = res; });
        });
    }
}]);

app.controller('AdminDetailCtrl', ['$scope', '$stateParams', function($scope, $stateParams) {
    console.log($scope.item);
    console.log($stateParams.id);
}]);

app.controller('AdminAddCtrl', ['$scope', '$stateParams', 'toaster', 'AppService', 'Session', function($scope, $stateParams, toaster, AppService, Session) {
    
    $scope.regions=[];
    $scope.hospitals=[];
    $scope.selectedHosp = {};

    $scope.err_msg = '';
    $scope.error = false;
    
    var handleError = function(response) {
        if (response.data.message) {
            if (typeof response.data.message === 'object') {
                angular.forEach(response.data.message, function(message) { $scope.err_msg += message[0]; });
            } else {
                $scope.err_msg = response.data.message;
            }
        } else if (response.data) {
                $scope.err_msg = response.data;
        } else {
                $scope.err_msg = response;
        }
        toaster.pop('error', 'Error', $scope.err_msg);
        $scope.error = true;
        return 1;
    };

    var handleSuccess = function(response) {
        toaster('info','Success',$scope.item+' added');
        $scope.err_msg = '';
        $scope.error = false;
        angular.element('#back_button').triggerHandler('click')
    };

    var updateHospital = function() {
            if ($scope.regions.length) {
                var h = [];
                angular.forEach($scope.regions, function (r) {
                    if (r.region != 'All') {
                        angular.forEach(r.districts, function (d) {
                            h = h.concat(d.hospitals);
                        });
                    }
                });

                $scope.hospitals = h.sort(sortByName);
                $scope.selectedHosp = $scope.hospitals[0];
            }
    };

    AppService.getHospitalsByUser(Session.getUserId()).then(function(res){
       $scope.regions = res.sort(sortByRegion);
       updateHospital();
    });

    if ($scope.item=='Beds') {
        $scope.bed = {};
       
        $scope.addBed = function() {
           $scope.bed.hospital = $scope.selectedHosp.id;
           console.log($scope.bed);
           AppService.addBed({data: $scope.bed, user_id:Session.getUserId()})
                         .then(function(response) { handleSuccess(response);  })
                         .catch(function(response) { handleError(response); });
        };
    }

    else if ($scope.item=='Wards') {
        $scope.ward = {};
        $scope.addWard = function() {
           $scope.ward.hospital = $scope.ward.hospital.id;
           AppService.addWard({data: $scope.ward, user_id:Session.getUserId()})
                         .then(function(response) { handleSuccess(response);  })
                         .catch(function(response) { handleError(response); });
        };
    }

    else if ($scope.item=='Hospitals') {
        $scope.hospital = {};
        $scope.addHospital = function() {
           $scope.hospital.location = $scope.hospital.location.location_id;
           AppService.addHospital({data: $scope.hospital, user_id:Session.getUserId()})
                         .then(function(response) { handleSuccess(response);  })
                         .catch(function(response) { handleError(response); });
        };
    }

    else if ($scope.item=='Locations') {
        $scope.loc = {};
        $scope.addLocation = function() {
           AppService.addLocation({data: $scope.loc, user_id:Session.getUserId()})
                         .then(function(response) { handleSuccess(response);  })
                         .catch(function(response) { handleError(response); });
        };
    }

    else if ($scope.item=='Users') {
        $scope.user = {};
        $scope.types = [];

        $scope.addUser = function() {
           AppService.addUser({data: $scope.user, user_id:Session.getUserId()})
                         .then(function(response) { handleSuccess(response);  })
                         .catch(function(response) { handleError(response); });
        };

        var role = Session.getRole();
        var getUserTypes = function() {
            var types = ['Super Admin','GF User','GHS Supervisor','GHS Call Center','GHS Local Management Team','GHS User'];
            if (! Session.isSuperAdmin()) {
                if (~role.indexOf('GHS')) {
                    types.splice(0, 2);
                    if (~role.indexOf('Team')) {
                        types.splice(0,2);
                   } else if (~role.indexOf('Call')) {
                        types = ['GHS Call Center'];
                    }
                } else {
                    types.splice(0, 1);
                }
            }
            return types;
        };

        $scope.types = getUserTypes();
    }
}]);
