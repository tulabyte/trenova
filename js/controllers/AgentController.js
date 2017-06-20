'use strict';

app.controller('AgentController', ['$scope', '$rootScope', 'FTAFunctions', '$state', '$stateParams', 'Data', function($scope, $rootScope, FTAFunctions, $state, $stateParams, Data) {
    
    //initialize stuff
    $scope.agent = {};
    $scope.agent_usg = {};
    $scope.agent_pur = {};
    $scope.agent.ad_type = "AGENT";
    $scope.agents = [];

    // Edit Agent
    $scope.editAgent = function(agent) {
      //check if we are on the edit page
      if($stateParams.id) {
        //edit the user
        console.log('editing admin...');
        Data.post('editAdmin', {
            admin: agent
        }).then(function (results) {
            console.log(results);
            if(results.status == "success") {
              //agent edited. Show message
              $rootScope.toasterPop('success','Action Successful!',results.message);
            } else {
              //problemo. show error
              $rootScope.toasterPop('error','Oops!',results.message);
            }
        });
      } else {
        //create the user
        // console.log('creating new user...');
        Data.post('createAdmin', {
            admin: agent
        }).then(function (results) {
            console.log(results);
            if(results.status == "success") {
              //agent created. Show message and go to agent list
              $state.go('app.agent-list');
              $rootScope.toasterPop('success','Action Successful!',results.message);
            } else {
              //problemo. show error
              $rootScope.toasterPop('error','Oops!',results.message);
            }
        });
      }
    };

      //Agent details
    if($state.current.name == 'app.agent-details') {
       //get the agent details
       console.log('agent details');
       FTAFunctions.getAgentDetails($stateParams.id).then(function(results) {
        console.log(results);
        if(results.status == "success") {
          $scope.agent = results.agent;
          $scope.agent_pur = results.agent_pur;
          $scope.agent_usg = results.agent_usg;
          $scope.agent_cr_pur = results.agent_cr_pur;
          $scope.agent_cr_usg = results.agent_cr_usg;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }

          //Agent's dash
    if($state.current.name == 'app.agent-dashboard') {
       //get the agent details
       console.log('agent details');
       FTAFunctions.getAgentDash().then(function(results) {
        console.log(results);
        if(results.status == "success") {
          $scope.agent = results.agent;
          $scope.agent_pur = results.agent_pur;
          $scope.agent_usg = results.agent_usg;
          $scope.agent_cr = results.agent_cr;
           // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }

          //Agent's purchase used
    if($state.current.name == 'app.agent-used-list') {
       //get the agent details
       console.log('agent details');
       FTAFunctions.getAgentPurchase().then(function(results) {
        console.log(results);
        if(results.status == "success") {
          $scope.agent_pur = results.agent_pur;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }

          //Agent's purchase unused
    if($state.current.name == 'app.agent-unused-list') {
       //get the agent details
       console.log('agent details');
       FTAFunctions.getAgentUnusedPurchase().then(function(results) {
        console.log(results);
        if(results.status == "success") {
          $scope.agent_usg = results.agent_usg;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }


              //Agent's order
    if($state.current.name == 'app.agent-order-list') {
       //get the agent details
       console.log('agent details');
       FTAFunctions.getAgentOrder().then(function(results) {
        console.log(results);
        if(results.status == "success") {
          $scope.agent_pur = results.agent_pur;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }

              //Agent's course List
    if($state.current.name == 'app.agent-course-list') {
       //get the agent details
       console.log('agent details');
       FTAFunctions.getAgentCourse().then(function(results) {
        console.log(results);
        if(results.status == "success") {
          $scope.agent_pur = results.agent_pur;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }

        //buy-course details
    if($state.current.name == 'app.buy-course') {
      //get the selected user
      FTAFunctions.getBuyDetails($stateParams.id).then(function(results) {
         console.log(results);
        if(results.status == "success") {
          $scope.agent_pur = results.agent_pur;
          $scope.agent_usg = results.agent_usg;
          $scope.agent_cr_pur = results.agent_cr_pur;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    };

    // delete agent
    $scope.deleteAgent = function(id) {
      if (confirm("Are you sure you want to delete this agent? THIS WILL REMOVE ALL CONTENT/INFO CONNECTED TO THIS AGENT!!!")) {
        FTAFunctions.deleteAdmin(id).then(function(results) {
          if(results.status == 'success') {
            $state.go('app.agent-list', {reload: true});
            $scope.loadAgentList();
            $rootScope.toasterPop('success','Action Successful!',results.message);
          } else {
            $rootScope.toasterPop('error','Oops!',results.message);
          }
        });
      } else {
        return false;
      }
    };

    $scope.loadAgentList = function() {
      //get the agents
      FTAFunctions.getAdminList('AGENT').then(function(results) {
        // console.log(results);
        if(results.status == "success") {
          $scope.agents = results.admins;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    };

    $scope.loadAgentWaitingList = function() {
      //get the agents
      FTAFunctions.getAdminList('AGENT','PENDING').then(function(results) {
        // console.log(results);
        if(results.status == "success") {
          $scope.agents = results.admins;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    };

    $scope.getDiscount = function(qty) {
      if(qty < $scope.agent_usg[0].df_min) return 0;
      var dlength = $scope.agent_usg.length;
      for(var i=0; i<(dlength-1); i++) {
        if (qty >= $scope.agent_usg[i].df_min && qty < $scope.agent_usg[i+1].df_min) {
          return $scope.agent_usg[i].df_discounts; 
        }
      }
      return $scope.agent_usg[dlength-1].df_discounts;
    };


    $scope.calcDiscountedTotal = function() {
        console.log('calc total');
        var discount = $scope.getDiscount($scope.agent.order_quantity);
        $scope.agent.order_total = $scope.agent_cr_pur.course_price *  $scope.agent.order_quantity * (100 - discount) / 100;
    };

//agent buy course
    $scope.buyCourse = function(agent){
      $scope.agent.course_id = $scope.agent_cr_pur.course_id;
      $scope.agent.discount = $scope.getDiscount(agent.order_quantity)
         Data.post('buyAgentCourse', {
            agent: agent
        }).then(function (results) {
            console.log(results);
            if(results.status == "success") {
               $scope.agent_gen = results.agent_gen;
               $rootScope.toasterPop('success','Action Successful!',results.message);
            } else {
              //problemo. show error
              $rootScope.toasterPop('error','Oops!',results.message);
            }
        });
    };

    // verify agent
    $scope.verifyAgent = function(agent, index) {
      if(confirm("Are you sure you want to VERIFY this agent?")) {
        FTAFunctions.verifyAdmin(agent.ad_id).then(function(results) {
          console.log(results);
          if(results.status == "success") {
            // verified, reload the list and show success message
            $rootScope.toasterPop('success','Action Successful!',results.message);
            $state.go('app.agent-list', {reload: true});
            $scope.loadAgentList();
          } else {
            // not verified, show error
            $rootScope.toasterPop('error','Oops!',results.message);
          }
        });
      }
    }

    //agent-list state
    if($state.current.name == 'app.agent-list') {
      $scope.loadAgentList();
    }

    //agent-list-pending state
    if($state.current.name == 'app.agent-list-pending') {
      $scope.loadAgentWaitingList();
    }

    //agent-edit state
    if($state.current.name == 'app.agent-edit' && $stateParams.id != '') {
      $state.current.data.pageTitle = "Edit Agent"
      //get the selected agent
      FTAFunctions.getAdmin($stateParams.id).then(function(results) {
        // console.log(results);
        if(results.status == "success") {
          $scope.agent = results.admin;
          // $rootScope.toasterPop('success','Action Successful!',results.message);
        } else {
          $rootScope.toasterPop('error','Oops!',results.message);
        }
      });
    }

    

  }])
 ;