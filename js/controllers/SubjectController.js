'use strict';

app.controller('SubjectController', ['$scope', '$rootScope', 'FTAFunctions', '$state', '$stateParams', 'Data', function($scope, $rootScope, FTAFunctions, $state, $stateParams, Data) {
    
    //initialize stuff
    $scope.subject = {};
    $scope.subjects = [];

    // Load subject list
    $scope.loadSubjectList = function() {
      Data.get('getSubjectList').then(function(results) {
        // console.log(results);
        if(results.status == "success") {
          $scope.subjects = results.subjects;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    };

    // Edit Subject
    $scope.editSubject = function(subject) {
      //check if we are on the edit page
      if($stateParams.id) {
        //edit the subject
        //console.log('editing subject...');
        Data.post('editSubject', {
            subject: subject
        }).then(function (results) {
            console.log(results);
            if(results.status == "success") {
              //subject edited. Show message
              $rootScope.toasterPop('success','Action Successful!',results.message);
            } else {
              //problemo. show error
              $rootScope.toasterPop('error','Oops!',results.message);
            }
        });
      } else {
        //create the subject
        // console.log('creating new subject...');
        Data.post('createSubject', {
            subject: subject
        }).then(function (results) {
            console.log(results);
            if(results.status == "success") {
              //subject created. Show message and go to subject list
              $state.go('app.subject-list');
              $rootScope.toasterPop('success','Action Successful!',results.message);
            } else {
              //problemo. show error
              $rootScope.toasterPop('error','Oops!',results.message);
            }
        });
      }
    };

    // delete subject
    $scope.deleteSubject = function(id) {
      if (confirm("Are you sure you want to delete this subject?")) {
        Data.get('deleteSubject?id='+id).then(function(results) {
          console.log(results);
          if(results.status == 'success') {
            $state.go('app.subject-list', {reload: true});
            $scope.loadSubjectList();
            $rootScope.toasterPop('success','Action Successful!',results.message);
          } else {
            $rootScope.toasterPop('error','Oops!',results.message);
          }
        });
      } else {
        return false;
      }
    };

    //subject-list
    if($state.current.name == 'app.subject-list') {
      //get the subject list
      $scope.loadSubjectList();
    }

    //subject-edit
    if($state.current.name == 'app.subject-edit' && $stateParams.id != '') {
      $state.current.data.pageTitle = "Edit Subject"
      //get the selected subject
      Data.get('getSubject?id='+$stateParams.id).then(function(results) {
        // console.log(results);
        if(results.status == "success") {
          $scope.subject = results.subject;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }

  }])
 ;