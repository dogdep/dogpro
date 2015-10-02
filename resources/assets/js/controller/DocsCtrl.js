(function() {
    angular
        .module('dp')
        .controller('DocsCtrl', controller);

    function controller($state, $scope) {
        $scope.$state = $state;
    }
})();
