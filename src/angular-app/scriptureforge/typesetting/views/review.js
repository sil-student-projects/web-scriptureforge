'use strict';

angular.module('typesetting.review',
    [ 'jsonRpc', 'ui.bootstrap', 'bellows.services', 'ngAnimate',
        'typesetting.renderedPageServices',
        'composition.selection' ])

    .controller(
    'reviewCtrl',
    [
        '$scope',
        '$state',
        'typesettingSetupService',
        'typesettingRenderedPageService',
        'sessionService',
        'modalService',
        'silNoticeService',
        function($scope, $state, typesettingSetupApi, renderedPageService) {

            $scope.listOfBooks = [];
            var currentVerse;
//              for (var i = 0; i < 31; i++) {
//                $scope.pages.push("red");
//              }
            var initializeBook = function(){
                $scope.currentImage = "";
                currentVerse = "";
                $scope.pages = [];
                $scope.selectedPage = 1;
            };

            var getPageStatus = function getPageStatus(){
                renderedPageService.getPageStatus($scope.bookID, function(result){
                    $scope.pages = result.data;
                });
            };
            var setPageStatus = function setPageStatus(){
                renderedPageService.setPageStatus($scope.bookID, $scope.pages, function(result){
                    //nothing todo?
                });
            };
            var getRenderedPageDto = function getRenderedPageDto(){
                initializeBook();
                renderedPageService.getRenderedPageDto(function getRenderedPageDto(result){
                    $scope.pages = result.data.pages;
                    $scope.renderedPage = result.data.renderedPage;
                    $scope.selectedPage = 1;
                    $scope.comments = result.data.comments;
                });
            };

            $scope.decreasePage = function() {
                $scope.selectedPage = Math.max(1,
                    $scope.selectedPage - 1);
            };
            $scope.increasePage = function() {
                $scope.selectedPage = Math.min($scope.pages.length,
                    parseInt($scope.selectedPage) + 1);
            };


            $scope.updatePage = function() {
                $scope.selectedPage = $scope.pageInput;
            };
            $scope.save = function save() {
                setParagraphProperties();
                setIllustrationProperties();
                $scope.pages[$scope.selectedPage] = "orange";
                setPageStatus();
            };

            $scope.$watch('selectedPage', function() {
                $scope.selectedPage = parseInt($scope.selectedPage);
                if ($scope.selectedPage <= 0)
                    $scope.selectedPage = 1;
                if(!$scope.pages)return;
                if ($scope.selectedPage > $scope.pages.length)
                    $scope.selectedPage = $scope.pages.length;
                $scope.pageInput = $scope.selectedPage;

                    //getRenderedPage($scope.selectedPage);

            });

            getRenderedPageDto();

        } ]);
        
        function FrmController($scope) {
                $scope.comment = [];
                $scope.btn_add = function() {
                    if($scope.txtcomment !=''){
                    $scope.comment.push($scope.txtcomment);
                    $scope.txtcomment = "";
                    }
                }

                $scope.remItem = function($index) {
                    $scope.comment.splice($index, 1);
                }
            }
        
