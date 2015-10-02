(function() {
    angular
        .module('dp')
        .controller("RepoCommitsCtrl", ctrl);

    function ctrl($scope, repo, commits, $state, toaster, Pusher) {
        $scope.repo = repo;
        $scope.commits = commits;

        $scope.hookPopover = function() {
            return 'Add webhook for push commits ' + repo.hookUrl;
        };

        $scope.prevPage = function() {
            return parseInt($state.params.page) - 1 || false;
        };

        $scope.nextPage = function() {
            return parseInt($state.params.page) + 1 || 2;
        };

        $scope.refreshCommits = function() {
            repo.$pull(function() {
                toaster.pop('info', 'Repository pull scheduled.');

                Pusher.pulls().bind("repo-"+repo.id, reload);

                function reload() {
                    Pusher.pulls().unbind("repo-"+repo.id, reload);
                    $state.reload();
                }
            });
        };
    }
})();
