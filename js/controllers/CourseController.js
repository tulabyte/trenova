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

app.controller('CourseController', ['$scope', '$rootScope', '$modal', 'FTAFunctions', '$state', '$stateParams', '$http', 'Data', /*'FileUploader',*/ function($scope, $rootScope, $modal, FTAFunctions, $state, $stateParams, $http, Data/*, FileUploader*/) {
    
    //initialize stuff
    $scope.course = {};
    $scope.courses = [];
    $scope.subjects = [];
    $scope.classes = [];
    $scope.modules = [];
    $scope.selected_class = {};

    var uploadUrl = 'api/default/uploadFile.php';

    $scope.loadModules = function (course_id) {
      FTAFunctions.getModuleList(course_id).then(function(results) {
        if(results.status = "success") {
          console.log(results.modules);
          $scope.modules = results.modules;
        }
      });
    };

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

    // load course list
    $scope.loadCourseList = function() {
      FTAFunctions.getCourseList().then(function(results) {
        console.log(results);
        if(results.status == "success") {
          $scope.courses = results.courses;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }

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

    // Delete Module
    $scope.deleteModule = function(id) {
      if (confirm("Are you sure you want to delete this MODULE?")) {
        FTAFunctions.deleteModule(id).then(function(result) {
          if(result.status = "success") {
            $scope.loadModules($scope.course.course_id);
            $rootScope.toasterPop('success','Successful!',result.message);
          }
        });
      }
    };

    // select class when ui-select item is picked
    $scope.selectClass = function(item, model) {
      // console.log(item);
      // console.log(model);
      $scope.course.course_class_id = item.class_id;
    };

    // edit course - load classes & subjects 
    if($state.current.name == 'app.course-edit') {
      $scope.loadSubjectList();
      $scope.loadClassList();
    };
    
    //course-edit
    if(($state.current.name == 'app.course-edit' || $state.current.name == 'app.course-details') && $stateParams.id != '') {
      
      if($state.current.name == 'app.course-edit') 
        $state.current.data.pageTitle = "Edit Course";

      //get the selected course
      FTAFunctions.getCourse($stateParams.id).then(function(results) {
        console.log(results);
        if(results.status == "success") {
          $scope.course = results.course;
          $scope.course.course_price = parseFloat ($scope.course.course_price);
          $scope.loadModules($scope.course.course_id);
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }

    //course-list
    if($state.current.name == 'app.course-list') {
      $scope.loadCourseList();
    };

    // Edit Course
    $scope.editCourse = function(course, course_image) {
      //check if we are on the edit page
      // console.log(course);
      if($stateParams.id) {
        //edit the course
        //console.log('editing course...');

        if(course.changeImage) {
          //upload new file if changeImage is set
          var fd = new FormData();
          fd.append('file', course_image);
          $http.post(uploadUrl, fd, {
              transformRequest: angular.identity,
              headers: {'Content-Type': undefined}
          }).then(function(results) { 
            //delete old file
            FTAFunctions.deleteFile(course.course_image).then(function(results) {
              course.course_image = course_image.name;
              $scope.$broadcast('fileReady');
            });
          });
        } else {
          $scope.$broadcast('fileReady');
        }
        $scope.$on('fileReady', function(event){
          Data.post('editCourse', {
              course: course
          }).then(function (results) {
              console.log(results);
              if(results.status == "success") {
                //course edited. Show message
                $rootScope.toasterPop('success','Action Successful!',results.message);
              } else {
                //problemo. show error
                $rootScope.toasterPop('error','Oops!',results.message);
              }
          });
        });
        
      } else {
        //create the course
        // console.log('creating new course...');

        //upload image
        var fd = new FormData();
        fd.append('file', course_image);
        $http.post(uploadUrl, fd, {
            transformRequest: angular.identity,
            headers: {'Content-Type': undefined}
        }).then(function(results) {
           console.log(results);
          // create the course
          course.course_image = course_image.name; //take only the file name
          Data.post('createCourse', {
              course: course
          }).then(function (results) {
              console.log(results);
              if(results.status == "success") {
                //course created. Show message and go to course list
                $state.go('app.course-list');
                $rootScope.toasterPop('success','Action Successful!',results.message);
              } else {
                //problemo. show error
                $rootScope.toasterPop('error','Oops!',results.message);
              }
          });
        }, function(error){
          // console.log(error);
          // upload problem
          $rootScope.toasterPop('error','Oops!','There was a problem uploading the file!');
        });
      }
    };

    // delete course
    $scope.deleteCourse = function(id) {
      if (confirm("Are you sure you want to delete this COURSE?")) {
        FTAFunctions.deleteCourse(id).then(function(results) {
          console.log(results);
          if(results.status == 'success') {
            $state.go('app.course-list', {reload: true});
            FTAFunctions.getCourseList().then(function(results) {
              if(results.status == "success") {
                $scope.courses = results.courses;
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