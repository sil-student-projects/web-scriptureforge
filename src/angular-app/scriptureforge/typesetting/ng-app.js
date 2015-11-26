'use strict';

angular.module('typesetting',
    [
          'ui.router',
          'bellows.filters',
          'typesetting.composition',
          'typesetting.services',
          'typesetting.projectSetupLayout',
          'typesetting.projectSetupAssets',
          'typesetting.renderList',
    ])
  .run(['$rootScope', '$state', '$stateParams', function($rootScope,   $state,   $stateParams) {
    // Add $state and $stateParams to the $rootScope so that we can access them from any scope.
    // <li ng-class="{ active: $state.includes('contacts.list') }"> will set the <li>
    // to active whenever 'contacts.list' or one of its decendents is active.
    $rootScope.$state = $state;
    $rootScope.$stateParams = $stateParams;
  },])
  .config(['$stateProvider', '$urlRouterProvider', function($stateProvider, $urlRouterProvider) {

    $urlRouterProvider.otherwise('/composition');

    $stateProvider
        .state('home', {
          url: '/composition',
          templateUrl: '/angular-app/scriptureforge/typesetting/views/composition.html',
        })
        .state('projectSetupAssets', {
          url: '/assets',
          templateUrl: '/angular-app/scriptureforge/typesetting/views/projectSetup.assets.html',
        })
        .state('projectSetupLayout', {
          url: '/layout',
          templateUrl: '/angular-app/scriptureforge/typesetting/views/projectSetup.layout.html',
        })
        .state('composition', {
          url: '/composition',
          templateUrl: '/angular-app/scriptureforge/typesetting/views/composition.html',
        })
        .state('review', {
          url: '/review',
          templateUrl: '/angular-app/scriptureforge/typesetting/views/review.html',
        })
        .state('render', {
          url: '/render',
          templateUrl: '/angular-app/scriptureforge/typesetting/views/renderList.html',
        });

  },])
  .controller('MainCtrl', ['$scope', function($scope) {
    $scope.selectedBtn = 0;



    $scope.settingsButton = {
      isopen: false,
    };

  },]);
