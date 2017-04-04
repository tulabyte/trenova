'use strict';

/* Controllers */

app
// Flot Chart controller 
.controller('DashboardController', ['$scope', 'FTAFunctions', '$moment', function($scope, FTAFunctions, $moment) {

  // initialize stuff
  $scope.box_stats = {};
  $scope.latest_subs = [];
  $scope.top_users = [];
  $scope.top_courses = [];
  $scope.new_payments = [];
  $scope.new_users = [];
  $scope.not_list = [];

  // console.log('moment.js today - ' + $moment().format('MMM D'));

  // get box stats
  FTAFunctions.getDashStats().then(function(results) {
    console.log(results);
    if(results.status == "success") {
      $scope.box_stats = results.stats;
    }
  });

  // get latest subs
  FTAFunctions.getLatestSubs().then(function(results) {
    console.log(results);
    if(results.status == "success") {
      $scope.latest_subs = results.latest_subs;
    }
  });

  // get top users
  FTAFunctions.getTopUsers().then(function(results) {
    console.log(results);
    if(results.status == "success") {
      $scope.top_users = results.top_users;
    }
  });

  // get top courses
  FTAFunctions.getTopCourses().then(function(results) {
    console.log(results);
    if(results.status == "success") {
      $scope.top_courses = results.top_courses;
    }
  });

  // get new users
  FTAFunctions.getNewUsers().then(function(results) {
    console.log(results);
    if(results.status == "success") {
      $scope.new_users = results.new_users;
    }
  });

  // get new payments
  FTAFunctions.getNewPayments().then(function(results) {
    console.log(results);
    if(results.status == "success") {
      $scope.new_payments = results.new_payments;
    }
  });

  // line chart - using angular-chart.js

  // Generate dates for last 7 days
  $scope.period = {};
  $scope.date_labels = [];
  var today_ts = $moment();
  $scope.period.end_date = $moment().subtract(1, 'days').format('YYYY-MM-DD'); // yesterday - enddate of last 7 days
  var date_ts = $moment().subtract(7, 'days'); // start from last week
  $scope.period.start_date = date_ts.format('YYYY-MM-DD'); //last week - startdate of last 7 days
  console.log($scope.period);

  do {
      $scope.date_labels.push(date_ts.format('ddd'));

      date_ts.add(1, 'days');
  } while(date_ts <= today_ts);

  // Initialize series titles
  $scope.user_series = "New Users";
  $scope.sub_series = "Subscriptions";

  /*// initialize sample data for user & sub trends
  $scope.user_trends = [6, 12, 7, 9, 6, 11, 6];
  $scope.sub_trends = [4, 7, 4, 3, 3, 6, 3];*/

  // get real data from API
  FTAFunctions.getDashTrends($scope.period).then(function(results) {
    console.log(results);
    if(results.status == "success") {
      $scope.user_trends = results.user_trends;
      $scope.sub_trends = results.sub_trends;
      // put data in chart
      $scope.line_data = [$scope.user_trends, $scope.sub_trends];
      $scope.line_series = [$scope.user_series, $scope.sub_series];
    }
  });

  
}]);