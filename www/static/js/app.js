var app = angular.module("YouOweApp", []);

app.controller("AppController", function($scope, $http) {
    $scope.currency = 'р.';

    if (window.location.hash != '#register') {
        $http.get('v1/users.json').success(function (data) {
            $scope.authorized = true;
            $scope.users = data;
        }).error(function () {
            jqtouch.goTo('#register');
            $scope.authorized = false;
        });
//    $http.get('v1/debts/oweyou.json').success(function(data) {
//        $scope.oweYou = data;
//    });
//
//    $http.get('v1/debts/youowe.json').success(function(data) {
//        $scope.youOwe = data;
//    });
    }
});

app.controller("RegisterController", function($scope, $http) {
    $scope.register = function () {
        $http.post('v1/users', $scope.user).success(function () {
            alert('success!');
        }).error(function(){
            alert('error');
        });
    };
});
