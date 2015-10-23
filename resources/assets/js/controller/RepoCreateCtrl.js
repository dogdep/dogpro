(function() {
    angular
        .module('dp')
        .controller("RepoCreateCtrl", ctrl);

    function ctrl($scope, api, repos, toaster, $state, config) {
        $scope.repo = new api.repos;
        $scope.publicKey = config.public_key;

        $scope.create = function() {
            $scope.repo.$save(function(repo) {
                repos.push(repo);
                $scope.repo = new api.repos;
                toaster.pop('success', 'Repository has been added.');
                $state.go('user.repo.view', {id: repo.id});
            });
        };
    }
})();
