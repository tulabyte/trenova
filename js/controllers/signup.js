'use strict';

/* Controllers */
  // signin controller
app.controller('SignupFormController', ['$scope', '$http', '$state', 'Data', '$rootScope', function($scope, $http, $state, Data, $rootScope) {
    $scope.admin = {};
    $scope.admin.ad_type = 'AGENT';
    $scope.authError = null;
    $scope.successMsg = null;
    
    $scope.signup = function(admin) {
      
      $scope.authError = null;
      $scope.successMsg = null;
      console.log(admin);
      // Try to signup
      Data.post('adminSignUp', {
        admin: admin
      }).then(function(response) {
        console.log(response);
        if ( response.status != 'success' || !response.admin_id ) {
          $scope.authError = response.message;
          $rootScope.toasterPop('error','Oops!',response.message);
        }else{
          $state.go('access.signin');
          $rootScope.toasterPop('success','Success!',response.message);
          $scope.successMsg = response.message;
        }
      }, function(x) {
        console.log(x);
        $scope.authError = 'Server Error';
        $rootScope.toasterPop('error','Oops!','Server Error');
      });
    };
  }])
;