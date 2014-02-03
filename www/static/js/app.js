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
        when('/login', {
            templateUrl: 'partials/login.html',
            controller: 'LoginController'
        }).
        when('/add', {
            templateUrl: 'partials/add.html',
            controller: 'AddDebtController'
        }).
        when('/profile', {
            templateUrl: 'partials/profile.html',
            controller: 'ProfileController'
        }).
        when('/changePassword/:token', {
            templateUrl: 'partials/changePassword.html',
            controller: 'ChangePasswordController'
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
        currency: 'р.',
        ajaxSpinnerTimeout: 2000
    }
});

app.factory('HistoryData', function() {
    var historyData = {
        list: {},
        loadingState: false
    };

    historyData.clearData = function() {
        this.list = {};
        this.loadingState = false;
    };

    return historyData;
});

app.factory('Users', function($http, $timeout, Settings) {
    var users = {
        list: {},
        loadingState: false
    };

    users.refreshData = function() {
        var loadingPromise = $timeout(function() {users.loadingState = true;}, Settings.ajaxSpinnerTimeout);
        $http.get('/v1/users.json').success(function (data) {
            for (var i in data) {
                if (data.hasOwnProperty(i)) {
                    if (data[i].nickname != '') {
                        data[i].title = data[i].nickname + ' <' + data[i].email +'>';
                        data[i].shortTitle = data[i].nickname;
                    } else {
                        data[i].title = data[i].email;
                        data[i].shortTitle = data[i].email;
                    }
                }
            }
            users.list = data;
//        }).error(function () {
//            User.register();
        }).then(function () {
            $timeout.cancel(loadingPromise);
            users.loadingState = false;
        });
    };

    users.clearData = function() {
        this.list = {};
    };

    return users;
});

