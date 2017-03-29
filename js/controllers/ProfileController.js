'use strict';

app.directive('fileModel', ['$parse', function ($parse) {
    return {
        restrict: 'A',
        link: function(scope, element, attrs) {
            var model = $parse(attrs.fileModel);
            var modelSetter = model.assign;
            
            element.bind('change', function(){
                scope.$apply(function(){
                    modelSetter(scope, element[0].files[0]);
                });
            });
        }
    };
}]);


app.controller('ProfileController', ['$scope', '$rootScope', 'FTAFunctions', '$state', '$stateParams', '$http', 'Data', function($scope, $rootScope, FTAFunctions, $state, $stateParams, $http,Data) {
    
    //initialize stuff

    var uploadUrl = 'api/default/uploadFile.php';

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

$scope.editProfile = function(profile, ad_photo) {
       var fd = new FormData();
        fd.append('file', ad_photo);
        $http.post(uploadUrl, fd, {
            transformRequest: angular.identity,
            headers: {'Content-Type': undefined}
        }).then(function(results) {
           console.log(results);
          // create the photo
          profile.ad_photo = ad_photo.name; //take only the file name
      //editing profile
          Data.post('editProfile', {
            profile: profile
        }).then(function (results) {
            console.log(results);
            if(results.status == "success") {
              //profile edited. Show message
                $state.go('app.dashboard');
                $rootScope.toasterPop('success','Action Successful!',results.message);
              } else {
                //problemo. show error
                $rootScope.toasterPop('error','Oops!',results.message);
              }
          });
        }, function(error){
          // console.log(error);
          // upload problem
          $rootScope.toasterPop('error','Oops!','There was a problem uploading the file!');
        });
      
    };
        


if($state.current.name == 'app.profile-edit') {
      //get profile details
      FTAFunctions.getProfile().then(function(results) {
         console.log(results);
        if(results.status == "success") {
          $scope.profile = results.profile;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }


  }])
 ;