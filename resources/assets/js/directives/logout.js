(function() {
    angular
        .module('dp')
        .directive('dpLogout', dir);

    function dir() {
        return {
            restrict: 'A',
            template:
                '<ul class="nav navbar-nav navbar-right">' +
                    '<li ng-if="user.avatar"><img ng-src="{{ user.avatar }}" class="img-rounded user-image"/></li>' +
                    '<li><a href="#" ng-click="logout()">Logout ({{ user.name }})</a></li>' +
                '</ul>',
            controller: function($scope, $state, authFactory) {
                $scope.user = authFactory.getUser();
                $scope.logout = function() {
                    authFactory.logout();
                    $state.go('anon.login');
                }
            }
        };
    }
})();
