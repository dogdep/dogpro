(function() {
    angular
        .module('dp')
        .factory('Pusher', push);

    function push() {
        var pusher = new Pusher('{$TOKEN:PUSHER_KEY}', {
            encrypted: true
        });
        var pulls = pusher.subscribe('pulls');

        return {
            pusher: function() {
                return pusher;
            },
            pulls: function() {
                return pulls;
            }
        };
    }
})();
