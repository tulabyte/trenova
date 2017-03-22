'use strict';

/*Using method outlined @ https://uncorkedstudios.com/blog/multipartformdata-file-upload-with-angularjs for file upload */

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

app.controller('ModalInstanceCtrl', ['$scope', '$modalInstance', 'course', 'module', 'Data', function($scope, $modalInstance, course, module, Data) {
  $scope.course = course;
  if(module) {
    $scope.module = module;
    $scope.module.mod_position = parseInt($scope.module.mod_position);
    console.log(module);
  } else {
    $scope.module = {};
  }
  $scope.error = undefined;

  $scope.editModule = function(module) {
    module.mod_course_id = $scope.course.course_id;
    console.log(module);
    if(module.mod_id) {
      //edit module
      Data.post('editModule', 
        { module: module }
      ).then(function(results) {
        if(results.status = "success") {
          $modalInstance.close("OK");
        } else {
          //error
          $scope.error = results.message;
        }
      }, function(error) {
        console.log(error);
      });
    } else {
      //create new module
      Data.post('createModule', 
        { module: module }
      ).then(function(results) {
        if(results.status = "success") {
          $modalInstance.close("OK");
        } else {
          //error
          $scope.error = results.message;
        }
      }, function(error) {
        console.log(error);
      });
    }
  };

  $scope.ok = function () {
    $modalInstance.close($scope.selected.item);
  };

  $scope.cancel = function () {
    $modalInstance.dismiss('cancel');
  };
}])
;

app.controller('PaymentController', ['$scope', '$rootScope', '$modal', 'FTAFunctions', '$state', '$stateParams', '$http', 'Data', function($scope, $rootScope, $modal, FTAFunctions, $state, $stateParams, $http, Data/*, FileUploader*/) {
    
    //initialize stuff
    $scope.payments = [];

    $scope.openModal = function (module = null) {
      var modalInstance = $modal.open({
        templateUrl: 'myModalContent.html',
        controller: 'ModalInstanceCtrl',
        resolve: {
          course: function () {
            return $scope.course;
          },
          module: function (){
            return module;
          }
        }
      });

      modalInstance.result.then(function (status) {
        if(status == 'OK') {
          $scope.loadModules($scope.course.course_id);
        }
      }, function () {
        console.log('Modal dismissed at: ' + new Date());
      });
    };

    // deny payment
    $scope.denyPayment = function(payment) {
      if(!confirm("Are you sure you want to DENY this payment?")) {
        return false;
      }
      Data.get('denyPayment?id='+payment.pay_id).then(function(results) {
        console.log(results);
        if(results.status == "success") {
          // remove item from the list
          for(var i=0; i<$scope.payments.length; i++) {
            if($scope.payments[i].pay_id == payment.pay_id) {
              $scope.payments.splice(i,1);
              break;
            }
          }
          $rootScope.toasterPop('success','Action Successful',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    };

    // deny payment
    $scope.confirmPayment = function(payment) {
      if(!confirm("Are you sure you want to CONFIRM this payment? THIS WILL AUTOMATICALLY ACTIVATE ALL SUBSCRIPTIONS ASSOCIATED WITH THIS PAYMENT!!!")) {
        return false;
      }
      Data.get('confirmPayment?id='+payment.pay_id).then(function(results) {
        console.log(results);
        if(results.status == "success") {
          // remove item from the list
          for(var i=0; i<$scope.payments.length; i++) {
            if($scope.payments[i].pay_id == payment.pay_id) {
              $scope.payments.splice(i,1);
              break;
            }
          }
          $rootScope.toasterPop('success','Action Successful',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    };

    

    if($state.current.name == 'app.payment-list') {
      //payment -list
      Data.get('getPaymentList').then(function(results) {
        console.log(results);
        if(results.status == "success") {
          $scope.payments = results.payments;
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }

    if($state.current.name == 'app.bank-waiting-list') {
      //bank waiting -list
      Data.get('getBankWaitingList').then(function(results) {
        console.log(results);
        if(results.status == "success") {
          $scope.payments = results.payments;
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }

  }])
 ;