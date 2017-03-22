'use strict';

/* Controllers */
  // signin controller
app.controller('PasswordResetFormController', ['$scope', '$http', '$state', 'Data', function($scope, $http, $state, Data) {
    $scope.email = null;
    $scope.authError = null;
    $scope.successMsg = null;
    
    $scope.doReset = function(email) {
      
      $scope.authError = null;
      $scope.successMsg = null;
      
      // Send email to API to reset password
      Data.get('adminResetPassword?email='+email).then(function (results) {
        console.log(results);
        if ( results.status != 'success' ) {
          $scope.authError = results.message;
        }else{
          $scope.successMsg = results.message;
        }
      }, function(x) {
        $scope.authError = 'Server Error';
      });
    };
  }])
;