(function() {
    angular
        .module('dp')
        .controller("RepoCreateCtrl", ctrl);

    function ctrl($scope, api, repos, toaster, $state) {
        $scope.repo = new api.repos;

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
