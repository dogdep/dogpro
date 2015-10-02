(function() {
    angular
        .module('dp')
        .filter("isArray", isArray);

    function isArray() {
        return function(input) {
            return angular.isArray(input);
        };
    }
})();
