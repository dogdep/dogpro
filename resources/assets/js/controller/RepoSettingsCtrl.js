(function() {
    angular
        .module('dp')
        .controller("RepoSettingsCtrl", ctrl);

    function ctrl($scope, repo, repos, toaster, $state, api, users, user) {
        $scope.repo = repo;
        $scope.user = user;
        $scope.save = function() {
            api.repos.save(repo, function(){
                toaster.pop('success', 'Repository settings saved.');
            });
        };

        $scope.removeUser = function(user) {
            repo.$removeUser({user_id: user.id});
        };

        $scope.assignUser = function(userId) {
            repo.$assignUser({user_id: userId});
        };

        $scope.isAssigned = function(user) {
            for(var i=0;i<$scope.repo.users.length;i++) {
                if (repo.users[i].id == user.id) {
                    return true;
                }
            }
            return false;
        };

        $scope.delete = function() {
            repo.$delete(function(){
                angular.forEach(repos, function(r, i){
                    if (repo.id == r.id) {
                        repos.splice(i, 1);
                    }
                });

                toaster.pop('success', 'Repository has been deleted.');
                $state.go("user.repo.index");
            });
        };

        $scope.users = users;
    }
})();
