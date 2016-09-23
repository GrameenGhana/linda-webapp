'use strict';

angular.module('app').factory('WebService', [ '$http', '$q', 'API_URL', function($http, $q, API_URL) {

		var request = function(method, path, data)
		{
            return $q(function(resolve, reject) {
                $http({method: method, url:  path, data: data})
                    .success(function (res) { resolve(res); })
                    .error(function (res)   { reject(res);  });
            });
		}

		return {
            locations: function(val)
            {
                var data = { params: { address: val, sensor: false } };
                return request('GET', 'http://maps.googleapis.com/maps/api/geocode/json', data).then(function(res) {
                    var addresses = [];
                    angular.forEach(res.data.results, function(item){ addresses.push(item.formatted_address); });
                    return addresses;
                });
            },
			get: function(path, data)
			{
				return request('GET', API_URL + path, data);
			},
			post: function(path, data)
			{
				return request('POST', API_URL + path, data);
			},
			put: function(path, data)
			{
				return request('PUT', API_URL + path, data);
			},
			delete: function(path, data)
			{
				return request('DELETE', API_URL + path, data);
			}
		}
}]);
