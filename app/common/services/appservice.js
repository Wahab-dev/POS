app.factory('AppService', ['$location','$rootScope',
                              function(location,rootScope){
	
	// Method to parse query string parameter from URL
	var _parseQueryString = function() {
		var path_tokens = location.absUrl().split('?');
		var query_string = path_tokens[path_tokens.length - 1];
		var query_tokens = query_string.split('&');
		var query_param = [];
		for(i=0;i<query_tokens.length;i++) {
			key_value = query_tokens[i].split('=');
			if(key_value.length != 2) continue;
			query_param[key_value[0]] = key_value[1]; 
		}
		return query_param;		
	};
	
	return {
		parseQueryString:_parseQueryString,
	}
}]);