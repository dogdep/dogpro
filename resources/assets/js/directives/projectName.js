(function() {
    angular
        .module('dp')
        .directive('dpProjectName', dpProjectName)
        .directive('dpProjectGroup', dpProjectGroup);

    function dpProjectName() {
        return {
            restrict: 'A',
            scope: {
                name: '=dpProjectName',
                model: '=ngModel'
            },
            link: function($scope, element) {
                $scope.$watch('name', function(url) {
                    var value = parseUrl(url);

                    if (!value) {
                        return;
                    }

                    var name = value.join();
                    if (value.length == 2) {
                        name = value[1];
                    }

                    name = name.replace(/[^a-zA-Z0-9]+/g, '').toLowerCase();
                    element.val(name);
                    $scope.model = name;
                });
            }
        };
    }

    function dpProjectGroup() {
        return {
            restrict: 'A',
            scope: {
                name: '=dpProjectGroup',
                model: '=ngModel'
            },
            link: function($scope, element) {
                $scope.$watch('name', function(url) {
                    var value = parseUrl(url);
                    var group = "";

                    if (value && value.length == 2) {
                        group = value[0].replace(/[^a-zA-Z0-9]+/g, '').toLowerCase();
                    }

                    element.val(group);
                    $scope.model = group;
                });
            }
        };
    }

    function parseUrl(value) {
        if (value == undefined) {
            return;
        }

        var domain = value.split(":");
        if (domain[1] == undefined) {
            return;
        }

        var path = domain[1].split('.');
        if (path[1] == undefined) {
            return;
        }

        return path[0].split('/');
    }

})();
