(function() {
    angular
        .module('dp')
        .directive('dpBody', dir);

    function dir() {
        return {
            restrict: 'AE',
            link: function($rootScope, element) {
                $rootScope.$watch('bodyClass', function(value) {
                    element.attr('class', value);
                });
            }
        };
    }
})();
