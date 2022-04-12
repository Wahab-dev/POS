app.controller('AppController', ['$scope','AppFacade','$q','$window','$location','AppService',
                                 function (scope, AppFacade,q,window,location,AppService) {
	
	scope.posData = {};
    scope.success_alert = '';
    scope.error_alert = '';
    
    var query_param = AppService.parseQueryString();
    var Initialize = function(){
		scope.posData = {
				"group_name":window.sessionStorage.getItem('group_name')
		};
		
		scope.posData.logout = function(){
			AppFacade.logout()
			.success(function(data, status, headers, config) {
				if (data.transaction_code
						&& data.transaction_code == "0000") {
					scope.posData.user_name = "";
					scope.posData.group_name = window.sessionStorage.getItem('group_name');
					window.sessionStorage.clear();
					window.sessionStorage.setItem('group_name',scope.posData.group_name);
					location.url("/?g=" + scope.posData.group_name);
				}
			})
			.error(function(data, status, headers, config) {
				scope.error_alert = "Unknown error occured";
			});
		}
    }
    if((window.sessionStorage.getItem('group_name') || query_param['g'])){
    	if(query_param['g'] != ''){
    		window.sessionStorage.setItem('group_name',query_param['g']);	
    	}
		Initialize();	
	}
    
}]);