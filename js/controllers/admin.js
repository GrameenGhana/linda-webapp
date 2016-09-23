'use strict';


app.controller('UserController',
              ['$rootScope', '$scope', 'Session', 'AppService','$interval', function($rootScope, $scope, Session, AppService, $interval) {
/*
        $scope.message = '';

        $scope.messages = Session.getMessages($scope.moveid);

        var updatesLoaded = function(updates) {
            if (updates.length > 0) { Session.updateMessages($scope.moveid, updates); }
            $scope.messages = Session.getMessages($scope.moveid);
        };

        var getMessageUpdates = function() {
            Movester.getMessages($scope.moveid, $scope.messages[$scope.messages.length - 1]).then(updatesLoaded);
        };

        $scope.createMessage = function() {
            if ($scope.message != '') {
                var message = {
                    user_id: Session.getUserId(),
                    move_id: $scope.moveid,
                    content: $scope.message,
                    updated_at: Date.now()
                };
                Movester.createMessage(message).then(getMessageUpdates);
                $scope.message = '';
            }
        };
        var stop = $interval(getMessageUpdates, 3000);

        $scope.$on('$destroy', function() {
              $interval.cancel(stop);
        });

        $rootScope.$on('move-selected', function(scope, moveid) {
              $scope.moveid = moveid;
              $scope.messages = Session.getMessages($scope.moveid);
        });
        */
}]);