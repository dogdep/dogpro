(function() {
    angular
        .module('dp')
        .service('api', service);

    function service($resource) {
        return {
            repos: $resource("/api/repo/:id", {id: '@id'}, {
                pull: { method: "POST", url: "/api/repo/:id/pull", params: {id: '@id'}},
                assignUser: { method: "POST", url: "/api/repo/:id/user/:user_id", params: {id: '@id'}},
                removeUser: { method: "DELETE", url: "/api/repo/:id/user/:user_id", params: {id: '@id'}}
            }),
            releases: $resource("/api/repo/:repo_id/release/:id", {id: '@id', repo_id: '@repo_id'}, {
                log: { method: "GET", url: "/api/repo/:repo_id/release/:id/log", params: {id: '@id'}, isArray: true},
                config: { method: "GET", url: "/api/repo/:id/release/:commit/config", params: {id: '@id', commit: '@commit'}}
            }),
            keys: $resource("/api/keys/:id", {id: '@id'}),
            commits: $resource("/api/repo/:id/commit/:commit", {id: '@id', commit: '@commit'}, {
                query: { url: "/api/repo/:id/commit/query/:page", isArray: true }
            }),
            roles: $resource("/api/repo/:repo_id/role/:id", {repo_id: '@repo_id', id: '@id'}, {
                all: { method: "GET", url: "/api/roles/all", params: {id: '@id'}}
            }),
            inventories: $resource("/api/repo/:repo_id/inventory/:id", {repo_id: '@repo_id', id: '@id'}),
            users: $resource("/api/user")
        }
    }
})();
