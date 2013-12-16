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
        when('/profile', {
            templateUrl: 'partials/profile.html',
            controller: 'ProfileController'
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

app.factory('HistoryData', function() {
    var historyData = {
        list: {}
    };

    historyData.clearData = function() {
        this.list = {};
    };

    return historyData;
});

app.factory('Users', function($http, $rootScope, $location) {
    var users = {
        list: {}
    };

    users.loadData = function() {
        $http.get('/v1/users.json').success(function (data) {
            for (var i in data) {
                if (data.hasOwnProperty(i)) {
                    if (data[i].nickname != '') {
                        data[i].title = data[i].nickname + ' <' + data[i].email +'>';
                    } else {
                        data[i].title = data[i].email;
                    }
                }
            }
            users.list = data;
        }).error(function () {
            $location.path('/register');
        });
    };

    users.clearData = function() {
        this.list = {};
    };

    return users;
});

app.factory('User', function ($http, $rootScope, $location, Users, HistoryData) {
    var user = {};

    function nullUser() {
        user.loggedIn = false;
        user.id = null;
        user.nickname = null;
        user.email = null;
        user.oweYou = {};
        user.youOwe = {};
    }
    function nullSession() {
        Users.clearData();
        HistoryData.clearData();
        nullUser();
    }
    nullUser();

    user.emptyOweYou = function() {
        return Object.keys(this.oweYou).length == 0;
    };

    user.emptyYouOwe = function() {
        return Object.keys(this.youOwe).length == 0;
    };

    user.refreshSummary = function() {
        $http.get('/v1/debts/summary.json').success(function (data) {
            user.oweYou = data.youGave;
            user.youOwe = data.youTook;
        }).error(function () {
            nullSession();
            $location.path('/register');
        });
    };

    user.login = function () {
        $http.post('/v1/login').success(function (data) {
            user.id = data.id;
            user.nickname = data.nickname;
            user.email = data.email;
            user.loggedIn = true;
            user.refreshSummary();
            Users.loadData();
            $rootScope.$broadcast('loginSuccess');
        }).error(function () {
            $location.path('/register');
        });
    };

    user.logout = function () {
        nullSession();

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
    } else {
        User.refreshSummary();
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
            $http.post('/v1/debts', $scope.newDebt).success(function (data, status) {
                if (status == 201) {
                    $location.path('/');
                } else {
                    alert('error: '. data);
                }
            });
        }
    };
});

app.controller("HistoryController", function($scope, $http, $routeParams, Settings, User, Users, HistoryData) {
    if (!User.loggedIn) {
        User.login();
    }

    $scope.settings = Settings;
    $scope.users = Users;
    $scope.userId = $routeParams.userId;
    if (HistoryData.list[$routeParams.userId] != undefined) {
        $scope.history = HistoryData.list[$routeParams.userId]
    } else {
        $scope.history = [];
    }

    $http.get('/v1/debts/history/'+$routeParams.userId+'.json').success(function (data) {
        HistoryData.list[$routeParams.userId] = [];
        for (var i in data) {
            if (data.hasOwnProperty(i)) {
                HistoryData.list[$routeParams.userId].push({
                    direction: data[i].sourceUserId == $routeParams.userId ? 'gave' : 'took',
                    date: new Date(data[i].createdDate*1000),
                    sum: (data[i].sourceUserId == $routeParams.userId ? '+' : '-') + data[i].sum
                });
            }
        }
        $scope.history = HistoryData.list[$routeParams.userId]
    });

    $scope.remind = function () {
        $http.post('/v1/notify/'+$routeParams.userId).success(function (data, status) {
            if (status == 204) {
                alert('E-mail сообщение отправлено успешно');
            } else {
                alert('При отправке e-mail сообщения произошла ошибка');
            }
        });
    };
});

app.controller("ProfileController", function($scope, $http, $routeParams, $location, User) {
    if (!User.loggedIn) {
        User.login();
    }

    $scope.user = {
        id: User.id,
        email: User.email,
        nickname: User.nickname
    };
    $scope.$on('loginSuccess', function() {
        $scope.user = {
            id: User.id,
            email: User.email,
            nickname: User.nickname
        };
    });

    $scope.updateProfile = function() {
        if (!$scope.profileForm.$valid) {
            alert('Пожалуйста исправьте ошибки и попробуйте заново');
        } else {
            $http.post('/v1/users/' + $scope.user.id, $scope.user).success(function (data, status) {
                if (status == 200) {
                    User.id = data.id;
                    User.nickname = data.nickname;
                    User.email = data.email;
                    $location.path('/');
                } else {
                    alert('error: '. data);
                }
            });
        }
    };
});
