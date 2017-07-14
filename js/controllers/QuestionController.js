'use strict';

app.controller('QuestionController', ['$scope', '$rootScope', 'FTAFunctions', '$state', '$stateParams', '$http', 'Data', function($scope, $rootScope, FTAFunctions, $state, $stateParams, $http, Data/*, FileUploader*/) {
    
    //initialize stuff
    $scope.csv ={
      content:null,
      header:true,
      headerVisible:false,
      separator:',',
      separatorVisible:false,
      result:null,
      encoding:'ISO-8859-1',
      encodingVisible:false,
    };
    $scope.createImportCsv = function(content, id){
//      console.log(id);
      console.log(content);
      Data.post('createImportCsvQuestions', 
        { question: content,
          course: id
        }
      ).then(function(results) {
        console.log(results);
        if(results.status = "success") {
          $rootScope.toasterPop('success','Action Successful!',results.message);
          $state.go('app.question-edit',{id:$stateParams.id , reload: true});
        } else {
          //error
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      }, function(error) {
        console.log(error);
      });
    }
    $scope.question = {};
    
        if($state.current.name == 'app.question-edit') {
      Data.get('getQuestionEditDetail?id='+$stateParams.id).then(function(results) {
         console.log(results);
        if(results.status == "success") {
          $scope.questions = results.questions;
          $scope.course = results.course;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          //$rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }

          if($state.current.name == 'app.question-list') {
      Data.get('getQuestionLists?id='+$stateParams.id).then(function(results) {
         console.log(results);
        if(results.status == "success") {
          $scope.questions = results.question;
          $scope.course = results.course;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          //$rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }

      if($state.current.name == 'app.question-re-edit') {
          Data.get('getQuestionEditList?id='+$stateParams.id).then(function(results) {
           console.log(results);
        if(results.status == "success") {
            for (var i = 0; i < results.question_options.length; i++) {
                if (results.question_options[i].qo_is_correct == 1) {
                       $scope.question.option = results.question_options[i].qo_option;
                    }
                }
              $scope.question.q_question = results.question.q_question;
              $scope.question.q_course_id = results.question.q_course_id;
              $scope.question.q_id = results.question.q_id;
              $scope.question_option = results.question_options;
              $scope.question.optiona = $scope.question_option[0].qo_option;
              $scope.question.optiona_id = $scope.question_option[0].qo_id;
              $scope.question.optionb = $scope.question_option[1].qo_option;
              $scope.question.optionb_id = $scope.question_option[1].qo_id;
              $scope.question.optionc = $scope.question_option[2].qo_option;
              $scope.question.optionc_id = $scope.question_option[2].qo_id;
              $scope.question.optiond = $scope.question_option[3].qo_option;
              $scope.question.optiond_id = $scope.question_option[3].qo_id;
              // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          //$rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }

$scope.backToList = function(){
  $state.go('app.question-list',{id:$stateParams.id});
}

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
          $state.go('app.question-edit',{id:$stateParams.id , reload: true});
        } else {
          //error
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      }, function(error) {
        console.log(error);
      });
    }
  };

$scope.editQuestionAndExit = function(question){
    question.course_id = $scope.course.course_id;
        if(question.q_id) {
      //edit question
      Data.post('editQuestionModule', 
        { question: question }
      ).then(function(results) {
        if(results.status = "success") {
          $rootScope.toasterPop('success','Action Successful!',results.message);
          $state.go('app.question-list',{id:$stateParams.id});
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
          $state.go('app.question-list',{id:$stateParams.id});
        } else {
          //error
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      }, function(error) {
        console.log(error);
      });
    }

}

        // Delete Question
    $scope.deleteQuestion = function(id) {
      if (confirm("Are you sure you want to delete this QUESTION?")) {
        FTAFunctions.deleteQuestionModule(id).then(function(result) {
          if(result.status = "success") {
            $scope.loadLessons($scope.course.course_id);
            $rootScope.toasterPop('success','Successful!',result.message);
          }
        });
      }
    };

$scope.editQuestionTitle = function(question){
  console.log(question);
      Data.post('editQuestionTitle', 
        { question: question }
      ).then(function(results) {
        console.log(results);
        if(results.status = "success") {
          $rootScope.toasterPop('success','Action Successful!',results.message);
          //$state.go('app.question-list',{id:$stateParams.id});
        } else {
          //error
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      }, function(error) {
        console.log(error);
      });
}

$scope.editQuestionAndOptions = function(question){
  console.log(question);
      Data.post('editQuestionModule', 
        { question: question }
      ).then(function(results) {
        console.log(results);
        if(results.status = "success") {
          $rootScope.toasterPop('success','Action Successful!',results.message);
          //$state.go('app.question-list',{id:$stateParams.id});
        } else {
          //error
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      }, function(error) {
        console.log(error);
      });
    }
$scope.updateQuizLimit = function(course){
  console.log(course);
      Data.post('updateQuizLimit', 
        { course: course }
      ).then(function(results) {
        console.log(results);
        if(results.status = "success") {
          $rootScope.toasterPop('success','Action Successful!',results.message);
          //$state.go('app.question-list',{id:$stateParams.id});
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