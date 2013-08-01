var app = angular.module("YouOweApp", []);

app.factory('User', function ($http, $rootScope, $location) {
    var user = {
        loggedIn: false,
        users: [],
        oweYou: [],
        youOwe: []
    };

    user.login = function () {
        var that = this;
        $http.get('v1/users.json').success(function (data) {
            that.loggedIn = true;
            $rootScope.$broadcast('loginSuccess');
            that.users = data;

            $http.get('v1/debts/oweyou.json').success(function (data) {
                that.oweYou = data;
            });
            $http.get('v1/debts/youowe.json').success(function (data) {
                that.youOwe = data;
            });
        }).error(function () {
            that.loggedIn = false;
            $rootScope.$broadcast('loginFail');
        });
    };

    user.logout = function () {
        var that = this;
        this.loggedIn = false;
        this.users = [];
        this.oweYou = [];
        this.youOwe = [];

        $http.post($location.protocol() + '://::@' + $location.host() + ':' + $location.port() + '/v1/logout').success(function () {
            that.login();
        }).error(function () {
            $rootScope.$broadcast('loginFail');
        });
    };

    return user;
});

app.controller("AppController", function ($scope, $http, $window, User) {
    $scope.currency = 'р.';
    $scope.user = User;

    if ($window.location.hash != '#register') {
        User.login();
    }

    $scope.logout = function () {
        User.logout();
    }
});

app.controller("RegisterController", function ($scope, $http, $location, User) {
    /*
     $scope.register = function () {
     $http.post('v1/users', $scope.user).success(function (data, status) {
     if (status == 201) {
     alert('success!');
     } else {
     alert('error');
     }
     });
     };
     */

    $scope.$on('loginFail', function () {
        jqtouch.goTo('#register');
    });

    $scope.$on('loginSuccess', function () {
        jqtouch.goTo('#home');
    });

    $scope.login = function () {
        User.login();
    };
});
