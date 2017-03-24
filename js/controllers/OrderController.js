'use strict';

app.controller('OrderController', ['$scope', '$rootScope', 'FTAFunctions', '$state', '$stateParams', 'Data', '$moment', function($scope, $rootScope, FTAFunctions, $state, $stateParams, Data, $moment) {
    
    //initialize stuff
    $scope.order = {};
    $scope.orders = [];

    //console.log('ID: ' + $stateParams.id);

    //order-list
    if($state.current.name == 'app.order-list') {
      //get the selected user
      FTAFunctions.getOrderList().then(function(results) {
        // console.log(results);
        if(results.status == "success") {
          $scope.orders = results.orders;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    };
      
    //order-details
    if($state.current.name == 'app.order-details') {
      //get the selected user
      FTAFunctions.getOrder($stateParams.id).then(function(results) {
         console.log(results);
        if(results.status == "success") {
          $scope.order = results.order;
          $scope.order_item = results.order_item;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    };

    // delete order
    $scope.deleteOrder = function(id) {
      if (confirm("Are you sure you want to delete this order? THIS WILL REMOVE ALL CONTENT/INFO CONNECTED TO THIS ORDER!!!")) {
        FTAFunctions.deleteOrder(id).then(function(results) {
          if(results.status == 'success') {
            $state.go('app.order-list', {reload: true});
            FTAFunctions.getOrderList().then(function(results) {
         console.log(results);
        if(results.status == "success") {
          $scope.orders = results.orders;
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

  }])
 ;