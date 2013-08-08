var app = angular.module("YouOweApp", []);

app.factory('Settings', function(){
    return {
        currency: 'р.'
    }
});

app.factory('Users', function($http, $rootScope) {
    var users = {
        list: []
    };

    $rootScope.$on('login', function () {
        $http.get('v1/users.json').success(function (data) {
            $rootScope.$broadcast('loginSuccess');
            users.list = data;
        }).error(function () {
            $rootScope.$broadcast('loginFail');
        });
    });

    $rootScope.$on('logout', function () {
        users.list = [];
    });

    return users;
});

app.factory('User', function ($http, $rootScope, $location) {
    var user = {
        loggedIn: false,
        oweYou: [],
        youOwe: []
    };

    user.login = function () {
        $rootScope.$broadcast('login');
    };

    $rootScope.$on('loginSuccess', function() {
        user.loggedIn = true;
        $http.get('v1/debts/oweyou.json').success(function (data) {
            user.oweYou = data;
        });
        $http.get('v1/debts/youowe.json').success(function (data) {
            user.youOwe = data;
        });
    });

    user.logout = function () {
        $rootScope.$broadcast('logout');
        this.loggedIn = false;
        this.oweYou = [];
        this.youOwe = [];

        $http.post($location.protocol() + '://::@' + $location.host() + ':' + $location.port() + '/v1/logout').success(function () {
            user.login();
        }).error(function () {
            $rootScope.$broadcast('loginFail');
        });
    };

    return user;
});

/****************
 * CONTROLLERS
 */
app.controller("AppController", function ($scope, $window, Users, User, Settings) {
    $scope.settings = Settings;
    $scope.user = User;
    $scope.users = Users;

    if ($window.location.hash != '#register') {
        User.login();
    }
});

app.controller("RegisterController", function ($scope, $http, User) {
    $scope.user = User;

    $scope.register = function () {
        if (!$scope.RegisterForm.$valid) {
            alert('Пожалуйста исправьте ошибки и попробуйте зарегистрироваться заново');
        } else {
            $http.post('v1/users', $scope.user).success(function (data, status) {
                if (status == 201) {
                    alert('Теперь Вы можете войти под своими реквизитами');
                } else {
                    alert('error: '. data);
                }
            });
        }
    };

    $scope.$on('loginFail', function () {
        jqtouch.goTo('#register');
        $scope.RegisterForm.$setPristine();
        if ($scope.user) {
            $scope.user.email = '';
            $scope.user.password = '';
        }
    });

    $scope.$on('loginSuccess', function () {
        jqtouch.goTo('#home');
    });
});

app.controller("AddDebtController", function($scope, Settings) {
   $scope.settings = Settings;

    $scope.addDebt = function () {
        if (!$scope.AddDebtForm.$valid) {
            alert('Пожалуйста исправьте ошибки и попробуйте заново');
        } else {
            $http.post('v1/users', $scope.user).success(function (data, status) {
                if (status == 201) {
                    alert('Теперь Вы можете войти под своими реквизитами');
                } else {
                    alert('error: '. data);
                }
            });
        }
    };
});