'use strict';

app.controller('BundleController', ['$scope', '$rootScope', 'FTAFunctions', '$state', '$stateParams', '$http', 'Data', function($scope, $rootScope, FTAFunctions, $state, $stateParams, $http, Data/*, FileUploader*/) {
    
    //initialize stuff
    $scope.bundle = {};
    $scope.bundles = [];
    $scope.subjects = [];
    $scope.courses = [];
    $scope.schools = [];

/* Load All Stuffs */

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

//load course list for select
$scope.loadCourseList = function(){
      Data.get('getCourseBundleList').then(function(results) {
         console.log(results);
        if(results.status == "success") {
          $scope.courses = results.courses;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
  };

        // load school list for select
    $scope.loadSchoolList = function() {
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
      $scope.loadSchoolList();
      $scope.loadCourseList();
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

 
//create and edit bundle 
$scope.editBundle = function(bundle){
    console.log(bundle);
    //check to edit
    if ($scope.bundle.bdl_id) {
        Data.post('editExistingBundle', 
            { bundle: bundle }
          ).then(function(results) {
            console.log(results);
            if(results.status = "success") {
              $rootScope.toasterPop('success','Action Successful!',results.message);
            } else {
              $rootScope.toasterPop('error','Oops!',results.message);
            }
          }, function(error) {
            console.log(error);
          });
    }else{
      //create new bundle
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

}

// Delete Bundle
    $scope.deleteBundle = function(id) {
  var apiURL = 'deleteBundle?id='+id;
   if (confirm("Are you sure you want to delete this BUNDLE?")) {
        Data.get(apiURL).then(function(result) {
          if(result.status = "success") {
            $rootScope.toasterPop('success','Successful!',result.message);
            $scope.loadBundleList();
          }
        });
      }
    };

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



  }])
 ;