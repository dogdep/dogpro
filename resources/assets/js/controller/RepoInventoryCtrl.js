(function() {
    angular
        .module('dp')
        .controller("RepoInventoryCtrl", ctrl);

    function ctrl($scope, api, repo, toaster) {
        $scope.inventory = newInv();
        $scope.repo = repo;

        $scope.create = function() {
            $scope.inventory.$save(function(inv) {
                repo.inventories.push(inv);
                $scope.inventory = newInv();
                toaster.pop('success', 'Inventory has been added.');
            });
        };

        $scope.remove = function(inventory) {
            if (!confirm("Delete inventory?")) {
                return;
            }

            api.inventories.delete({repo_id: repo.id, id: inventory.id}).$promise.then(function() {
                repo.inventories.splice(repo.inventories.map(function (x) {return x.id;}).indexOf(inventory.id), 1);
                toaster.pop('success', 'Inventory has been deleted.');
            });
        };

        $scope.update = function(inventory) {
            api.inventories.save(inventory).$promise.then(function() {
                toaster.pop('success', 'Inventory has been saved.');
            });
        };

        function newInv() {
            return new api.inventories({repo_id: repo.id});
        }
    }
})();
