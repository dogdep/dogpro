(function() {
    angular
        .module('dp')
        .directive('dpAutorefresh', dir);

    function dir() {
        return {
            restrict: 'AE',
            template: '<label class="checkbox"><input type="checkbox" ng-model="autorefresh"/> Auto refresh</label>',
            scope: {
                callback: '&dpAutorefresh',
                interval: '=',
                auto: '='
            },
            controller: function($rootScope, $scope, $interval, $q, $timeout) {
                $scope.refreshInProgress = false;
                $scope.autorefresh = true;
                $scope.refreshInterval = $scope.interval || 5000;
                $scope.initRefresh = $scope.auto || true;

                var shouldRefresh = $scope.autorefresh;
                var idle = false;
                var stop;

                $scope.$on('$destroy', destroyHandler);
                $scope.$on('stop-refreshing', onStopRefreshing);
                $scope.$on('start-refreshing', onStartRefreshing);
                $scope.$on('request-refresh', onRequestRefresh);
                ifvisible.setIdleDuration(360);
                ifvisible.on("idle", registerAfkListener);
                ifvisible.on("wakeup", registerBackListener);

                $rootScope.$broadcast('stop-refreshing', $scope.$id);

                if ($scope.initRefresh) {
                    onRequestRefresh();
                }

                stop = $interval(function() {
                    onRequestRefresh();
                }, $scope.refreshInterval);

                function doRefresh() {
                    if ($scope.refreshInProgress) {
                        return false;
                    }
                    $scope.refreshInProgress = true;

                    return true;
                }

                function onRequestRefresh() {
                    if ($scope.autorefresh && doRefresh()) {
                        $q.when($scope.callback()).finally(function() {
                            $scope.refreshInProgress = false;
                        });
                    }
                }

                function onStopRefreshing(e, scopeId) {
                    if ($scope.$id == scopeId) {
                        return;
                    }
                    shouldRefresh = $scope.autorefresh;
                    $scope.autorefresh = false;
                }

                function onStartRefreshing() {
                    $scope.autorefresh = shouldRefresh;
                }

                function registerAfkListener() {
                    if (idle) {
                        return;
                    }
                    $timeout(function() {
                        shouldRefresh = $scope.autorefresh;
                        $scope.autorefresh = false;
                        idle = true;
                        console.info('User is AFK');
                    }, 1);
                }

                function registerBackListener() {
                    $timeout(function() {
                        $scope.autorefresh = shouldRefresh;
                        idle = false;
                        console.info('User came back');
                    }, 1);
                }

                function destroyHandler() {
                    $interval.cancel(stop);
                    $rootScope.$broadcast('start-refreshing');

                    ifvisible.off("idle", registerAfkListener);
                    ifvisible.off("wakeup", registerBackListener);
                }
            }
        };
    }
})();
