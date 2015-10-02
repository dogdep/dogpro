(function() {
    angular
        .module('dp')
        .filter("arrayToString", isArray);

    function isArray() {
        return function(input) {
            if (!angular.isArray(input)) {
                return input;
            }

            return input.join(',');
        };
    }
})();
