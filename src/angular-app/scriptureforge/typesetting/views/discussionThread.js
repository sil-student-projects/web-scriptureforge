'use strict';

angular.module('typesetting.discussionThread', ['ui.bootstrap', 'bellows.services', 'ngAnimate', 'palaso.ui.notice', 'typesetting.discussionServices'])
    // controller for discussionThread
    .controller('discussionThreadCtrl', ['$scope', '$state', 'typesettingDiscussionService', '$location', 'sessionService', 'modalService', 'silNoticeService',
      function($scope, $state, discussionService, $location, sessionService, modal, notice) {

        // path is "/discussion/:threadId"
        var threadId = $location.path().split('/')[2];

        // is currentThreadIndex initialised?
        if ($scope.discussion.currentThreadIndex < 0) {
          discussionService.getPageDto(function(result) {
            $scope.discussion.threads = result.data.threads;
            for (var i = 0; i < $scope.discussion.threads.length; i++) {
              if ($scope.discussion.threads[i].id === threadId) {
                $scope.discussion.currentThreadIndex = i;
                break;
              }
            }

            // thread not found in threads list?
            if ($scope.discussion.currentThreadIndex < 0) {
              $state.go('discussion');
            } else {
              $scope.thread = $scope.discussion.threads[$scope.discussion.currentThreadIndex];
            }
          });
        } else {
          $scope.thread = $scope.discussion.threads[$scope.discussion.currentThreadIndex];
        }

        function getPageDto() {
          discussionService.getPageDto(function(result) {
            $scope.discussion.threads = result.data.threads;
            $scope.thread = $scope.discussion.threads[$scope.discussion.currentThreadIndex];
          });
        };

        $scope.currentPost = "";

        $scope.changeTitle = function changeTitle() {
          if ($scope.isManager()) {
            var title = prompt("Please enter a new thread name.", $scope.thread.title);
            if (title != null && title.trim() != "") {
              discussionService.updateThread($scope.thread.id, title, function(result) {
                getPageDto();
                notice.push(notice.SUCCESS, "Title updated.");
              });
            }
          }
        };

        $scope.saveEdit = function saveEdit(post) {
          discussionService.updatePost($scope.thread.id, post.id, post.content, function(result) {
            getPageDto();
            notice.push(notice.SUCCESS, "Post updated.");
          });
        };

        $scope.deletePost = function deletePost(post) {
          var confirmBool = confirm("Are you sure you want to delete this post?");
          if (confirmBool) {
            discussionService.deletePost($scope.thread.id, post.id, function(result) {
              getPageDto();
              notice.push(notice.SUCCESS, "Post deleted.");
            });
          }
        };
        $scope.deleteReply = function deleteReply(post, reply) {
          var confirmBool = confirm("Are you sure you want to delete this reply?");
          if (confirmBool) {
            discussionService.deleteReply($scope.thread.id, post.id, reply.id, function(result) {
              getPageDto();
              notice.push(notice.SUCCESS, "Reply deleted.");
            });
          }
        };
        $scope.createReply = function createReply(post, newReplyContent) {
          discussionService.createReply($scope.thread.id, post.id, newReplyContent, function(result) {
            newReplyContent = "";
            getPageDto();
            notice.push(notice.SUCCESS, "Reply successful.");
          });
        };

        $scope.createPost = function createPost() {
          discussionService.createPost($scope.thread.id, $scope.newPostContent, function(result) {
            $scope.newPostContent = "";
            getPageDto();
            notice.push(notice.SUCCESS, "Post successful.");
          });
        };

        $scope.changeStatus = function changeStatus() {
          if ($scope.isManager()) {
            alert("Change Status");
          }
          // TODO: Change status
        };

        $scope.isManager = function() {
          return true;
        };

      }]);
