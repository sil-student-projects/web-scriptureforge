'use strict';

angular.module('typesetting.discussionList', ['ui.bootstrap', 'bellows.services', 'ngAnimate', 'palaso.ui.notice', 'typesetting.discussionServices'])
// controller for discussionList
.controller('discussionListCtrl', ['$scope', '$state', 'typesettingDiscussionService', 'sessionService', 'modalService', 'silNoticeService',
function($scope, $state, discussionService, sessionService, modal, notice) {
  $scope.newThread = {
      'title': '',
      'associatedItem': ''
  }
  $scope.newThreadCollapsed = true;

  discussionService.getPageDto(function(result) {
    $scope.discussion.threads = result.data.threads;
  });

  $scope.deleteThread = function deleteThread(thread, index) {
    var confirmBool = confirm("Are you sure you would like to delete this thread?");
    if (confirmBool) {
      discussionService.deleteThread(thread.id, function(result) {
        
        // ensure additional rows can't be deleted while waiting for the server
        if (result.ok && thread.id === $scope.discussion.threads[index].id) {
          $scope.discussion.threads.splice(index, 1);
          notice.push(notice.SUCCESS, "Thread deleted.");
        }
      });
    }
  };

  $scope.createThread = function createThread() {
    $scope.newThread.status = 'Open';
    $scope.newThread.dateModified = new Date();
    $scope.newThread.author = {'name': 'me'};
    $scope.discussion.threads.unshift($scope.newThread);
    discussionService.createThread($scope.newThread.title, $scope.newThread.associatedItem, function(result) {
      $scope.newThread = {
          'title': '',
          'associatedItem': ''
      };
      notice.push(notice.SUCCESS, "Thread created.");
    });
  };

  $scope.isManager = function() {
    return true;
  };

}]);
