'use strict';

app.controller('QuizController', ['$scope', '$rootScope', '$modal', 'FTAFunctions', '$state', '$stateParams', '$http', 'Data', function($scope, $rootScope, $modal, FTAFunctions, $state, $stateParams, $http, Data/*, FileUploader*/) {
    
    //initialize stuff
    $scope.quizs = {};
    
    if($state.current.name == 'app.quiz-list') {
      Data.get('getQuizList').then(function(results) {
         console.log(results);
        if(results.status == "success") {
          $scope.quizs = results.quizs;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    };

    if($state.current.name == 'app.quiz-details') {
      Data.get('getQuizDetails?id='+$stateParams.id).then(function(results) {
         console.log(results);
        if(results.status == "success") {
          $scope.quizs = results.quizs;
          $scope.course = results.course;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    };




  }])
 ;