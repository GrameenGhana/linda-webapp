'use strict';

app.controller('AuthModalInstanceCtrl', 
    ['$scope', '$state', '$modalInstance', '$auth', 'data',

    function($scope, $state, $modalInstance, $auth,data) {

                $scope.data = data;

                $scope.err_msg = '';
	            $scope.error = false;
                $scope.showSpinner = false;

                $scope.showCancel = true;
                $scope.showSignin = true;

                var handleError = function(response) {
                       $scope.showSpinner = false;

                        if (response.data.message) {
                            if (typeof response.data.message === 'object') {
                                angular.forEach(response.data.message, 
                                   function(message) {
                                    $scope.err_msg += message[0];
                                   }
                                );
                            } else {
                                $scope.err_msg = response.data.message;
                            }
                        } else if (response.data) {
                                $scope.err_msg = response.data;
                        } else {
                                $scope.err_msg = response;
                        }
			            $scope.error = true;
                        return 1;
                };

	            // sign in existing user 
                $scope.signin = function() {
		            $scope.error = false;
		            $scope.error_msg = '';

                    $auth.login({ email: $scope.data.email, password: md5($scope.data.password) })
                         .then(function(response) { $modalInstance.close(response);  })
                         .catch(function(response) { handleError(response); });
                };

                $scope.cancel = function () { $modalInstance.dismiss('cancel'); };
}]); 


/* Controllers */
app.controller('AuthFormController', ['$scope', '$state', '$modal', '$auth', 'Session', 
        function($scope, $state, $modal, $auth, Session) {
                 
                 $scope.data = {email:'', password:'', name:'', confirmPassword:''};

                 $scope.init = function() {
                    var modalInstance = $modal.open({
                            templateUrl: 'AuthModalContent.html',
                            controller: 'AuthModalInstanceCtrl',
                            backdrop: 'static',
                            keyboard: false,
                            size: 'lg',
                            resolve: {
                                data: function() {
                                    return $scope.data;
                                }
                            }
                    });

                    modalInstance.result.then(function (data) {
                            Session.create();  
                            $state.go('app.home');
                     }, function () {
                            $state.go('access.signin');
                     });                    
                };
      } 
]);
