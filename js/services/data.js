app.factory("Data", ['$http', '$rootScope', 'bsLoadingOverlayService', 
    function ($http, $rootScope, bsLoadingOverlayService) { // This service connects to our REST API

         //var serviceBase = 'http://tulabyte.net/trenova/api/default/';
        // var serviceBase = 'api/default/index.php/';
         var serviceBase = 'http://localhost/lenova/trenova/api/default/index.php/';
        // var serviceBase = 'http://localhost:3000/api/default/index.php/';

        var obj = {};

        obj.get = function (q) {
            //$rootScope.showPreloader();
            return $http.get(serviceBase + q).then(function (results) {
                return results.data;
            },
            function(error) {
                console.log(error);
            });
        };
        obj.post = function (q, object) {
            return $http.post(serviceBase + q, object).then(function (results) {
                return results.data;
            },
            function(error) {
                console.log(error);
            });
        };
        obj.put = function (q, object) {
            return $http.put(serviceBase + q, object).then(function (results) {
                return results.data;
            },
            function(error) {
                console.log(error);
            });
        };
        obj.delete = function (q) {
            return $http.delete(serviceBase + q).then(function (results) {
                return results.data;
            },
            function(error) {
                console.log(error);
            });
        };

        return obj;
}]);

app.factory('allHttpInterceptor', function(bsLoadingOverlayHttpInterceptorFactoryFactory) {
    return bsLoadingOverlayHttpInterceptorFactoryFactory();
})