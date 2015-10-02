(function() {
    angular
        .module('dp')
        .controller("RepoDeployCtrl", ctrl);

    function ctrl($scope, commit, repo, api, toaster, $state, config) {
        $scope.config = config;
        $scope.release = {
            repo_id: repo.id,
            commit: commit.hash,
            inventory_id: repo.inventories[0].id.toString() || null,
            roles: {}
        };

        $scope.commit = commit;
        $scope.repo = repo;

        $scope.deploy = function(){
            api.releases.save($scope.release, function(release){
                toaster.pop('success', 'Deployment scheduled.');
                $scope.$close(true);
                $state.go("user.repo.view.release", {id: release.repo_id, release_id: release.id});
            });
        };
    }
})();
