var loginApp = angular.module('LoginApp', [])

loginApp.controller('LoginController', ['$scope','AppFacade','$window','$location',
                                        function(scope, AppFacade, window, location) {

	scope.login_details = {};
	scope.credentials = {
			user_name:'rameshbabu',
			password:'rameshbabu',
			group_name: window.sessionStorage.getItem('group_name')
	};

	scope.authenticate = function(credentials) {
		console.log(credentials);
		AppFacade.authenticate(credentials)
		.success(function(data, status, headers, config) {
			if (data.transaction_code
					&& data.transaction_code == "0000") {
				scope.posData = data.login_details;
				window.sessionStorage.setItem('access_permissions',data.login_details.access_permissions);
				window.sessionStorage.setItem('email_address',data.login_details.email_address);
				window.sessionStorage.setItem('first_name',data.login_details.first_name);
				window.sessionStorage.setItem('group_attention',data.login_details.group_attention);
				window.sessionStorage.setItem('group_id',data.login_details.group_id);
				window.sessionStorage.setItem('group_name',data.login_details.group_name);
				window.sessionStorage.setItem('group_type',data.login_details.group_type);
				window.sessionStorage.setItem('last_name',data.login_details.last_name);
				window.sessionStorage.setItem('phone_number',data.login_details.phone_number);
				window.sessionStorage.setItem('user_id',data.login_details.user_id);
				window.sessionStorage.setItem('user_name',data.login_details.user_name);
				location.url('/pos_dashboard');
			} else {
			}
		})
		.error(function(data, status, headers, config) {
			scope.error_alert = "Unknown error occured";
		});
	}
}]);