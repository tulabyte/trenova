'use strict';

/* Controllers */
  // signin controller
app.controller('SigninFormController', ['$scope', '$http', '$state', 'Data', '$rootScope', function($scope, $http, $state, Data, $rootScope) {
    $scope.admin = {};
    $scope.authError = null;
    $scope.successMsg = null;
    
    $scope.login = function(admin) {
      
      $scope.authError = null;
      $scope.successMsg = null;
      console.log(admin);
      // Try to login
      Data.post('adminLogin', {
        admin: admin
      }).then(function(response) {
        console.log(response);
        if ( response.status != 'success' ) {
          // sign in failed!
          $scope.authError = response.message;
          $rootScope.toasterPop('error','Oops!',response.message);
        }else{
          // sign in successful
          $rootScope.toasterPop('success','Success!','Logged in successful!');
          $scope.successMsg = response.message;
          // authenticate the user
          $rootScope.trenova_user = response.trenova_user;
          $rootScope.authenticated = true;

          switch ($rootScope.trenova_user.ad_type) {
            case 'RESELLER' : $state.go('app.re-dashboard');
            break;
            case 'AGENT' : $state.go('app.agent-dashboard');
            break;
            
            default : $state.go('app.dashboard');
          }
        }
      }, function(x) {
        console.log(x);
        $scope.authError = 'Server Error';
        $rootScope.toasterPop('error','Oops!','Server Error');
      });
    };
  }])
;