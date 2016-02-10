(function () {
    angular
        .module('dp')
        .controller('ConfigGeneratorCtrl', controller);

    function controller($scope, roleConfig) {
        $scope.roleConfig = roleConfig;
        $scope.config = {
            defaults: {
                deploy_dir: "/var/www"
            },
            plays: []
        };
        $scope.plays = {};

        $scope.removePlay = function (id) {
            $scope.config.plays.splice(id, 1);
        };

        $scope.add = function (roleId) {
            if (typeof roleConfig[roleId] == "undefined") {
                return;
            }

            $scope.new_role = undefined;
            $scope.config.plays.push({
                name: roleConfig[roleId].name,
                role: roleId
            });
        };

        $scope.$watch('config', generateConfig, true);

        function generateConfig() {
            var conf = {
                defaults: {},
                plays: []
            };

            angular.forEach($scope.config.defaults, function(value, key){
                if (value) {
                    conf.defaults[key]= value;
                }
            });

            angular.forEach($scope.config.plays, function(play, key) {
                var playConf = {};

                angular.forEach(play, function(value, key){
                    var playVars = roleConfig[play.role]['variables'];
                    if (value && key.indexOf('$') !== 0) {
                        if (playVars[key] == undefined) {
                            playConf[key]= value;
                        } else if (playVars[key].type == 'array') {
                            playConf[key]= value.split(',');
                        } else {
                            playConf[key]= value;
                        }
                    }
                });

                conf.plays.push(playConf);
            });

            $scope.configYaml = YAML.stringify(conf, 100);
        }
    }
})();