app.factory('User', function ($http, $rootScope, $location, $timeout, Users, HistoryData, Settings) {
    var user = {};

    function nullUser() {
        user.loggedIn = false;
        user.id = null;
        user.nickname = null;
        user.email = null;
        user.oweYou = {};
        user.youOwe = {};
        user.even = {};
        user.loadingState = false;
        user.loginState = false;
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

    user.emptyEven = function() {
        return Object.keys(this.even).length == 0;
    };

    user.mainPage = function() {
        $location.path('/');
    };

    user.registerPage = function() {
        $location.path('/register');
    };

    user.refreshSummary = function() {
        var loadingPromise = $timeout(function() {user.loadingState = true;}, Settings.ajaxSpinnerTimeout);
        $http.get('/v1/debts/summary.json').success(function (data) {
            user.oweYou = data.youGave;
            user.youOwe = data.youTook;
            user.even = data.even;
//        }).error(function () {
//            nullSession();
//            user.login();
        }).then(function() {
            $timeout.cancel(loadingPromise);
            user.loadingState = false;
        });
    };

    user.loginPage = function () {
        $location.path('/login');
    };

    user.loginFromCookie = function() {
        var loadingPromise = $timeout(function() {user.loginState = true;}, Settings.ajaxSpinnerTimeout);
        $http.post('/v1/login').success(function (data) {
            user.id = data.id;
            user.nickname = data.nickname;
            user.email = data.email;
            user.loggedIn = true;
            user.refreshSummary();
            Users.refreshData();
            $rootScope.$broadcast('loginSuccess');
        }).error(function () {
            $rootScope.$broadcast('loginFail');
            user.loginState = false;
        }).then(function() {
            $timeout.cancel(loadingPromise);
            user.loginState = false;
        });
    };

    user.logout = function () {
        nullSession();

        $http.post('/v1/logout').success(function () {
            $location.path('/login'); //todo: check following error function
//        }).error(function () {
//            $location.path('/register');
        });
    };

    user.loginFromCookie();

    return user;
});

/****************
 * CONTROLLERS
 */
app.controller("AppController", function ($scope, $window, Users, User, Settings) {
    $scope.settings = Settings;
    $scope.user = User;
    $scope.users = Users;

    if (!User.loggedIn) {
        User.loginFromCookie();
        $scope.$on('loginFail', function() {
            User.loginPage();
        });
    } else {
        User.refreshSummary();
        Users.refreshData();
    }
});

app.controller("RegisterController", function ($scope, $http, User) {
    if (User.loggedIn) {
        User.mainPage();
    }

    $scope.userModel = User;

    $scope.register = function () {
        if (!$scope.registerForm.$valid) {
            alert('Пожалуйста исправьте ошибки и попробуйте зарегистрироваться заново');
        } else {
            $http.post('v1/users', $scope.user).success(function (data, status) {
                if (status == 201) {
                    alert('Теперь Вы можете войти под своими реквизитами');
                    User.loginPage();
                } else {
                    alert('error: '. data);
                }
            });
        }
    };

    $scope.$on('loginSuccess', function() {
        User.mainPage();
    });
});

app.controller("LoginController", function ($scope, $http, $timeout, User, Users, Settings) {
    if (User.loggedIn) {
        User.mainPage();
    }

    $scope.userModel = User;

    $scope.login = function () {
        if (!$scope.loginForm.$valid) {
            alert('Пожалуйста исправьте ошибки и попробуйте войти заново');
        } else {
            var loadingPromise = $timeout(function() {User.loginState = true;}, Settings.ajaxSpinnerTimeout);
            $http.post('/v1/login', $scope.user).success(function (data) {
                User.id = data.id;
                User.nickname = data.nickname;
                User.email = data.email;
                User.loggedIn = true;
                User.refreshSummary();
                Users.refreshData();
                User.mainPage();
            }).error(function (data, status) {
                User.loginState = false;
                if (status == 401) {
                    alert('Не удалось войти');
                }
            }).then(function() {
                $timeout.cancel(loadingPromise);
                User.loginState = false;
            });
        }
    };
});

app.controller("AddDebtController", function($scope, $http, Settings, User, Users) {
    if (!User.loggedIn) {
        User.loginFromCookie();
        $scope.$on('loginFail', function() {
            User.loginPage();
        });
    }

    $scope.settings = Settings;
    $scope.users = Users;

    $scope.addDebt = function () {
        if (!$scope.AddDebtForm.$valid) {
            alert('Пожалуйста исправьте ошибки и попробуйте заново');
        } else {
            $http.post('/v1/debts', $scope.newDebt).success(function (data, status) {
                if (status == 201) {
                    User.mainPage();
                } else {
                    alert('error: '. data);
                }
            });
        }
    };
});

app.controller("HistoryController", function($scope, $http, $routeParams, $timeout, Settings, User, Users, HistoryData) {
    if (!User.loggedIn) {
        User.loginFromCookie();
        $scope.$on('loginSuccess', function() {
            init();
        });
        $scope.$on('loginFail', function() {
            User.loginPage();
        });
    } else {
        init();
    }

    function init() {
        var loadingPromise = $timeout(function() {HistoryData.loadingState = true;}, Settings.ajaxSpinnerTimeout);
        $http.get('/v1/debts/history/'+$routeParams.userId+'.json').success(function (data) {
            HistoryData.list[$routeParams.userId] = [];
            for (var i in data) {
                if (data.hasOwnProperty(i)) {
                    HistoryData.list[$routeParams.userId].push({
                        direction: data[i].sourceUserId == $routeParams.userId ? 'gave' : 'took',
                        date: new Date(data[i].createdDate*1000),
                        description: data[i].description,
                        sum: (data[i].sourceUserId == $routeParams.userId ? '+' : '-') + data[i].sum
                    });
                }
            }
            $scope.history = HistoryData.list[$routeParams.userId]
        }).then(function() {
            $timeout.cancel(loadingPromise);
            HistoryData.loadingState = false;
        });
    }

    $scope.historyObject = HistoryData;
    $scope.settings = Settings;
    $scope.user = User;
    $scope.users = Users;
    $scope.userId = $routeParams.userId;
    if (HistoryData.list[$routeParams.userId] != undefined) {
        $scope.history = HistoryData.list[$routeParams.userId]
    } else {
        $scope.history = [];
    }

    $scope.remind = function () {
        $http.post('/v1/notify/'+$routeParams.userId).success(function (data, status) {
            if (status == 204) {
                alert('E-mail сообщение отправлено успешно');
            } else {
                alert('При отправке e-mail сообщения произошла ошибка');
            }
        });
    };

    $scope.displayReminderButton = function() {
        return typeof(User.oweYou) !== 'undefined' && typeof(User.oweYou[ $routeParams.userId ]) !== 'undefined';
    }
});

app.controller("ProfileController", function($scope, $http, User) {
    if (!User.loggedIn) {
        User.loginFromCookie();
        $scope.$on('loginSuccess', function() {
            init();
        });
        $scope.$on('loginFail', function() {
            User.loginPage();
        });
    } else {
        init();
    }

    function init() {
        $scope.user = {
            id: User.id,
            email: User.email,
            nickname: User.nickname
        };
    }

    $scope.userModel = User;

    $scope.updateProfile = function() {
        if (!$scope.profileForm.$valid) {
            alert('Пожалуйста исправьте ошибки и попробуйте заново');
        } else {
            $http.post('/v1/users/' + $scope.user.id, $scope.user).success(function (data, status) {
                if (status == 200) {
                    User.id = data.id;
                    User.nickname = data.nickname;
                    User.email = data.email;
                    User.mainPage();
                } else {
                    alert('error: '. data);
                }
            });
        }
    };
});

app.controller("ChangePasswordController", function($scope, $http, $routeParams, User) {
    if (User.loggedIn) {
        User.mainPage();
    } else {
        init();
    }

    function init() {
        $http.get('/v1/decodeToken/' + $routeParams.token).success(function (data, status) {
            if (status == 200) {
                $scope.user = {};
                $scope.user.id = data.id;
                $scope.user.nickname = data.nickname;
                $scope.user.email = data.email;
            } else {
                alert('error: '. data);
            }
        });
    }

    $scope.token = $routeParams.token;

    $scope.updateProfile = function() {
        if (!$scope.profileForm.$valid) {
            alert('Пожалуйста исправьте ошибки и попробуйте заново');
        } else {
            $http.post('/v1/updateProfile/' + $routeParams.token, $scope.user).success(function (data, status) {
                if (status == 200) {
                    User.mainPage();
                } else {
                    alert('error: '. data);
                }
            });
        }
    };
});
