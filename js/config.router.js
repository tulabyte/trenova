'use strict';

/**
 * Config for the router
 */
angular.module('app')
  .run(
    [          '$rootScope', '$state', '$stateParams', 'bsLoadingOverlayService', 'Data', 'toaster', 'DTOptionsBuilder', 
      function ($rootScope,   $state,   $stateParams, bsLoadingOverlayService, Data, toaster, DTOptionsBuilder) {
          
          $rootScope.authenticated = false;

          //session access control
          $rootScope.$on("$stateChangeStart", function (event, next, current) {
              
              if (!$rootScope.authenticated && $state.current.name != 'access.signin' &&  $state.current.name != 'access.forgotpwd' &&  $state.current.name != 'access.signup' &&  $state.current.name != 'access.confirm') {
                Data.get('session').then(function (results) {
                    console.log(results);
                    if (results.trenova_user) {
                        $rootScope.authenticated = true;
                        $rootScope.trenova_user = results.trenova_user;
                    } else {
                        //var nextUrl = next.$$route.originalPath;
                        //$location.path("/login");
                        $state.go('access.signin');
                    }
                });
              }
          });

          // pending payments
          

          //logout
          $rootScope.logout = function() {
            Data.get('logout').then(function (results) {
              if(results.status='success') {
                $rootScope.trenova_user = {};
                $state.go('access.signin');
                $rootScope.toasterPop('success','Success','Logout Successful!');
              };
            });
          };

          //toaster notifications/alerts
          $rootScope.toasterPop = function(type, title, text) {
              toaster.pop(type, title, text);    
          };

          // convert date string to JavaScript date
          $rootScope.makeDate = function(string) {
            return new Date(string);
          };          
          $rootScope.dtOptions = DTOptionsBuilder.newOptions().withOption("order",[]);

          $rootScope.$state = $state;
          $rootScope.$stateParams = $stateParams;

          bsLoadingOverlayService.setGlobalConfig({
            templateUrl: 'tpl/loading-overlay-template.html'
          });        
      }
    ]
  )
  .config(
    [          '$stateProvider', '$urlRouterProvider',
      function ($stateProvider,   $urlRouterProvider) {
          
          $urlRouterProvider
              .otherwise('/app/dashboard');
          $stateProvider
              .state('app', {
                  abstract: true,
                  url: '/app',
                  data: {pageTitle: 'Welcome'},
                  templateUrl: 'tpl/app.html'
              })
              .state('app.dashboard', {
                  url: '/dashboard',
                  data: {pageTitle: 'Dashboard'},
                  templateUrl: 'tpl/app_dashboard.html',
                  resolve: {
                    deps: ['$ocLazyLoad',
                      function( $ocLazyLoad ){
                        return $ocLazyLoad.load(['js/controllers/DashboardController.js']);
                    }]
                  }
              })

              .state('app.agent-dashboard', {
                  url: '/agent-dashboard',
                  data: {pageTitle: 'Agent Dashboard'},
                  templateUrl: 'tpl/app_agent-dashboard.html',
                  resolve: {
                    deps: ['$ocLazyLoad',
                      function( $ocLazyLoad ){
                        return $ocLazyLoad.load(['js/controllers/AgentController.js']);
                    }]
                  }
              })

              .state('app.agent-used-list', {
                  url: '/agent-used-list',
                  data: {pageTitle: 'Agent Dashboard'},
                  templateUrl: 'tpl/agent-used-list.html',
                  resolve: {
                    deps: ['$ocLazyLoad',
                      function( $ocLazyLoad ){
                        return $ocLazyLoad.load(['js/controllers/AgentController.js']);
                    }]
                  }
              })

              .state('app.agent-unused-list', {
                  url: '/agent-unused-list',
                  data: {pageTitle: 'Agent Dashboard'},
                  templateUrl: 'tpl/agent-unused-list.html',
                  resolve: {
                    deps: ['$ocLazyLoad',
                      function( $ocLazyLoad ){
                        return $ocLazyLoad.load(['js/controllers/AgentController.js']);
                    }]
                  }
              })

              .state('app.agent-purchase-list', {
                  url: '/agent-purchase-list/:id',
                  data: {pageTitle: 'Purchase List'},
                  templateUrl: 'tpl/agent-purchase-list.html',
                  resolve: {
                    deps: ['$ocLazyLoad',
                      function( $ocLazyLoad ){
                        return $ocLazyLoad.load(['js/controllers/AgentController.js']);
                    }]
                  }
              })

              .state('app.agent-course-list', {
                  url: '/agent-course-list',
                  data: {pageTitle: 'Course List'},
                  templateUrl: 'tpl/agent-course-list.html',
                  resolve: {
                    deps: ['$ocLazyLoad',
                      function( $ocLazyLoad ){
                        return $ocLazyLoad.load(['js/controllers/AgentController.js']);
                    }]
                  }
              })

              .state('app.agent-order-list', {
                  url: '/agent-order-list',
                  data: {pageTitle: 'Order List'},
                  templateUrl: 'tpl/agent-order-list.html',
                  resolve: {
                    deps: ['$ocLazyLoad',
                      function( $ocLazyLoad ){
                        return $ocLazyLoad.load(['js/controllers/AgentController.js']);
                    }]
                  }
              })

              //buy course details
              .state('app.buy-course', {
                  url: '/buy-course/:id',
                  templateUrl: 'tpl/agent-buy-course.html',
                  data: {pageTitle: 'Buy Course'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/AgentController.js');
                      }]
                  }
              })

              .state('app.quiz-list', {
                  url: '/quiz-list/:id',
                  templateUrl: 'tpl/quiz-list.html',
                  data: {pageTitle: 'Quiz List'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/QuizController.js');
                      }]
                  }
              })

              .state('app.quiz-details', {
                  url: '/quiz-details/:id',
                  templateUrl: 'tpl/quiz-details.html',
                  data: {pageTitle: 'Quiz Details'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/QuizController.js');
                      }]
                  }
              })

              //add imports
              .state('app.question-import', {
                  url: '/question-import/:id',
                  templateUrl: 'tpl/question-import.html',
                  data: {pageTitle: 'Import Questions'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/QuestionController.js');
                      }]
                  }
              })

              //add question
              .state('app.question-edit', {
                  url: '/question-edit/:id',
                  templateUrl: 'tpl/question-edit.html',
                  data: {pageTitle: 'Create Question'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/QuestionController.js');
                      }]
                  }
              })

              //add question
              .state('app.question-re-edit', {
                  url: '/question-re-edit/:id/',
                  templateUrl: 'tpl/question-re-edit.html',
                  data: {pageTitle: 'Edit Question'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/QuestionController.js');
                      }]
                  }
              })

              //question list
              .state('app.question-list', {
                  url: '/question-list/:id',
                  templateUrl: 'tpl/question-list.html',
                  data: {pageTitle: 'Question List'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/QuestionController.js');
                      }]
                  }
              })

              //add bundle
              .state('app.bundle-edit', {
                  url: '/bundle-edit/:id',
                  templateUrl: 'tpl/bundle-edit.html',
                  data: {pageTitle: 'Create Bundle'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/BundleController.js');
                      }]
                  }
              })

              //add bundle
              .state('app.bundle-details', {
                  url: '/bundle-details/:id',
                  templateUrl: 'tpl/bundle-details.html',
                  data: {pageTitle: 'Bundle Details'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/BundleController.js');
                      }]
                  }
              })


              //bundle list
              .state('app.bundle-list', {
                  url: '/bundle-list/:id',
                  templateUrl: 'tpl/bundle-list.html',
                  data: {pageTitle: 'Bundle List'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/BundleController.js');
                      }]
                  }
              })

              //forum list
              .state('app.forum-list', {
                  url: '/forum-list/:id',
                  templateUrl: 'tpl/forum-list.html',
                  data: {pageTitle: 'Forum List'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/ForumController.js');
                      }]
                  }
              })

              //forum details
              .state('app.forum-details', {
                  url: '/forum-details/:id',
                  templateUrl: 'tpl/forum-details.html',
                  data: {pageTitle: 'Forum Details'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/ForumController.js');
                      }]
                  }
              })

                .state('app.feedback-list', {
                  url: '/feedback-list',
                  data: {pageTitle: 'Feedback List'},
                  templateUrl: 'tpl/feedback-list.html',
                  resolve: {
                    deps: ['$ocLazyLoad',
                      function( $ocLazyLoad ){
                        return $ocLazyLoad.load(['js/controllers/UserController.js']);
                    }]
                  }
              })

              //feedback details
              .state('app.feedback-details', {
                  url: '/feedback-details/:id',
                  templateUrl: 'tpl/feedback-details.html',
                  data: {pageTitle: 'Feedback Details'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/UserController.js');
                      }]
                  }
              })

              //broadcast list
              .state('app.broadcast-list', {
                  url: '/app.broadcast-list',
                  templateUrl: 'tpl/bc-list.html',
                  data: {pageTitle: 'Broadcast-list'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/UserController.js');
                      }]
                  }
              })

                //broadcast message
              .state('app.broadcast-message', {
                  url: '/broadcast-message/:id',
                  templateUrl: 'tpl/bc-message.html',
                  data: {pageTitle: 'Broadcast Details'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/UserController.js');
                      }]
                  }
              })

              //user message
              .state('app.user-message', {
                  url: '/user-message/:id',
                  templateUrl: 'tpl/user-message.html',
                  data: {pageTitle: 'Message Details'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/UserController.js');
                      }]
                  }
              })

              .state('app.re-dashboard', {
                  url: '/re-dashboard',
                  data: {pageTitle: 'Reseller Dashboard'},
                  templateUrl: 'tpl/app_re-dashboard.html',
                  resolve: {
                    deps: ['$ocLazyLoad',
                      function( $ocLazyLoad ){
                        return $ocLazyLoad.load(['js/controllers/ResellerController.js']);
                    }]
                  }
              })

              .state('app.re-dash-list', {
                  url: '/re-dash-list',
                  data: {pageTitle: 'Reseller Unpaid List'},
                  templateUrl: 'tpl/re-dash-list.html',
                  resolve: {
                    deps: ['$ocLazyLoad',
                      function( $ocLazyLoad ){
                        return $ocLazyLoad.load(['js/controllers/ResellerController.js']);
                    }]
                  }
              })

              .state('app.reseller-list-commission', {
                  url: '/reseller-list-commission',
                  data: {pageTitle: 'Reseller Commission List'},
                  templateUrl: 'tpl/reseller-list-commission.html',
                  resolve: {
                    deps: ['$ocLazyLoad',
                      function( $ocLazyLoad ){
                        return $ocLazyLoad.load(['js/controllers/ResellerController.js']);
                    }]
                  }
              })

              .state('app.re-dash-paid', {
                  url: '/re-dash-paid',
                  data: {pageTitle: 'Reseller Paid List'},
                  templateUrl: 'tpl/re-dash-paid.html',
                  resolve: {
                    deps: ['$ocLazyLoad',
                      function( $ocLazyLoad ){
                        return $ocLazyLoad.load(['js/controllers/ResellerController.js']);
                    }]
                  }
              })

              /*ADMINS*/

              // admin-edit
              .state('app.admin-edit', {
                  url: '/admin-edit/:id',
                  templateUrl: 'tpl/admin-edit.html',
                  data: {pageTitle: 'New Admin'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/AdminController.js');
                      }]
                  }
              })



              /*AGENTS*/

              // agent-edit
              .state('app.agent-edit', {
                  url: '/agent-edit/:id',
                  templateUrl: 'tpl/agent-edit.html',
                  data: {pageTitle: 'New Agent'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/AgentController.js');
                      }]
                  }
              })


                //agent details
                .state('app.agent-details', {
                  url: '/agent-details/:id',
                  templateUrl: 'tpl/agent-details.html',
                  data: {pageTitle: 'Agent Details'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/AgentController.js');
                      }]
                  }
              })


              // agent list
              .state('app.agent-list', {
                  url: '/agent-list',
                  templateUrl: 'tpl/agent-list.html',
                  data: {pageTitle: 'Agent List'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/AgentController.js');
                      }]
                  }
              })

              // agent list - pending
              .state('app.agent-list-pending', {
                  url: '/agent-list-pending',
                  templateUrl: 'tpl/agent-list-pending.html',
                  data: {pageTitle: 'Agents Awaiting Verification'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/AgentController.js');
                      }]
                  }
              })

              /* RESELLERS */

              // reseller-edit
              .state('app.reseller-edit', {
                  url: '/reseller-edit/:id',
                  templateUrl: 'tpl/reseller-edit.html',
                  data: {pageTitle: 'New Reseller'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/ResellerController.js');
                      }]
                  }
              })

              // reseller list
              .state('app.reseller-list', {
                  url: '/reseller-list',
                  templateUrl: 'tpl/reseller-list.html',
                  data: {pageTitle: 'Reseller List'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/ResellerController.js');
                      }]
                  }
              })


              //reseller details
              .state('app.reseller-details', {
                  url: '/reseller-details/:id',
                  templateUrl: 'tpl/reseller-details.html',
                  data: {pageTitle: 'Reseller Details'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/ResellerController.js');
                      }]
                  }
              })

              // reseller list - pending
              .state('app.reseller-list-pending', {
                  url: '/reseller-list-pending',
                  templateUrl: 'tpl/reseller-list-pending.html',
                  data: {pageTitle: 'Resellers Awaiting Verification'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/ResellerController.js');
                      }]
                  }
              })

              /* SCHOOLS */

              // edit school
              .state('app.school-edit', {
                  url: '/school-edit/:id',
                  templateUrl: 'tpl/school-edit.html',
                  data: {pageTitle: 'New School'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/SchoolController.js');
                      }]
                  }
              })

              // school list
              .state('app.school-list', {
                  url: '/school-list',
                  templateUrl: 'tpl/school-list.html',
                  data: {pageTitle: 'School List'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/SchoolController.js');
                      }]
                  }
              })

              /* CLASSS */

              // edit class
              .state('app.class-edit', {
                  url: '/class-edit/:id',
                  templateUrl: 'tpl/class-edit.html',
                  data: {pageTitle: 'New Class'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/ClassController.js');
                      }]
                  }
              })

              // class list
              .state('app.class-list', {
                  url: '/class-list',
                  templateUrl: 'tpl/class-list.html',
                  data: {pageTitle: 'Class List'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/ClassController.js');
                      }]
                  }
              })

              /* SUBJECTS */

              // edit subject
              .state('app.subject-edit', {
                  url: '/subject-edit/:id',
                  templateUrl: 'tpl/subject-edit.html',
                  data: {pageTitle: 'New Subject'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/SubjectController.js');
                      }]
                  }
              })

              // subject list
              .state('app.subject-list', {
                  url: '/subject-list',
                  templateUrl: 'tpl/subject-list.html',
                  data: {pageTitle: 'Subject List'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/SubjectController.js');
                      }]
                  }
              })

              /* ------------------- */



              // profile-edit
              .state('app.profile-edit', {
                  url: '/profile-edit',
                  templateUrl: 'tpl/profile-edit.html',
                  data: {pageTitle: 'Edit Profile'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/ProfileController.js');
                      }]
                  }
              })

              // password-change
              .state('app.password-change', {
                  url: '/password-change',
                  templateUrl: 'tpl/password-change.html',
                  data: {pageTitle: 'Change Password'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/ProfileController.js');
                      }]
                  }
              })

              // user-edit
              .state('app.user-edit', {
                  url: '/user-edit/:id',
                  templateUrl: 'tpl/user-edit.html',
                  data: {pageTitle: 'New User'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/UserController.js');
                      }]
                  }
              })


                //user details
                .state('app.user-details', {
                  url: '/user-details/:id',
                  templateUrl: 'tpl/user-details.html',
                  data: {pageTitle: 'User Details'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/UserController.js');
                      }]
                  }
              })



              // edit category
              .state('app.cat-edit', {
                  url: '/cat-edit/:id',
                  templateUrl: 'tpl/cat-edit.html',
                  data: {pageTitle: 'New Category'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/CategoryController.js');
                      }]
                  }
              })

              // edit course
              .state('app.course-edit', {
                  url: '/course-edit/:id',
                  templateUrl: 'tpl/course-edit.html',
                  data: {pageTitle: 'New Course'},
                  resolve: {
                      deps: ['$ocLazyLoad',
                        function( $ocLazyLoad){
                          return $ocLazyLoad.load([
                              'angularFileUpload',
                              'textAngular',
                              'ui.select'
                            ]).then(
                              function(){
                                 return $ocLazyLoad.load('js/controllers/CourseController.js');
                              }
                          );
                      }]
                  }
              })

              // course details
              .state('app.course-details', {
                  url: '/course-details/:id',
                  templateUrl: 'tpl/course-details.html',
                  data: {pageTitle: 'Course Details'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/CourseController.js');
                      }]
                  }
              })

              // admin list
              .state('app.admin-list', {
                  url: '/admin-list',
                  templateUrl: 'tpl/admin-list.html',
                  data: {pageTitle: 'Admin List'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/AdminController.js');
                      }]
                  }
              })

              // user list
              .state('app.user-list', {
                  url: '/user-list',
                  templateUrl: 'tpl/user-list.html',
                  data: {pageTitle: 'User List'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/UserController.js');
                      }]
                  }
              })

              // admin logs
              .state('app.admin-logs', {
                  url: '/admin-logs',
                  templateUrl: 'tpl/admin-logs.html',
                  data: {pageTitle: 'Admin Logs'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/AdminController.js');
                      }]
                  }
              })

              // category list
              .state('app.cat-list', {
                  url: '/cat-list',
                  templateUrl: 'tpl/cat-list.html',
                  data: {pageTitle: 'Course Categories'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/CategoryController.js');
                      }]
                  }
              })

              // course list
              .state('app.course-list', {
                  url: '/course-list',
                  templateUrl: 'tpl/course-list.html',
                  data: {pageTitle: 'Course List'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/CourseController.js');
                      }]
                  }
              })

              // course list
              .state('app.course-pending-list', {
                  url: '/course-pending-list',
                  templateUrl: 'tpl/course-pending-list.html',
                  data: {pageTitle: 'Course Pending List'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/CourseController.js');
                      }]
                  }
              })              

              // subscription list
              .state('app.sub-list', {
                  url: '/sub-list/:type',
                  templateUrl: 'tpl/sub-list.html',
                  data: {pageTitle: 'Subscription List'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/SubscriptionController.js');
                      }]
                  }
              })

              // payment list
              .state('app.payment-list', {
                  url: '/payment-list/:type',
                  templateUrl: 'tpl/payment-list.html',
                  data: {pageTitle: 'Payment List'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/PaymentController.js');
                      }]
                  }
              })

              // bank-waiting list
              .state('app.bank-waiting-list', {
                  url: '/bank-waiting-list',
                  templateUrl: 'tpl/bank-waiting-list.html',
                  data: {pageTitle: 'Bank Waiting List'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/PaymentController.js');
                      }]
                  }
              })

                   // order list
              .state('app.order-list', {
                  url: '/order-list',
                  templateUrl: 'tpl/order-list.html',
                  data: {pageTitle: 'Order List'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/OrderController.js');
                      }]
                  }
              })

              //order details
              .state('app.order-details', {
                  url: '/order-details/:id',
                  templateUrl: 'tpl/order-details.html',
                  data: {pageTitle: 'Order Details'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad){
                          return uiLoad.load('js/controllers/OrderController.js');
                      }]
                  }
              })
              
              
              // user auth
              
              .state('access', {
                  url: '/access',
                  template: '<div ui-view class="fade-in-right-big smooth"></div>'
              })
              .state('access.signin', {
                  url: '/signin',
                  templateUrl: 'tpl/page_signin.html',
                  data: {pageTitle: 'Sign In'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad ){
                          return uiLoad.load( ['js/controllers/signin.js'] );
                      }]
                  }
              })
              .state('access.signup', {
                  url: '/signup',
                  templateUrl: 'tpl/page_signup.html',
                  data: {pageTitle: 'Sign Up'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad ){
                          return uiLoad.load( ['js/controllers/signup.js'] );
                      }]
                  }
              })
              .state('access.confirm', {
                  url: '/confirm/:code',
                  templateUrl: 'tpl/page_confirm.html',
                  data: {pageTitle: 'Confirm your Registration'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad ){
                          return uiLoad.load( ['js/controllers/confirm.js'] );
                      }]
                  }
              })
              .state('access.forgotpwd', {
                  url: '/forgotpwd',
                  templateUrl: 'tpl/page_forgotpwd.html',
                  data: {pageTitle: 'Recover Password'},
                  resolve: {
                      deps: ['uiLoad',
                        function( uiLoad ){
                          return uiLoad.load( ['js/controllers/forgotpwd.js'] );
                      }]
                  }
              })
              
      }
    ]
  );