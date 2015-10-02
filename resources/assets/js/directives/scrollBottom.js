(function() {
    angular
        .module('dp')
        .directive('dpScrollBottom', dir);

    function dir($interval) {
        return {
            restrict: 'A',
            link: function(scope, element) {
                var autoScrollEnabled = true;

                var stop = $interval(function() {
                    if (autoScrollEnabled) {
                        element[0].scrollTop = element[0].scrollHeight;
                    }
                }, 200);

                element.on('scroll', function () {
                    autoScrollEnabled = element[0].scrollTop + element[0].clientHeight >= element[0].scrollHeight - 50;
                });

                scope.$on('$destroy', function() {
                    $interval.cancel(stop);
                });
            }
        };
    }
})();
