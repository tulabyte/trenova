'use strict';

app.controller('SubscriptionController', ['$scope', '$rootScope', '$modal', 'FTAFunctions', '$state', '$stateParams', '$http', 'Data', function($scope, $rootScope, $modal, FTAFunctions, $state, $stateParams, $http, Data/*, FileUploader*/) {
    
    //initialize stuff
    $scope.subscriptions = [];

    // pause subscription
    $scope.pauseSubscription = function(id) {
      if(!confirm("Are you sure you want to PAUSE this subscription?")) {
        return false;
      }
      Data.get('pauseSubscription?id='+id).then(function(results) {
        console.log(results);
        if(results.status == "success") {
           FTAFunctions.getSubscriptionList().then(function(results) {
        //console.log(results);
        if(results.status == "success") {
          $scope.subscriptions = results.subscriptions;
        }
      });
          // pause item in the list
          /*for(var i=0; i<$scope.subscriptions.length; i++) {
            if($scope.subscriptions[i].sub_id == id) {
              $scope.subscriptions.splice(i,1);
              break;
            }
          }*/
          $rootScope.toasterPop('success','Action Successful',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    };

    // resume subscription
    $scope.resumeSubscription = function(id) {
      if(!confirm("Are you sure you want to RESUME this subscription?")) {
        return false;
      }
      Data.get('resumeSubscription?id='+id).then(function(results) {
        console.log(results);
        if(results.status == "success") {
          FTAFunctions.getSubscriptionList().then(function(results) {
        //console.log(results);
        if(results.status == "success") {
          $scope.subscriptions = results.subscriptions;
        }
      });
          // pause item in the list
          /*for(var i=0; i<$scope.subscriptions.length; i++) {
            if($scope.subscriptions[i].sub_id == id) {
              $scope.subscriptions.splice(i,1);
              break;
            }
          }*/
          $rootScope.toasterPop('success','Action Successful',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    };


    
    //subscription-list
    if($state.current.name == 'app.sub-list') {
      //get the selected subscription
      FTAFunctions.getSubscriptionList().then(function(results) {
        console.log(results);
        if(results.status == "success") {
          $scope.subscriptions = results.subscriptions;
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }

  }])
 ;