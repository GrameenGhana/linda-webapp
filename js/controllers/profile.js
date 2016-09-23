'use strict';

/* Controllers */
app.controller('ProfileController', ['$scope', '$state', 'Session', 'AppService',

        function($scope, $state, Session, AppService) {

                $scope.err_msg = '';
                $scope.success_msg = '';

                $scope.error = false; 
                $scope.success = false;

                // get locations
                // $scope.getLocation = Movester.getLocation;

                // get list of industries
                // $scope.industries = [];
                // Movester.getIndustryList().then(function(res) { $scope.industries = res; });
                
                // update form submitted 
                $scope.loginInfoForm = {};
                $scope.credentials = { email: Session.getEmail() };

	            $scope.updateinfo = function() {
		            $scope.submitted = true;
		            if (!$scope.loginInfoForm.$invalid) {
		                $scope.error = false;
                        $scope.success = false;
		                AppService.updateUser($scope.credentials)
                            .then(function(response) {
                                $scope.success = true;
                                $scope.success_msg = 'Information updated.';
                                Session.updateUser(response.user);
                                $state.go('app.profile'); }
                            ,function(err) {
			                    $scope.error = true;
                                $scope.err_msg = err;
                                return 0;
		                    });
		            } else {
			            $scope.error = true;
                        $scope.err_msg = 'Update error. Please enter correct information.';
			            return 1;
		            }
	            };
      } 
]);
