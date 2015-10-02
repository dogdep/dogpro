(function() {
    angular
        .module('dp')
        .controller("RepoReleasesCtrl", ctrl);

    function ctrl($scope, repo, releases) {
        $scope.repo = repo;
        $scope.releases = releases;
    }
})();
