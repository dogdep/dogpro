(function() {
    angular
        .module('dp')
        .controller('KeysCtrl', controller);

    function controller($scope, keys, api, toaster) {
        $scope.keys = keys;

        $scope.key = new api.keys();
        $scope.save = function() {
            $scope.key.$save(function(){
                $scope.keys.push($scope.key);
                $scope.key = new api.keys();
                toaster.pop("success", "Key created");
            });

        };

        $scope.delete = function(key) {
            key.$delete(function (){
                toaster.pop("success", "Key deleted");
                for (var i=0; i<keys.length; i++) {
                    if (keys[i].id == key.id) {
                        keys.splice(i, 1);
                    }
                }
            });
        }
    }
})();
