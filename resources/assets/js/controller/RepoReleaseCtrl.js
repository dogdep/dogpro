
(function() {
    angular
        .module('dp')
        .controller("RepoReleaseCtrl", ctrl);

    function ctrl($scope, repo, release, ansi2html, $sce, Pusher, $timeout, $interval) {
        $scope.release = release;
        $scope.logs = $sce.trustAsHtml(ansi2html.toHtml(release.raw_log));
        $scope.lastTask = getLastTask(release.raw_log);
        $scope.repo = repo;

        var pusher = Pusher.pusher().subscribe("releases");

        pusher.bind("release-" + release.id, onReleaseUpdate);

        $scope.sumStats = function (data) {
            var counter = 0;
            angular.forEach(data, function(c) {
                counter += c;
            });
            return counter;
        };

        $scope.canCancel = function(){
            return ["preparing", "queued", "running"].indexOf(release.status) > -1;
        };

        $scope.cancel = function() {
            $scope.release.status = 'cancelled';
            $scope.release.$save();
        };

        $scope.retry = function() {
            $scope.release.status = 'queued';
            $scope.release.$save();
        };

        var stop = $interval(function() {
            if ($scope.release.status != 'running') {
                $scope.progress = false;
                return;
            }

            if ($scope.release.started_at && $scope.release.time_avg > 0 ) {
                var startedAt = parseInt($scope.release.started_at) * 1000;
                var now = (new Date()).getTime();
                var expected = parseInt($scope.release.time_avg) * 1000;

                if (now - startedAt > expected) {
                    $scope.progress = 95;
                } else {
                    $scope.progress = (now - startedAt) / expected;
                }

                $scope.progress = Math.round($scope.progress * 100);

                var diff = expected - (now - startedAt);
                if (diff > 0) {
                    $scope.progressLabel = "-" + moment(diff).format("mm:ss");
                    $scope.progressOvertime = false;
                } else {
                    $scope.progressLabel = "+" + moment(-diff).format("mm:ss");
                    $scope.progressOvertime = true;
                }
            } else {
                $scope.progress = 100;
                $scope.progressLabel = "No estimate is available"
            }
        }, 100);

        $scope.$on('$destroy', function(){
            pusher.unbind("release-" + release.id, onReleaseUpdate);
            $interval.cancel(stop);
        });

        function onReleaseUpdate(release) {
            $timeout(function(){
                angular.extend($scope.release, release);
                if (typeof release.raw_log == "string") {
                    $scope.logs = $sce.trustAsHtml(ansi2html.toHtml(release.raw_log));
                    $scope.lastTask = getLastTask(release.raw_log);
                }
            });
        }

        function getLastTask(log) {
            var matches = log.match(/TASK:\s\[(.*)]/igm);

            if (matches && matches.length) {
                return matches
                    .pop()
                    .replace(/^TASK:\s\[\s*(dogpro.\w+)?\s*\|?\s*/, '')
                    .replace(/]\s*$/, '')
                    .replace(/\|/g, '-')
                    .toLowerCase();
            }

            return "";
        }
    }
})();
