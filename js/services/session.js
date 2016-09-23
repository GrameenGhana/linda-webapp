'use strict';

angular.module('app').service('Session', ['$rootScope', '$auth', 'USER_ROLES', 

function($rootScope, $auth, USER_ROLES) {
    
	this.create = function() {
	     $rootScope.$broadcast('current-user-updated');    
	     $rootScope.$broadcast('go-home');
	};

    this.getUser = function() { return $auth.getUser(); };

    this.getUserId = function() {
        var u = this.getUser();
        return (u == null) ? u : u.id; 
    };

    this.getEmail = function() {
        var u = this.getUser();
        return (u == null) ? u : u.email; 
    };

    this.getRegion = function() {
        var u = this.getUser();
        return (u==null) ? u : u.region;
    };

    this.getHospital = function() {
        var u = this.getUser();
        return (u==null) ? u : u.hospital_id;
    };

    this.getRole = function() {
        var u = this.getUser();
        return (u == null) ? u : u.type; 
    };

    this.updateUser = function(userdetails) {
        localStorage.user = userdetails;
	    $rootScope.$broadcast('profile-change');
    };


    this.UpdateOrAppend = function (oldA, newA) {
        var found = false;
        for (var i=0; i < oldA.length; i++) {
            if (oldA[i].id == newA.id) {
                oldA[i] = newA;
                found = true;
            }
        }
        if (!found) { oldA = oldA.concat(newA); }
        return oldA;
    };


// Account
    this.isAdmin = function() {
        var u = this.getUser();
        if (u == null) return false;
        return (u.type==USER_ROLES.admin || u.type == USER_ROLES.gfuser || u.type==USER_ROLES.ghssupr || u.type==USER_ROLES.ghsteam);
    };

    this.isSuperAdmin = function() {
        var u = this.getUser();
        if (u == null) return false;
        return (u.type==USER_ROLES.admin);
    };

    this.isNurse = function() {
        var u = this.getUser();
        if (u == null) return false;
        return (u.type==USER_ROLES.ghsuser);
    };

    this.isAuthenticated = function() { return $auth.isAuthenticated(); };
  
    this.isAuthorized = function(authorizedRoles) {
		if (!angular.isArray(authorizedRoles)) { 
            authorizedRoles = [authorizedRoles]; 
        }
	    return (this.isAuthenticated() && authorizedRoles.indexOf(this.getRole()) !== -1);
	};

    this.logout = function() { $auth.logout(); };

	return this;
}]);
