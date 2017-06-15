'use strict';

app.controller('BundleController', ['$scope', '$rootScope', 'FTAFunctions', '$state', '$stateParams', '$http', 'Data', function($scope, $rootScope, FTAFunctions, $state, $stateParams, $http, Data/*, FileUploader*/) {
    
    //initialize stuff
    $scope.bundle = {};
    $scope.bundles = [];
    $scope.subjects = [];

    
    // load subject list for select
    $scope.loadSubjectList = function() {
      Data.get('getSubjectList').then(function(results) {
        console.log(results);
        if(results.status == "success") {
          $scope.subjects = results.subjects;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    };

        // load class list for select
    $scope.loadClassList = function() {
      Data.get('getSchoolList').then(function(results) {
         console.log(results);
        if(results.status == "success") {
          $scope.schools = results.schools;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    };

        // load bundle list
    $scope.loadCourseList = function() {
      FTAFunctions.getCourseList().then(function(results) {
        console.log(results);
        if(results.status == "success") {
          $scope.bundles = results.bundles;
          $scope.bundles_pend = results.bundles_pending;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }
//load bundle details
$scope.loadBundleDetails = function(){
       Data.get('getBundleDetails?id='+$stateParams.id).then(function(results) {
           console.log(results);
        if(results.status == "success") {
              $scope.bundle = results.bundle;
              $scope.course_bundles = results.course_bundles;
              $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
}
    //load bundle list
    $scope.loadBundleList = function() {
        Data.get('getBundleLists').then(function(results) {
         console.log(results);
        if(results.status == "success") {
          $scope.bundles = results.bundles;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          //$rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }

    //load a single bundle
    $scope.loadBundle = function(){
          Data.get('getBundle?id='+$stateParams.id).then(function(results) {
             console.log(results);
            if(results.status == "success") {
              $scope.bundle = results.bundle;
              $scope.bundle.bdl_price = parseInt(results.bundle.bdl_price);

              // $rootScope.toasterPop('success','Action Successful!',results.message);
            } else {
              $rootScope.toasterPop('error','Oops!',results.message);
            }
          });
    }
    if($state.current.name == 'app.bundle-edit' && $stateParams.id != '') {
      $state.current.data.pageTitle = "Edit Bundle";
      $scope.loadBundle();
    }



    // edit bundle - load classes & subjects 
    if($state.current.name == 'app.bundle-edit') {
      $scope.loadSubjectList();
      $scope.loadClassList();
    };

      if($state.current.name == 'app.bundle-list') {
          $scope.loadBundleList();
    }

    //load bundle details
      if($state.current.name == 'app.bundle-details') {
        $scope.loadBundleDetails();
    }    

$scope.backToList = function(){
  $state.go('app.bundle-list',{id:$stateParams.id});
}

/*delete a single course in the bundle*/
$scope.deleteSingleCourseBundle = function(id, bid){
  console.log(id, bid);
  var apiURL = 'deleteSingleCourseBundle?id='+id+'&bid='+bid;
   if (confirm("Are you sure you want to delete this COURSE?")) {
        Data.get(apiURL).then(function(result) {
          if(result.status = "success") {
            $rootScope.toasterPop('success','Successful!',result.message);
            $scope.loadBundleDetails();
          }
        });
      }
}
    $scope.editBundleModule = function(bundle) {
    bundle.bundle_id = $scope.bundle.bundle_id;
    /*console.log(bundle.bundle_id);*/
    if(bundle.q_id) {
      //edit bundle
      Data.post('editBundleModule', 
        { bundle: bundle }
      ).then(function(results) {
        if(results.status = "success") {
          $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          //error
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      }, function(error) {
        console.log(error);
      });
    } else {
      //create new bundle
      Data.post('createBundleModule', 
        { bundle: bundle }
      ).then(function(results) {
        console.log(results);
        if(results.status = "success") {
          $rootScope.toasterPop('success','Action Successful!',results.message);
          $state.go('app.bundle-edit',{id:$stateParams.id , reload: true});
        } else {
          //error
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      }, function(error) {
        console.log(error);
      });
    }
  };

$scope.editBundleAndExit = function(bundle){
    bundle.bundle_id = $scope.bundle.bundle_id;
        if(bundle.q_id) {
      //edit bundle
      Data.post('editBundleModule', 
        { bundle: bundle }
      ).then(function(results) {
        if(results.status = "success") {
          $rootScope.toasterPop('success','Action Successful!',results.message);
          $state.go('app.bundle-list',{id:$stateParams.id});
        } else {
          //error
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      }, function(error) {
        console.log(error);
      });
    } else {
      //create new bundle
      Data.post('createBundleModule', 
        { bundle: bundle }
      ).then(function(results) {
        console.log(results);
        if(results.status = "success") {
          $rootScope.toasterPop('success','Action Successful!',results.message);
          $state.go('app.bundle-list',{id:$stateParams.id});
        } else {
          //error
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      }, function(error) {
        console.log(error);
      });
    }

}

        // Delete Bundle
    $scope.deleteBundle = function(id) {
      if (confirm("Are you sure you want to delete this QUESTION?")) {
        FTAFunctions.deleteBundleModule(id).then(function(result) {
          if(result.status = "success") {
            $scope.loadLessons($scope.bundle.bundle_id);
            $rootScope.toasterPop('success','Successful!',result.message);
          }
        });
      }
    };

$scope.editBundleTitle = function(bundle){
  console.log(bundle);
      Data.post('editBundleTitle', 
        { bundle: bundle }
      ).then(function(results) {
        console.log(results);
        if(results.status = "success") {
          $rootScope.toasterPop('success','Action Successful!',results.message);
          //$state.go('app.bundle-list',{id:$stateParams.id});
        } else {
          //error
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      }, function(error) {
        console.log(error);
      });
}

//creating the bundle 
$scope.editBundle = function(bundle){
  console.log(bundle);
      Data.post('createNewBundle', 
        { bundle: bundle }
      ).then(function(results) {
        console.log(results);
        if(results.status = "success") {
          $rootScope.toasterPop('success','Action Successful!',results.message);
          //$state.go('app.bundle-list',{id:$stateParams.id});
        } else {
          //error
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      }, function(error) {
        console.log(error);
      });
    }
$scope.updateQuizLimit = function(bundle){
  console.log(bundle);
      Data.post('updateQuizLimit', 
        { bundle: bundle }
      ).then(function(results) {
        console.log(results);
        if(results.status = "success") {
          $rootScope.toasterPop('success','Action Successful!',results.message);
          //$state.go('app.bundle-list',{id:$stateParams.id});
        } else {
          //error
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      }, function(error) {
        console.log(error);
      });
}


  }])
 ;