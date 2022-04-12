app.directive("appHeader", function () {
    return {
        restrict: 'E',
        transclude: true,
        scope: {
            rootData: '='
        },
        templateUrl: 'app/directories/material_directories/header.html',
        controller: ['$scope',function(a){
            a.x_h = a.rootData.list_data;
            console.log(a.x_h);
        }]
    }
});
app.directive("sideSlider", function () {
    return {
        restrict: 'E',
        transclude: true,
        scope: {
            rootData: '='
        },
        templateUrl: 'app/directories/material_directories/slider.html',
        controller: ['$scope',function(a){
        }]
    }
});
