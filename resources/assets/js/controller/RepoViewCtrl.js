(function() {
    angular
        .module('dp')
        .controller("RepoViewCtrl", ctrl);

    function ctrl($scope, repo, $state, repos, toaster) {
        $scope.repo = repo;
        $scope.$state = $state;

        $scope.delete = function() {
            if (!confirm("Delete repository?")) {
                return;
            }

            repo.$delete(function(){
                angular.forEach(repos, function(r, i){
                    if (repo.id == r.id) {
                        repos.splice(i, 1);
                    }
                });

                toaster.pop('success', 'Repository has been deleted.');
                $state.go("user.repo.index");
            });
        }
    }
})();
