'use strict';

/* Controllers */
  // signin controller
app.controller('ConfirmationController', ['$scope', '$http', '$state', 'Data', '$rootScope', '$stateParams', function($scope, $http, $state, Data, $rootScope, $stateParams) {
    
    $scope.authError = null;
    $scope.successMsg = null;
    
    $scope.doConfirmation = function(code) {
      
      $scope.authError = null;
      $scope.successMsg = null;
      console.log(code);
      // Try to login
      Data.get('confirmAdminSignup?code=' + code).then(function(response) {
        console.log(response);
        if ( response.status != 'success') {
          $scope.authError = response.message;
          $rootScope.toasterPop('error','Oops!',response.message);
        }else{
          $rootScope.toasterPop('success','Success!','Logged in successful!');
          $scope.successMsg = response.message;
        }
      }, function(x) {
        console.log(x);
        $scope.authError = 'Server Error';
        $rootScope.toasterPop('error','Oops!','Server Error');
      });
    };

    $scope.doConfirmation($stateParams.code);

  }])
;