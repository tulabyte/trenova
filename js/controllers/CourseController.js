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

app.controller('ModalInstanceCtrl', ['$scope', 'Upload', '$rootScope', '$modalInstance', 'course', 'module', 'Data', function($scope, Upload, $rootScope, $modalInstance, course, module, Data) {
 var uploadVideoUrl = 'api/default/uploadVideo.php';

  $scope.course = course;
  if(module) {
    $scope.module = module;
    $scope.module.less_number = parseInt($scope.module.less_number);
    console.log(module);
  } else {
    $scope.module = {};
  }
  $scope.error = undefined;

  $scope.editModule = function(file, module) {
    module.less_course_id = $scope.course.course_id;
    $scope.module.less_video = file.name;
    //console.log(module);
//    console.log(file);
    if(module.less_id) {
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
          $scope.upload($scope.file, $scope.module.less_video);
//          $modalInstance.close("OK");
        } else {
          //error
          $scope.error = results.message;
        }
      }, function(error) {
        console.log(error);
      });
    }
  };

  

  /*code to upload the file via ngf*/
     $scope.upload = function (file, video_name) {
        Upload.upload({
            url: uploadVideoUrl,
            data: {file: file, 'video': video_name}
        }).then(function (resp) { 
//            console.log('Success ' + resp.config.data.file.name + 'uploaded. Response: ' + resp.data);
        }, function (resp) {
  //          console.log('Error status: ' + resp.status);
        }, function (evt) {
            var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
            $scope.video_progress = progressPercentage;
            if (progressPercentage == 100){
            $rootScope.toasterPop('Video Upload Successful!');
            $modalInstance.close("OK");
              }
    //        console.log('progress: ' + progressPercentage + '% ' + evt.config.data.file.name);
        });
    };


    $scope.editQuestionModule = function(question) {
    question.course_id = $scope.course.course_id;
    /*console.log(question.course_id);*/
    if(question.q_id) {
      //edit question
      Data.post('editQuestionModule', 
        { question: question }
      ).then(function(results) {
        if(results.status = "success") {
          $rootScope.toasterPop('success','Action Successful!',results.message);
          $modalInstance.close("OK");
        } else {
          //error
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      }, function(error) {
        console.log(error);
      });
    } else {
      //create new question
      Data.post('createQuestionModule', 
        { question: question }
      ).then(function(results) {
        console.log(results);
        if(results.status = "success") {
          $rootScope.toasterPop('success','Action Successful!',results.message);
          $modalInstance.close("OK");
        } else {
          //error
          $rootScope.toasterPop('error','Oops!',results.message);
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

app.controller('CourseController', ['$scope', 'Upload', '$rootScope', '$modal', 'FTAFunctions', '$state', '$stateParams', '$http', 'Data', /*'FileUploader',*/ function($scope, Upload, $rootScope, $modal, FTAFunctions, $state, $stateParams, $http, Data/*, FileUploader*/) {
    
    //initialize stuff
    $scope.course = {};
    $scope.courses = [];
    $scope.subjects = [];
    $scope.classes = [];
    $scope.lessons = [];
    $scope.selected_class = {};

    var uploadUrl = 'api/default/uploadFile.php';
    var uploadVideoUrl = 'api/default/uploadVideo.php';

    $scope.loadLessons = function (course_id) {
      FTAFunctions.getModuleList(course_id).then(function(results) {
        if(results.status = "success") {
          console.log(results);
          $scope.lessons = results.lessons;
          $scope.questions = results.questions;
        }
      });
    };

/*code to upload the file via ngf*/
/*     $scope.upload = function (file, video_name) {
        Upload.upload({
            url: uploadVideoUrl,
            data: {file: file, 'video': video_name}
        }).then(function (resp) {
//            console.log('Success ' + resp.config.data.file.name + 'uploaded. Response: ' + resp.data);
        }, function (resp) {
  //          console.log('Error status: ' + resp.status);
        }, function (evt) {
            var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
            $scope.module.video_progress = progressPercentage;
    //        console.log('progress: ' + progressPercentage + '% ' + evt.config.data.file.name);
        });
    };
*/


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

    // load term list for select
    $scope.loadTermList = function(id) {
      Data.get('getTermList?id='+ id).then(function(results) {
         console.log(results);
        if(results.status == "success") {
          $scope.termLabel = results.term.sch_term_label;
          console.log($scope.termLabel);
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
          $scope.loadLessons($scope.course.course_id);
        }
      }, function () {
        console.log('Modal dismissed at: ' + new Date());
      });
    };

        $scope.openQuestionModal = function (module = null) {
      var modalInstance = $modal.open({
        templateUrl: 'myQuestionModalContent.html',
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
          $scope.loadLessons($scope.course.course_id);
        }
      }, function () {
        console.log('Modal dismissed at: ' + new Date());
      });
    };

    // Delete Module
    $scope.deleteModule = function(id) {
      if (confirm("Are you sure you want to delete this LESSON?")) {
        FTAFunctions.deleteModule(id).then(function(result) {
          if(result.status = "success") {
            $scope.loadLessons($scope.course.course_id);
            $rootScope.toasterPop('success','Successful!',result.message);
          }
        });
      }
    };

        // Delete Question Module
    $scope.deleteQuestionModule = function(id) {
      if (confirm("Are you sure you want to delete this QUESTION?")) {
        FTAFunctions.deleteQuestionModule(id).then(function(result) {
          if(result.status = "success") {
            $scope.loadLessons($scope.course.course_id);
            $rootScope.toasterPop('success','Successful!',result.message);
          }
        });
      }
    };

/*function to generate course title*/
    $scope.generateCourseName = function( stage, subject, term){
      return stage + '-' +subject + '-' + $scope.termLabel + '-' + term;
    }

    $scope.getCourseName = function(name) {
          console.log(name);
        $scope.course.sub_title = name.selected_subject.sb_title;
        $scope.subTitle = $scope.course.sub_title;
        $scope.course.course_subject_id = name.selected_subject.sb_id;
        $scope.course.course_title = $scope.generateCourseName($scope.courseTitle,$scope.subTitle,$scope.course.course_term);
    };

    // class plus subject plus term name
    $scope.getCourseTerm = function(name) {
        $scope.course.course_title = $scope.generateCourseName($scope.courseTitle,$scope.subTitle, name);
        console.log($scope.course.course_title);
    };

    // select class when ui-select item is picked
    $scope.selectClass = function(item, model) {
      $scope.course.course_class_id = item.class_id;
      $scope.courseTitle = model.class_name;
      $scope.loadTermList($scope.course.course_class_id);
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
          $scope.courseLabel = results.label;
          $scope.course_subject_id = results.course.course_subject_id;
          $scope.course_class_id = results.course.course_class_id;
          $scope.course.course_price = parseFloat ($scope.course.course_price);
          $scope.course.course_term = parseInt ($scope.course.course_term);
          console.log($scope.course_class_id);
          $scope.loadLessons($scope.course.class_id);
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
       console.log(course);
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

    // disApproveCourse
    $scope.disApproveCourse = function(id) {
        FTAFunctions.disApproveCourse(id).then(function(results) {
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
    };

        // approve course
    $scope.approveCourse = function(id) {
        FTAFunctions.approveCourse(id).then(function(results) {
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
    };



    //$rootScope.toasterPop('success','Testing','Toaster.js Works in FORM.JS !!!');

  }])
 ;