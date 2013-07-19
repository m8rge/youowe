function YouOweApp($scope) {
    $scope.currency = 'р.';
    $scope.oweYou = [
        { email: 'to.merge@gmail.com', totalSum: 420 },
        { email: 'ramm@66.ru', totalSum: 30 }
    ];
    $scope.youOwe = [
        { email: 'to.merge@gmail.com', totalSum: 420 },
        { email: 'ramm@66.ru', totalSum: 30 }
    ];
}