angular.module('dp').config(function($locationProvider, $stateProvider, $urlRouterProvider) {

    $locationProvider.html5Mode(true);
    $urlRouterProvider.otherwise("/");


    $stateProvider
        .state('anon', {
            abstract: true,
            template: "<ui-view/>"
        })
        .state('anon.check', {
            url: '/',
            controller: function($state, AuthFactory) {
                if (AuthFactory.isLoggedIn()) {
                    $state.go('user.repo.index');
                } else {
                    $state.go('anon.login');
                }
            }
        })
        .state('anon.login', {
            url: '/login?error',
            templateUrl: '/templates/auth/login.html',
            controller: function($rootScope, $scope, providers, $stateParams, toaster) {
                $rootScope.bodyClass = 'login';
                $scope.providers = providers;
                if ($stateParams.error) {
                    toaster.pop("error", $stateParams.error);
                }
            },
            resolve: {
                providers: function($http) {
                    return $http({url: '/internal/auth/providers', skipAuthorization: true, method: 'GET'})
                        .then(function(res) {
                            return res.data;
                        });
                }
            }
        })
        .state('anon.login_handle', {
            url: '/login/handle/:token',
            controller: function(AuthFactory, $state, $stateParams) {
                AuthFactory.login($stateParams.token);
                $state.go('user.repo.index');
            }
        })
        .state('anon.gitlab', {
            url: '/gitlab?code',
            controller: 'GitlabCtrl',
            resolve: {
                code: function($stateParams) {
                    return $stateParams.code;
                }
            }
        })
        .state('user', {
            url: '/',
            controller: "UserCtrl",
            templateUrl: "/templates/layout.html",
            resolve: {
                user: function(AuthFactory) {
                    return AuthFactory.getUser();
                },
                repos: function(api) {
                    return api.repos.query().$promise;
                }
            }
        })
        .state('user.docs', {
            url: "docs",
            templateUrl: '/templates/docs/docs.html',
            controller: 'DocsCtrl',
            resolve: {
                roleConfig: function (api) {
                    return api.roles.get().$promise;
                }
            }
        })
        .state('user.docs.ansible', {
            url: "/ansible",
            templateUrl: '/templates/docs/ansible.html'
        })
        .state('user.docs.variables', {
            url: "/variables",
            templateUrl: '/templates/docs/variables.html'
        })
        .state('user.docs.config', {
            url: "/generator",
            controller: 'ConfigGeneratorCtrl',
            templateUrl: '/templates/docs/config_generator.html'
        })
        .state('user.docs.roles', {
            url: "/roles",
            controller: function($scope, roleConfig) {
                $scope.roles = roleConfig;
                $scope.typeOf = function(i){
                    return typeof i;
                }
            },
            templateUrl: '/templates/docs/config.html'
        })
        .state('user.repo', {
            url: "repo",
            abstract: true,
            template: '<ui-view class="anim-in-out anim-slide-below-fade" data-anim-speed="500" data-anim-sync="true"/>'
        })
        .state('user.repo.index', {
            url: "",
            controller: "RepoIndexCtrl",
            templateUrl: "/templates/repo/index.html"
        })
        .state('user.repo.create', {
            url: "/create",
            controller: "RepoCreateCtrl",
            templateUrl: "/templates/repo\/create.html",
            resolve: {
                config: function(api) {
                    return api.config.get().$promise;
                }
            }
        })
        .state('user.repo.view', {
            url: "/:id",
            parent: 'user.repo',
            controller: "RepoViewCtrl",
            templateUrl: "/templates/repo/view.html",
            resolve: {
                repo: function (repos, $stateParams) {
                    return repos[repos.map(function(x) { return x.id }).indexOf(parseInt($stateParams.id))];
                }
            }
        })
        .state('user.repo.view.releases', {
            url: "/releases",
            controller: "RepoReleasesCtrl",
            templateUrl: "/templates/repo/releases.html",
            resolve: {
                releases: function ($stateParams, api) {
                    return api.releases.query({repo_id: $stateParams.id}).$promise;
                }
            }
        })
        .state('user.repo.view.release', {
            url: "/releases/:release_id",
            controller: "RepoReleaseCtrl",
            templateUrl: "/templates/repo/release.html",
            resolve: {
                release: function ($stateParams, api) {
                    return api.releases.get({id: $stateParams.release_id}).$promise;
                }
            }
        })
        .state('user.repo.view.settings', {
            url: "/settings",
            controller: "RepoSettingsCtrl",
            templateUrl: "/templates/repo/settings.html",
            resolve: {
                users: function(api, user) {
                    return user.admin ? api.users.query().$promise : [];
                }
            }
        })
        .state('user.repo.view.modal', {
            abstract: true,
            onEnter: function($modal, $state) {
                var stateChanged = false;
                $modal.open({
                    size: 'lg',
                    template: "<div ui-view='commitModal'></div>",
                    controller: function ($state, $scope, $modalInstance) {
                        $scope.$close = function(a){
                            $modalInstance.close(a);
                        };
                    }
                }).result.then(function(result) {
                    stateChanged = !!result;
                }).finally(function(){
                    if (!stateChanged) {
                        $state.go('user.repo.view.commits');
                    }
                });
            }
        })
        .state('user.repo.view.modal.deploy', {
            parent: 'user.repo.view.modal',
            url: "/deploy/:commit",
            views: {
                'commitModal@': {
                    controller: "RepoDeployCtrl",
                    templateUrl: "/templates/repo/deploy.html"
                }
            },
            resolve: {
                config: function(api, $stateParams) {
                    return api.repos.config({id: $stateParams.id, commit: $stateParams.commit}).$promise;
                },
                commit: function(api, $stateParams) {
                    return api.commits.get({commit: $stateParams.commit, id: $stateParams.id}).$promise;
                }
            }
        })
        .state('user.repo.view.inventory', {
            url: "/inventory",
            controller: "RepoInventoryCtrl",
            templateUrl: "/templates/repo/inventory.html"
        })
        .state('user.repo.view.commits', {
            url: "/commits/:page/:branch",
            controller: "RepoCommitsCtrl",
            templateUrl: "/templates/repo/commits.html",
            params: { page: "1", branch: "master" },
            resolve: {
                commits: function (api, $stateParams) {
                    return api.commits.query({id: $stateParams.id, page: $stateParams.page, branch: $stateParams.branch}).$promise;
                },
                branches: function (api, $stateParams) {
                    return api.branches.query({id: $stateParams.id}).$promise;
                }
            }
        })
    ;
});
