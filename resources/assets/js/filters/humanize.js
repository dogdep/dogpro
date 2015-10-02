(function() {
    angular
        .module('dp')
        .filter("humanizeRole", isArray);

    function isArray() {
        return function(text) {
            if(!text) {
                return text;
            }

            text = text.split("_").join(" ").toLowerCase();
            text = text.split(".").join(" ").toLowerCase();

            return text.charAt(0).toUpperCase() + text.slice(1);
        };
    }
})();
