'use strict';

app.controller('CategoryController', ['$scope', '$rootScope', 'FTAFunctions', '$state', '$stateParams', 'Data', function($scope, $rootScope, FTAFunctions, $state, $stateParams, Data) {
    
    //initialize stuff
    $scope.category = {};
    $scope.categories = [];

    //console.log('ID: ' + $stateParams.id);

    //cat-list
    if($state.current.name == 'app.cat-list' || $state.current.name == 'app.cat-edit') {
      //get the category list
      FTAFunctions.getCategoryList().then(function(results) {
        // console.log(results);
        if(results.status == "success") {
          $scope.categories = results.categories;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }

    //cat-edit
    if($state.current.name == 'app.cat-edit' && $stateParams.id != '') {
      $state.current.data.pageTitle = "Edit Category"
      //get the selected category
      FTAFunctions.getCategory($stateParams.id).then(function(results) {
        // console.log(results);
        if(results.status == "success") {
          $scope.category = results.category;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }

    // Edit Category
    $scope.editCategory = function(category) {
      //check if we are on the edit page
      if($stateParams.id) {
        //edit the category
        //console.log('editing category...');
        Data.post('editCategory', {
            category: category
        }).then(function (results) {
            console.log(results);
            if(results.status == "success") {
              //category edited. Show message
              $rootScope.toasterPop('success','Action Successful!',results.message);
            } else {
              //problemo. show error
              $rootScope.toasterPop('error','Oops!',results.message);
            }
        });
      } else {
        //create the category
        // console.log('creating new category...');
        Data.post('createCategory', {
            category: category
        }).then(function (results) {
            console.log(results);
            if(results.status == "success") {
              //category created. Show message and go to category list
              $state.go('app.cat-list');
              $rootScope.toasterPop('success','Action Successful!',results.message);
            } else {
              //problemo. show error
              $rootScope.toasterPop('error','Oops!',results.message);
            }
        });
      }
    };

    // delete category
    $scope.deleteCategory = function(id) {
      if (confirm("Are you sure you want to delete this category?")) {
        FTAFunctions.deleteCategory(id).then(function(results) {
          console.log(results);
          if(results.status == 'success') {
            $state.go('app.cat-list', {reload: true});
            FTAFunctions.getCategoryList().then(function(results) {
              if(results.status == "success") {
                $scope.categories = results.categories;
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