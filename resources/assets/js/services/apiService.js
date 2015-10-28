(function() {
    angular
        .module('dp')
        .service('api', service);

    function service($resource) {
        return {
            repos: $resource("/api/repo/:id", {id: '@id'}, {
                pull: { method: "POST", url: "/api/repo/:id/pull", params: {id: '@id'}},
                assignUser: { method: "POST", url: "/api/repo/:id/user/:user_id", params: {id: '@id'}},
                removeUser: { method: "DELETE", url: "/api/repo/:id/user/:user_id", params: {id: '@id'}},
                config: { method: "GET", url: "/api/repo/:id/config/:commit", params: {id: '@id', commit: '@commit'}}
            }),
            commits: $resource("/api/repo/:id/commit/:commit", {id: '@id', commit: '@commit'}, {
                query: { url: "/api/repo/:id/commit/query/:page", isArray: true }
            }),
            releases: $resource("/api/release/:id", {id: '@id'}),
            config: $resource("/api/config"),
            roles: $resource("/api/roles"),
            users: $resource("/api/user"),
            inventories: $resource("/api/inventory/:id", {id: '@id'})
        }
    }
})();
