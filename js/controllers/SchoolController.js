'use strict';

app.controller('SchoolController', ['$scope', '$rootScope', 'FTAFunctions', '$state', '$stateParams', 'Data', function($scope, $rootScope, FTAFunctions, $state, $stateParams, Data) {
    
    //initialize stuff
    $scope.school = {};
    $scope.schools = [];

    // Load school list
    $scope.loadSchoolList = function() {
      Data.get('getSchoolList').then(function(results) {
        // console.log(results);
        if(results.status == "success") {
          $scope.schools = results.schools;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    };

    // Edit School
    $scope.editSchool = function(school) {
      //check if we are on the edit page
      if($stateParams.id) {
        //edit the school
        //console.log('editing school...');
        Data.post('editSchool', {
            school: school
        }).then(function (results) {
            console.log(results);
            if(results.status == "success") {
              //school edited. Show message
              $rootScope.toasterPop('success','Action Successful!',results.message);
            } else {
              //problemo. show error
              $rootScope.toasterPop('error','Oops!',results.message);
            }
        });
      } else {
        //create the school
        // console.log('creating new school...');
        Data.post('createSchool', {
            school: school
        }).then(function (results) {
            console.log(results);
            if(results.status == "success") {
              //school created. Show message and go to school list
              $state.go('app.school-list');
              $rootScope.toasterPop('success','Action Successful!',results.message);
            } else {
              //problemo. show error
              $rootScope.toasterPop('error','Oops!',results.message);
            }
        });
      }
    };

    // delete school
    $scope.deleteSchool = function(id) {
      if (confirm("Are you sure you want to delete this school?")) {
        Data.get('deleteSchool?id='+id).then(function(results) {
          console.log(results);
          if(results.status == 'success') {
            $state.go('app.school-list', {reload: true});
            $scope.loadSchoolList();
            $rootScope.toasterPop('success','Action Successful!',results.message);
          } else {
            $rootScope.toasterPop('error','Oops!',results.message);
          }
        });
      } else {
        return false;
      }
    };

    //school-list
    if($state.current.name == 'app.school-list') {
      //get the school list
      $scope.loadSchoolList();
    }

    //school-edit
    if($state.current.name == 'app.school-edit' && $stateParams.id != '') {
      $state.current.data.pageTitle = "Edit School"
      //get the selected school
      Data.get('getSchool?id='+$stateParams.id).then(function(results) {
        // console.log(results);
        if(results.status == "success") {
          $scope.school = results.school;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }

  }])
 ;