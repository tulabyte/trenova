'use strict';

app.controller('ForumController', ['$scope', '$rootScope', 'FTAFunctions', '$state', '$stateParams', '$http', 'Data', function($scope, $rootScope, FTAFunctions, $state, $stateParams, $http, Data/*, FileUploader*/) {
    
    //initialize stuff
    $scope.courses = [];

/* Load All Stuffs */
//load course list for select
$scope.loadCourseList = function(){
      Data.get('getCourseList').then(function(results) {
         console.log(results);
        if(results.status == "success") {
          $scope.courses = results.courses;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
  };


//load forum details
$scope.loadForumDetails = function(){
       Data.get('getForumComment?id='+$stateParams.id).then(function(results) {
           console.log(results);
        if(results.status == "success") {
              $scope.course = results.course;
              $scope.forum_comments = results.forum_comments;
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

    $scope.editForumComment = function(forum) {
      //create comment
      $scope.forum.course_id = $scope.course.course_id;
      console.log($scope.forum.course_id);
      Data.post('createForumComment', 
        { forum: forum }
      ).then(function(results) {
        if(results.status = "success") {
          $rootScope.toasterPop('success','Action Successful!',results.message);
          $state.reload();
        } else {
          //error
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      }, function(error) {
        console.log(error);
      });
    }

      if($state.current.name == 'app.forum-list') {
          $scope.loadCourseList();
    }

    if($state.current.name == 'app.forum-details') {
          $scope.loadForumDetails();
    }

$scope.backToList = function(){
  $state.go('app.bundle-list',{id:$stateParams.id});
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