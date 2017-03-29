'use strict';

app.controller('UserController', ['$scope', '$rootScope', 'FTAFunctions', '$state', '$stateParams', 'Data', '$moment', function($scope, $rootScope, FTAFunctions, $state, $stateParams, Data, $moment) {
    
    //initialize stuff
    $scope.user = {};
    $scope.users = [];

    //console.log('ID: ' + $stateParams.id);

    //user-list
    if($state.current.name == 'app.user-list') {
      //get the selected user
      FTAFunctions.getUserList().then(function(results) {
         console.log(results);
        if(results.status == "success") {
          $scope.users = results.users;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }

     //feedback-list
    if($state.current.name == 'app.feedback-list') {
      //get the selected user
      FTAFunctions.getFeedbackList().then(function(results) {
         console.log(results);
        if(results.status == "success") {
          $scope.feedback = results.feedback;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }

         //bc-list
    if($state.current.name == 'app.broadcast-list') {
      //get the selected user
      FTAFunctions.getFeedbackList().then(function(results) {
         console.log(results);
        if(results.status == "success") {
          $scope.broadcast = results.broadcast;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          //$rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }


      //bc-message
      $scope.newBroadcast = function(broadcast) {

          Data.post('createBroadCast', {
            broadcast: broadcast
        }).then(function (results) {
            console.log(results);
            if(results.status == "success") {
              //broadcast successful. Show message
                $state.go('app.dashboard');
                $rootScope.toasterPop('success','Action Successful!',results.message);
              } else {
                //problemo. show error
                $rootScope.toasterPop('error','Oops!',results.message);
              }
          });
    };



      //broadcast and feedback-details
    if($state.current.name == 'app.feedback-details') {
       //get the feedback details
       console.log('feedback details');
       FTAFunctions.getFeedbackDetails($stateParams.id).then(function(results) {
        console.log(results);
        if(results.status == "success") {
          $scope.feedback = results.feedback;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }
     
           //broadcast details
    if($state.current.name == 'app.broadcast-message') {
       //get the feedback details
       console.log('feedback details');
       FTAFunctions.getFeedbackDetails($stateParams.id).then(function(results) {
        console.log(results);
        if(results.status == "success") {
          $scope.broadcast = results.broadcast;
          console.log($stateParams.id);
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          //$rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }

      //user-details
    if($state.current.name == 'app.user-details') {
       //get the user details
       console.log('user details');
       FTAFunctions.getUser($stateParams.id).then(function(results) {
        console.log(results);
        if(results.status == "success") {
          $scope.user = results.user;
          $scope.user_sub = results.user_sub;
          $scope.user_esub = results.user_esub;
          $scope.user_pmt = results.user_pmt;
          $scope.user_ord = results.user_ord;
          $scope.user_pymt = results.user_pymt;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }


    //user-edit
    if($state.current.name == 'app.user-edit' && $stateParams.id != '') {
      $state.current.data.pageTitle = "Edit User"
      //get the selected user
      FTAFunctions.getUser($stateParams.id).then(function(results) {
        // console.log(results);
        if(results.status == "success") {
          $scope.user = results.user;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }

    // Edit User
    $scope.editUser = function(user) {
      //check if we are on the edit page
      if($stateParams.id) {
        //edit the user
        //console.log('editing user...');
        Data.post('editUser', {
            user: user
        }).then(function (results) {
            console.log(results);
            if(results.status == "success") {
              //user edited. Show message
              $rootScope.toasterPop('success','Action Successful!',results.message);
            } else {
              //problemo. show error
              $rootScope.toasterPop('error','Oops!',results.message);
            }
        });
      } else {
        //create the user
        // console.log('creating new user...');
        Data.post('createUser', {
            user: user
        }).then(function (results) {
            console.log(results);
            if(results.status == "success") {
              //user created. Show message and go to user list
              $state.go('app.user-list');
              $rootScope.toasterPop('success','Action Successful!',results.message);
            } else {
              //problemo. show error
              $rootScope.toasterPop('error','Oops!',results.message);
            }
        });
      }
    };

    // delete user
    $scope.deleteUser = function(id) {
      if (confirm("Are you sure you want to delete this user?")) {
        FTAFunctions.deleteUser(id).then(function(results) {
          if(results.status == 'success') {
            $state.go('app.user-list', {reload: true});
            FTAFunctions.getUserList().then(function(results) {
              if(results.status == "success") {
                $scope.users = results.users;
              }
            });
            $rootScope.toasterPop('success','Action Successful!',results.message);
          } else {
            $rootScope.toasterPop('error','Oops!',results.message);
          }
        });
      } else {
        return false;
      }
    };

    //$rootScope.toasterPop('success','Testing','Toaster.js Works in FORM.JS !!!');

  }])
 ;