'use strict';

angular.module('typesetting.composition',
    [ 'jsonRpc', 'ui.bootstrap', 'bellows.services', 'ngAnimate',
        'typesetting.compositionServices',"typesetting.renderedPageServices",
        'composition.selection' ])

    .controller(
    'compositionCtrl',
    [
        '$scope',
        '$state',
        'typesettingSetupService',
        'typesettingCompositionService',
        'typesettingRenderedPageService',
        'sessionService',
        'modalService',
        'silNoticeService',
        function($scope, $state, typesettingSetupApi,
                 compositionService, renderedPageService) {

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
            //var getRenderedPage = function getRenderedPage(pageNum) {
            //    renderedPageService.getRenderedPage(
            //        $scope.bookID,
            //        pageNum,
            //        function(result) {
            //                $scope.renderedPage = result.data;
            //
            //        });
            //};
            var getPageStatus = function getPageStatus(){
                compositionService.getPageStatus($scope.bookID, function(result){
                    $scope.pages = result.data;
                });
            };
            var setPageStatus = function setPageStatus(){
                compositionService.setPageStatus($scope.bookID, $scope.pages, function(result){
                    //nothing todo?
                });
            };
            var getPageDto = function getPageDto(){
                initializeBook();
                renderedPageService.getPageDto(function getPageDto(result){
                    $scope.pages = result.data.pages;
                    $scope.renderedPage = result.data.renderedPages;


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

            getPageDto();

        } ]);
