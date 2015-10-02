(function() {
    angular
        .module('dp')
        .factory('AuthInterceptor', interceptor);

    function interceptor($q, $injector, toaster) {
        return {
            response: response,
            responseError: error
        };

        function response(response) {
            return response || $q.when(response);
        }

        function error(rejection) {
            var AuthFactory = $injector.get('AuthFactory');
            var State = $injector.get('$state');

            if (rejection.status === 400 || rejection.status === 401) {
                if (AuthFactory.isLoggedIn()) {
                    toaster.pop('warning', 'Session Expired', 'Please log in.');
                    AuthFactory.logout();
                }
                State.go('anon.login');
            }

            if (rejection.status === 403) {
                toaster.pop('error', "Forbidden", 'You cannot access this resource.');
            }

            if (rejection.status === 404 && rejection.data.error == 'user_not_found') {
                AuthFactory.logout();
                State.go('anon.login');
            }

            return $q.reject(rejection);
        }
    }

})();
