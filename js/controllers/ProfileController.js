'use strict';

app.controller('ProfileController', ['$scope', '$rootScope', 'FTAFunctions', '$state', '$stateParams', 'Data', function($scope, $rootScope, FTAFunctions, $state, $stateParams, Data) {
    
    //initialize stuff

    // Edit Category
    $scope.changePassword = function(password) {
      if(password.new != password.new2) {
        $rootScope.toasterPop('error','Oops!',"The new passwords don't match. Please check and correct");
        return false;
      }

      Data.post('changeAdminPassword', {
          password: password
      }).then(function (results) {
          console.log(results);
          if(results.status == "success") {
            //category edited. Show message
            $rootScope.toasterPop('success','Successful!',results.message);
          } else {
            //problemo. show error
            $rootScope.toasterPop('error','Oops!',results.message);
          }
      });
    };

  }])
 ;