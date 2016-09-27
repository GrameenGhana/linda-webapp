'use strict';

angular.module('app')

.factory('AppService', [ 'WebService', function(WebService) {

    var appService = {};

    // Reports
    appService.getSplineReport = function(params) { return WebService.post('reports/spline',params); };
    appService.getBedUtilizationReport = function(params) { return WebService.post('reports/bedutilization',params); };

    // Beds
    appService.getBeds = function() { return WebService.get('bed'); };
    appService.getBedsByLocation = function(hospital, ward) { return WebService.get('bed/'+hospital+'/'+ward);};
    appService.deleteBed = function(id) { return WebService.delete('bed/'+id); };
    appService.updateBedStatus = function(ids, status) {
        var info = {'ids': ids, 'status': status };
        return WebService.post('bed/updatestatus', info);
    };
    appService.addBed = function(data) { return WebService.post('bed', data); };
    appService.toggleBedStatus = function(info) { return WebService.post('bed/togglestatus',info); };

    appService.addWard = function(data) { return WebService.post('ward', data); };
    appService.deleteWard = function(id) { return WebService.delete('ward/'+id); };

    // Hospitals
    appService.getHospitals = function() { return WebService.get('hospital'); }
    appService.getHospitalsByUser = function(userid) { return WebService.get('hospital/byuser?userid='+userid); }
    appService.getHospitalsForNurse = function(id) { return WebService.get('hospital/nurse/'+id); };
    appService.getHospitalsPeriodData = function(period) { return WebService.get('hospital/period/'+period); };
    appService.getHospitalsForMap = function() { return WebService.get('hospital/map'); }
    appService.getHospitalsForTree = function() { return WebService.get('hospital/tree'); }
    appService.getHospitalsForTree = function() { return WebService.get('hospital/tree'); }
    appService.addHospital = function(data) { return WebService.post('hospital', data); };
    appService.deleteHospital = function(id) { return WebService.delete('hospital/'+id); };
    appService.enableHospital = function(id) { return WebService.get('hospital/status/enable/'+id); };
    appService.disableHospital = function(id) { return WebService.get('hospital/status/disable/'+id); };

    // Locations
    appService.getLocations = function() { return WebService.get('location'); }
    appService.addLocation = function(data) { return WebService.post('location', data); };
    appService.deleteLocation = function(id) { return WebService.delete('location/'+id); }

    // User
    appService.getUsers = function() { return WebService.get('user'); }
    appService.updateUser = function(user) { return WebService.post('user', user); };
    appService.deleteUser = function(id) { return WebService.delete('user/'+id); }
    appService.addUser = function(data) { return WebService.post('user', data); };

    return appService;
}]);
