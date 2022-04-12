var app = angular.module('posApp',['oc.lazyLoad','ngRoute','angularFileUpload',
                                   'scrollable-table','ngMaterial','ngMdIcons']);

angular.module('posApp').config(['$ocLazyLoadProvider',function($ocLazyLoadProvider){
	$ocLazyLoadProvider.config({
		debug: false,
		events: false,
		loadedModules: ['posApp'],
		modules:[
		        {
		        	name: 'loginApp',
		        	files: ['app/login/controller/logincontroller.js']
		        }
		        ]
	})
}]);
		        	