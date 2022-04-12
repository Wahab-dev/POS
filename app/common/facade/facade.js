app.factory('AppFacade', function($http,$upload) {
    
    var _authenticate = function(data){
        return $http.post('index.php?pos=user/authenticate',data);
	};
	
	var _logout = function(data){
        return $http.post('index.php?pos=user/logout');
	};
	
	return{
		authenticate:_authenticate,
		logout:_logout
	};

});
