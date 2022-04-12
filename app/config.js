app.config([ '$routeProvider', '$httpProvider',
		function($routeProvider, $httpProvider) {
			$routeProvider
			.when("/", {
				templateUrl : "app/login/views/login.html",
				controller : "LoginController",
				resolve : {
					loadModule : [ '$ocLazyLoad', function(lazyload) {
						return lazyload.load('loginApp');
					} ]
				}
			})
			.when("/pos_dashboard", {
				templateUrl : "app/dashboard/view/dashboard.html",
			})
			.otherwise({
				redirectTo : "/error"
			});
		} ]);


app.run(function($rootScope,$timeout,$location,$window , AppService,$route) {
	
	$rootScope.$on('$routeChangeStart', function(event, next, current) {
		/*var query_param = AppService.parseQueryString();
		$rootScope.login_error = "";
		if(query_param['g'] ){
			$window.sessionStorage.setItem('group_name',query_param['g']);
		}
		
		if($location.path() === '/' && $window.sessionStorage.getItem('user_name')){
			$location.url("/pos_dashboard");
		}else{
			$location.url("/?g="+query_param['g']);
		}*/
		/*if(query_param['session_timeout'] && query_param['session_timeout'] == '1') {
			if(current && current.scope && current.scope.portal) {
				current.scope.portal.logged_in_group_id = "";
                		current.scope.portal.setBranding();
                		delete(current.scope.portal.is_masqueraded);
                		delete(current.scope.portal.user_name);
			}
			$rootScope.login_error = 'Your session has timed out.<br>Please login again to continue.';
		}*/
	});
	
});