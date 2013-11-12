var app = angular.module("YouOweApp", ['ngRoute', 'globalErrors']);

app.config(function ($routeProvider) {
    $routeProvider.
        when('/', {
            templateUrl: 'partials/main.html',
            controller: 'AppController'
        }).
        when('/register', {
            templateUrl: 'partials/register.html',
            controller: 'RegisterController'
        }).
        when('/add', {
            templateUrl: 'partials/add.html',
            controller: 'AddDebtController'
        }).
        when('/history/:userId', {
            templateUrl: 'partials/history.html',
            controller: 'HistoryController'
        }).
        otherwise({
            redirectTo: '/'
        });
});

app.factory('Settings', function(){
    return {
        currency: 'р.'
    }
});

app.factory('Users', function($http, $rootScope, $location) {
    var users = {
        list: {}
    };

    users.loadData = function(successCallback) {
        $http.get('/v1/users.json').success(function (data) {
            users.list = data;
            successCallback();
        }).error(function () {
            $location.path('/register');
        });
    };

    users.clearData = function() {
        users.list = {};
    };

    return users;
});

app.factory('User', function ($http, $rootScope, $location, Users) {
    var user = {
        loggedIn: false,
        oweYou: {},
        youOwe: {}
    };

    user.emptyOweYou = function() {
        return Object.keys(this.oweYou).length == 0;
    };

    user.emptyYouOwe = function() {
        return Object.keys(this.youOwe).length == 0;
    };

    user.login = function () {
        Users.loadData(function() {
            user.loggedIn = true;
            $http.get('/v1/debts/debts.json').success(function (data) {
                user.oweYou = data.youGave;
                user.youOwe = data.youTake;
            });
            $rootScope.$broadcast('loginSuccess');
        });
    };

    user.logout = function () {
        Users.clearData();
        this.loggedIn = false;
        this.oweYou = {};
        this.youOwe = {};

        $http.post($location.protocol() + '://::@' + $location.host() + ':' + $location.port() + '/v1/logout').success(function () {
            user.login();
        }).error(function () {
            $location.path('/register');
        });
    };

    return user;
});

/****************
 * CONTROLLERS
 */
app.controller("AppController", function ($scope, $window, $location, Users, User, Settings) {
    $scope.settings = Settings;
    $scope.user = User;
    $scope.users = Users;

    if (!User.loggedIn) {
        User.login();
    }
});

app.controller("RegisterController", function ($scope, $http, $location, User) {
    $scope.user = User;

    $scope.register = function () {
        if (!$scope.registerForm.$valid) {
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

    $scope.$on('loginSuccess', function() {
        $location.path('/');
    });
});

app.controller("AddDebtController", function($scope, $http, $location, Settings, User, Users) {
    if (!User.loggedIn) {
        User.login();
    }

    $scope.settings = Settings;
    $scope.users = Users;

    $scope.addDebt = function () {
        if (!$scope.AddDebtForm.$valid) {
            alert('Пожалуйста исправьте ошибки и попробуйте заново');
        } else {
            $http.post('v1/debts', $scope.newDebt).success(function (data, status) {
                if (status == 201) {
                    User.oweYou.push(data);
                    $location.path('/');
                } else {
                    alert('error: '. data);
                }
            });
        }
    };
});

app.controller("HistoryController", function($scope, $routeParams, Settings, User, Users) {
    if (!User.loggedIn) {
        User.login();
    }

    $scope.settings = Settings;
    $scope.users = Users;
    $scope.userId = $routeParams.userId;
});