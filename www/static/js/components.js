//angular.directive('focusOn', function($timeout) {
//    return {
//        restrict: "A",
//        link: function(scope, element, attrs) {
//            scope.$watch(attrs.focusOn, function(value) {
//                if(value === true) {
//                    $timeout(function() {
//                        element[0].focus();
//                    });
//                }
//            });
//        }
//    };
//});

angular
    .module('globalErrors', [])
    .config(function($provide, $httpProvider) {
        $httpProvider.interceptors.push(function($timeout, $q) {
            return {
                responseError: function(errorResponse) {
                    if (errorResponse.status != 401) {
                        if (errorResponse.data.error) {
                            alert('Ошибка: ' + errorResponse.data.error);
                        } else {
                            alert('При запросе ресурса (' + errorResponse.config.method + ')' + errorResponse.config.url +' произошла ошибка на сервере с кодом ' + errorResponse.status);
                        }
                    }
                    return $q.reject(errorResponse);
                }
            }
        });
    });
