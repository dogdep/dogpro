(function() {
    angular
        .module('dp')
        .factory('authFactory', auth);

    function auth($rootScope, $http, jwtHelper) {
        var localToken = 'auth_token';
        var refreshingToken = null;

        return {
            login: login,
            logout: logout,
            getUser: getUser,
            isLoggedIn: isLoggedIn,
            getToken: getToken,
            tokenExpired: tokenExpired,
            setToken: setToken,
            refreshToken: refreshToken
        };

        function getToken() {
            return localStorage.getItem(localToken);
        }

        function setToken(token) {
            localStorage.setItem(localToken, token);
        }

        function login(token) {
            setToken(token);
            $rootScope.$broadcast('auth:login', getUser());
        }

        function logout() {
            localStorage.removeItem(localToken);

            $rootScope.$broadcast('auth:login', null);
        }

        function getUser() {
            var token = getToken();
            if (token) {
                try {
                    return jwtHelper.decodeToken(token).user;
                } catch(err) {}
            }
            return [];
        }

        function tokenExpired() {
            return jwtHelper.isTokenExpired(getToken());
        }

        function isLoggedIn() {
            return getUser().id > 0;
        }

        function refreshToken() {
            if (refreshingToken == null) {
                refreshingToken = $http({
                    url: '/internal/auth/refresh',
                    skipAuthorization: true,
                    method: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + getToken()
                    }
                }).then(function(response) {
                    var token = response.data.token;
                    setToken(token);
                    refreshingToken = null;

                    return token;
                });
            }

            return refreshingToken;
        }
    }
})();
