(function() {
    angular
        .module('dp')
        .directive('dpTimeAgo', dir);

    function dir() {
        return {
            replace: true,
            template: '<div class="time" am-time-ago="dpTimeAgo" tooltip="{{ dpTimeAgo|date:\'MMM d, y HH:mm\' }}" tooltip-placement="bottom"></div>',
            scope: {
                dpTimeAgo: '='
            }
        };
    }
})();
