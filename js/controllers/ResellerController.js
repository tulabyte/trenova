'use strict';

app.controller('ResellerController', ['$scope', '$rootScope', 'FTAFunctions', '$state', '$stateParams', 'Data', function($scope, $rootScope, FTAFunctions, $state, $stateParams, Data) {
    
    //initialize stuff
    $scope.reseller = {};
    $scope.reseller.ad_type = "RESELLER";
    $scope.resellers = [];

    // Edit Reseller
    $scope.editReseller = function(reseller) {
      //check if we are on the edit page
      if($stateParams.id) {
        //edit
        console.log('editing reseller...');
        Data.post('editAdmin', {
            admin: reseller
        }).then(function (results) {
            console.log(results);
            if(results.status == "success") {
              //reseller edited. Show message
              $rootScope.toasterPop('success','Action Successful!',results.message);
            } else {
              //problemo. show error
              $rootScope.toasterPop('error','Oops!',results.message);
            }
        });
      } else {
        //create
        // console.log('creating new...');
        Data.post('createAdmin', {
            admin: reseller
        }).then(function (results) {
            console.log(results);
            if(results.status == "success") {
              //reseller created. Show message and go to reseller list
              $state.go('app.reseller-list');
              $rootScope.toasterPop('success','Action Successful!',results.message);
            } else {
              //problemo. show error
              $rootScope.toasterPop('error','Oops!',results.message);
            }
        });
      }
    };

    // delete reseller
    $scope.deleteReseller = function(id) {
      if (confirm("Are you sure you want to delete this reseller? THIS WILL REMOVE ALL CONTENT/INFO CONNECTED TO THIS RESELLER!!!")) {
        FTAFunctions.deleteAdmin(id).then(function(results) {
          if(results.status == 'success') {
            $state.go('app.reseller-list', {reload: true});
            $scope.loadResellerList();
            $rootScope.toasterPop('success','Action Successful!',results.message);
          } else {
            $rootScope.toasterPop('error','Oops!',results.message);
          }
        });
      } else {
        return false;
      }
    };

    $scope.loadResellerList = function() {
      //get the resellers
      FTAFunctions.getResellerList().then(function(results) {
         console.log(results);
        if(results.status == "success") {
          $scope.resellers = results.resellers;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    };


    $scope.loadResellerWaitingList = function() {
      //get the resellers
      FTAFunctions.getAdminList('RESELLER','PENDING').then(function(results) {
        // console.log(results);
        if(results.status == "success") {
          $scope.resellers = results.admins;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    };

    //mark as paid
    $scope.payResellerCommission = function(id) {
      if (confirm("Are you sure you want to Mark as Paid?")) {
         FTAFunctions.markResellerCommission(id).then(function(results) {
          console.log(results);
          if(results.status == 'success') {
            //load list
      switch($state.current.name){
         case'app.reseller-list-commission' :
        FTAFunctions.getResellerCommission().then(function(results) {
        console.log(results);
        if(results.status == "success") {
          $scope.resellers = results.resellers;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
        break;
        case'app.reseller-details' :
      FTAFunctions.getResellerDetails($stateParams.id).then(function(results) {
        console.log(results);
        if(results.status == "success") {
          $scope.reseller = results.reseller;
          $scope.reseller_referral = results.reseller_referral;
          $scope.reseller_com = results.reseller_com;
          $scope.reseller_pay = results.reseller_pay;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
      break;
            }

           $rootScope.toasterPop('success','Action Successful!',results.message);
          } else {
            $rootScope.toasterPop('error','Oops!',results.message);
          }
        });
      } else {
        return false;
      }
    };

    // verify reseller
    $scope.verifyReseller = function(reseller, index) {
      if(confirm("Are you sure you want to VERIFY this reseller?")) {
        FTAFunctions.verifyAdmin(reseller.ad_id).then(function(results) {
          console.log(results);
          if(results.status == "success") {
            // verified, reload the list and show success message
            $rootScope.toasterPop('success','Action Successful!',results.message);
            $state.go('app.reseller-list', {reload: true});
            $scope.loadResellerList();
          } else {
            // not verified, show error
            $rootScope.toasterPop('error','Oops!',results.message);
          }
        });
      }
    }

    //reseller-list state
    if($state.current.name == 'app.reseller-list') {
      $scope.loadResellerList();
    }

    //reseller-list-pending state
    if($state.current.name == 'app.reseller-list-pending') {
      $scope.loadResellerWaitingList();
    }

    //reseller-edit state
    if($state.current.name == 'app.reseller-edit' && $stateParams.id != '') {
      $state.current.data.pageTitle = "Edit Reseller"
      //get the selected reseller
      FTAFunctions.getAdmin($stateParams.id).then(function(results) {
        // console.log(results);
        if(results.status == "success") {
          $scope.reseller = results.admin;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }

//reseller details
    if($state.current.name == 'app.reseller-details') {
       //get the reseller details
       console.log('reseller details');
       FTAFunctions.getResellerDetails($stateParams.id).then(function(results) {
        console.log(results);
        if(results.status == "success") {
          $scope.reseller = results.reseller;
          $scope.reseller_referral = results.reseller_referral;
          $scope.reseller_com = results.reseller_com;
          $scope.reseller_pay = results.reseller_pay;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }

//commission
    if($state.current.name == 'app.reseller-list-commission') {
       //get the reseller details
       console.log('reseller details');
       FTAFunctions.getResellerCommission().then(function(results) {
        console.log(results);
        if(results.status == "success") {
          $scope.resellers = results.resellers;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }

//reseller's dashboard
    if($state.current.name == 'app.re-dashboard') {
       //get the reseller details
       console.log('reseller details');
       FTAFunctions.getResellerDashboard().then(function(results) {
        console.log(results);
        if(results.status == "success") {
          $scope.reseller = results.reseller;
          $scope.reseller_referral = results.reseller_referral;
          $scope.reseller_pay = results.reseller_pay;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }

    //reseller's paid list
    if($state.current.name == 'app.re-dash-paid') {
       //get the reseller details
       console.log('reseller details');
       FTAFunctions.getResellerPaid().then(function(results) {
        console.log(results);
        if(results.status == "success") {
          $scope.reseller_pay = results.reseller_pay;
          $scope.reseller_t = results.reseller_t;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }

        //reseller's unpaid list
    if($state.current.name == 'app.re-dash-list') {
       //get the reseller details
       console.log('reseller details');
       FTAFunctions.getResellerUnPaid().then(function(results) {
        console.log(results);
        if(results.status == "success") {
          $scope.reseller_upay = results.reseller_upay;
          $scope.reseller_t = results.reseller_t;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }


    

  }])
 ;