var app = angular.module("YouOweApp", []);

app.controller("AppController", function($scope, $http) {
    $scope.currency = 'р.';

    var loadData = function () {
        $http.get('v1/users.json').success(function (data) {
            $scope.$emit('authSucceed');
            $scope.authorized = true;
            $scope.users = data;
        }).error(function () {
            jqtouch.goTo('#register');
            $scope.authorized = false;
        });
        $scope.$on('authSucceed', function() {
            $http.get('v1/debts/oweyou.json').success(function(data) {
                $scope.oweYou = data;
            });

            $http.get('v1/debts/youowe.json').success(function(data) {
                $scope.youOwe = data;
            });
        });
    };

    $scope.$on('tryLogin', loadData);
    $scope.$on('logout', function () {
        $scope.authorized = false;
        $scope.users = [];
        $scope.oweYou = [];
        $scope.youOwe = [];
    });

    if (window.location.hash != '#register') {
        loadData();
    }

    $scope.logout = function () {
        $scope.$emit('logout');
        $http.post('http://::@youowe.localhost:8080/v1/logout').success(function() {
            $scope.$emit('tryLogin');
        }).error(function () {
            jqtouch.goTo('#register');
        });
    }
});

app.controller("RegisterController", function($scope, $http) {
    $scope.register = function () {
        $http.post('v1/users', $scope.user).success(function (data, status) {
            if (status == 201) {
                alert('success!');
            } else {
                alert('error');
            }
        });
    };

    $scope.login = function () {
        jqtouch.goTo('#home');
        $scope.$emit('tryLogin');
    }
});
