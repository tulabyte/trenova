'use strict';

/* Controllers */

angular.module('app')
  .controller('AppCtrl', ['$scope', '$translate', '$localStorage', '$window', 'Data', '$rootScope', 'toaster', 
    function(              $scope,   $translate,   $localStorage,   $window, Data, $rootScope , toaster) {
      // add 'ie' classes to html
      var isIE = !!navigator.userAgent.match(/MSIE/i);
      isIE && angular.element($window.document.body).addClass('ie');
      isSmartDevice( $window ) && angular.element($window.document.body).addClass('smart');

      // config
      $scope.app = {
        name: 'Learnnova',
        version: '0.3.0',
        // for chart colors
        color: {
          primary: '#7266ba',
          info:    '#23b7e5',
          success: '#27c24c',
          warning: '#fad733',
          danger:  '#f05050',
          light:   '#e8eff0',
          dark:    '#3a3f51',
          black:   '#1c2b36'
        },
        settings: {
          themeID: 1,
          navbarHeaderColor: 'bg-black',
          navbarCollapseColor: 'bg-white-only',
          asideColor: 'bg-black',
          headerFixed: true,
          asideFixed: false,
          asideFolded: false,
          asideDock: false,
          container: false
        }
      }

      /*console.log ('Testing Data Service: ');
      //console.log('spinnerActive Before http - ' + $scope.spinnerActive);
      Data.get('session').then(function(results) {
        //console.log('spinnerActive after http - ' + $scope.spinnerActive);
        //console.log(results);
        console.log('Data call successful');
      }, function(error) {
        console.log('Data call FAILED!');
      });*/

      $rootScope.toasterPop('success','Testing','Toaster.js Works!!!');

      // save settings to local storage
      /*if ( angular.isDefined($localStorage.settings) ) {
        $scope.app.settings = $localStorage.settings;
      } else {
        $localStorage.settings = $scope.app.settings;
      }*/
      $scope.$watch('app.settings', function(){
        if( $scope.app.settings.asideDock  &&  $scope.app.settings.asideFixed ){
          // aside dock and fixed must set the header fixed.
          $scope.app.settings.headerFixed = true;
        }
        // save to local storage
        $localStorage.settings = $scope.app.settings;
      }, true);

      // angular translate
      $scope.lang = { isopen: false };
      $scope.langs = {en:'English', de_DE:'German', it_IT:'Italian'};
      $scope.selectLang = $scope.langs[$translate.proposedLanguage()] || "English";
      $scope.setLang = function(langKey, $event) {
        // set the current lang
        $scope.selectLang = $scope.langs[langKey];
        // You can change the language during runtime
        $translate.use(langKey);
        $scope.lang.isopen = !$scope.lang.isopen;
      };

      function isSmartDevice( $window )
      {
          // Adapted from http://www.detectmobilebrowsers.com
          var ua = $window['navigator']['userAgent'] || $window['navigator']['vendor'] || $window['opera'];
          // Checks for iOs, Android, Blackberry, Opera Mini, and Windows mobile devices
          return (/iPhone|iPod|iPad|Silk|Android|BlackBerry|Opera Mini|IEMobile/).test(ua);
      }

  }]) 

  .controller('HeaderCtrl', ['$scope', 'Data', '$rootScope', 'FTAFunctions', '$state', '$stateParams' , 'toaster', '$moment',
    function($scope, Data, $rootScope, FTAFunctions, $state, $stateParams , toaster, $moment) {
      $scope.payments = [];
      //feedback message and bank awaiting list
      $scope.FeedMessageList = function(index, id){
        var i = parseInt(index);
        var j = parseInt(id);
        console.log(i , j);
        $scope.not_feed.splice(i, 1);
        $state.go('app.feedback-details',{id : id} );
      };

      Data.get('getLatestNotifications').then(function(results) {
        console.log(results);
        if(results.status == "success") {
          $scope.not_list = results.not_list;
          $scope.not_feed = results.not_feed;
          $scope.not_count = parseInt(results.not_count);
        }
      });
    }]);