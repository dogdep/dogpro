(function() {
    angular
        .module('dp')
        .controller("UserCtrl", ctrl);

    function ctrl($scope, repos, $state, $rootScope, user) {
        $scope.user = user;
        $rootScope.$on('$stateChangeError', function(e, toState, toParams, fromState, fromParams, error) {
            console.error(error);
        });

        $scope.repos = repos;

        $scope.isActive = function(repo) {
            return $state.includes('user.repo') && $state.params.id == repo.id;
        };

        $scope.getActive = function() {
            for (var i=0; i<repos.length;i++) {
                if (repos[i].id == $state.params.id) {
                    return repos[i];
                }
            }

            return null;
        };
    }
})();
