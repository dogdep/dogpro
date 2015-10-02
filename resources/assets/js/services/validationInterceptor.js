(function() {
    angular
        .module('dp')
        .factory('ValidationInterceptor', interceptor);

    function interceptor($q, toaster) {
        return {
            response: response,
            responseError: error
        };

        function response(response) {
            return response || $q.when(response);
        }

        function error(rejection) {

            if (rejection.status === 422) {
                if (typeof rejection.data["error"] == "string") {
                    toaster.pop('warning', "Validation Error", rejection.data["error"]);
                    return $q.reject(rejection);
                }

                for (var key in rejection.data) {
                    if (rejection.data.hasOwnProperty(key)) {


                        for (var i=0; i<rejection.data[key].length; i++) {
                            toaster.pop('warning', "Validation Error", rejection.data[key][i]);
                            return $q.reject(rejection);
                        }
                    }
                }
            }

            return $q.reject(rejection);
        }
    }

})();
