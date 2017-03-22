'use strict';

app.controller('ClassController', ['$scope', '$rootScope', 'FTAFunctions', '$state', '$stateParams', 'Data', function($scope, $rootScope, FTAFunctions, $state, $stateParams, Data) {
    
    //initialize stuff
    $scope.class = {};
    $scope.classes = [];
    $scope.schs = [];

    // Load school list for select
    $scope.loadSchList = function() {
      Data.get('getSchoolList').then(function(results) {
        // console.log(results);
        if(results.status == "success") {
          $scope.schs = results.schools;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    };

    // Load class list
    $scope.loadClassList = function() {
      Data.get('getClassList').then(function(results) {
         console.log(results);
        if(results.status == "success") {
          $scope.classes = results.classes;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    };

    // Edit Class
    $scope.editClass = function($class) {
      //check if we are on the edit page
      if($stateParams.id) {
        //edit the class
        //console.log('editing class...');
        Data.post('editClass', {
            class: $class
        }).then(function (results) {
            console.log(results);
            if(results.status == "success") {
              //class edited. Show message
              $rootScope.toasterPop('success','Action Successful!',results.message);
            } else {
              //problemo. show error
              $rootScope.toasterPop('error','Oops!',results.message);
            }
        });
      } else {
        //create the class
        // console.log('creating new class...');
        Data.post('createClass', {
            class: $class
        }).then(function (results) {
            console.log(results);
            if(results.status == "success") {
              //class created. Show message and go to class list
              $state.go('app.class-list');
              $rootScope.toasterPop('success','Action Successful!',results.message);
            } else {
              //problemo. show error
              $rootScope.toasterPop('error','Oops!',results.message);
            }
        });
      }
    };

    // delete class
    $scope.deleteClass = function(id) {
      if (confirm("Are you sure you want to delete this class?")) {
        Data.get('deleteClass?id='+id).then(function(results) {
          console.log(results);
          if(results.status == 'success') {
            $state.go('app.class-list', {reload: true});
            $scope.loadClassList();
            $rootScope.toasterPop('success','Action Successful!',results.message);
          } else {
            $rootScope.toasterPop('error','Oops!',results.message);
          }
        });
      } else {
        return false;
      }
    };

    //class-list
    if($state.current.name == 'app.class-list') {
      //get the class list
      $scope.loadClassList();
    };

    //class-edit
    if($state.current.name == 'app.class-edit') {
      $scope.loadSchList();
    }

    if($state.current.name == 'app.class-edit' && $stateParams.id != '') {
      $state.current.data.pageTitle = "Edit Class";
      //get the selected class
      Data.get('getClass?id='+$stateParams.id).then(function(results) {
        // console.log(results);
        if(results.status == "success") {
          $scope.class = results.class;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }

  }])
 ;