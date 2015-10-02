(function() {
    angular
        .module('dp', dependencies(), params);

    function dependencies() {
        return [
            'ui.bootstrap',
            'ngAnimate',
            'ngResource',
            'toaster',
            'ui.router',
            'angularMoment',
            'smoothScroll',
            'angular-loading-bar',
            'angular-jwt',
            'anim-in-out'
        ];
    }

    function params($httpProvider, jwtInterceptorProvider) {
        $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

        jwtInterceptorProvider.tokenGetter = /*@ngInject*/ function (config, AuthFactory) {
            var idToken = AuthFactory.getToken();

            if (config.url.substr(config.url.length - 5) == '.html') {
                return null;
            }

            if (idToken && AuthFactory.tokenExpired()) {
                return AuthFactory.refreshToken();
            }

            return idToken;
        };

        $httpProvider.interceptors.push('jwtInterceptor');
        $httpProvider.interceptors.push('AuthInterceptor');
        $httpProvider.interceptors.push('ValidationInterceptor');
    }
})();


