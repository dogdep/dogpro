(function() {
    angular
        .module('dp')
        .controller('GitlabCtrl', controller);

    function controller($state, AuthFactory, $http, code, toaster) {
        if (code == undefined) {
            $state.go('anon.login');
        } else {
            $http({url: "/internal/auth/gitlab?code=" + code, skipAuthorization: true, method: 'GET'})
                .then(function(response) {
                    AuthFactory.login(response.data.token);
                    $state.go('user.repo.index');
                }, function() {
                    toaster.pop('error', "Something went wrong...");
                    $state.go('anon.login');
                });
        }
    }
})();
