'use strict';

angular.module('typesetting.typeset',
        [ 'jsonRpc', 'ui.bootstrap', 'bellows.services', 'ngAnimate',
            'typesetting.compositionServices', 'typesetting.renderedPageServices',
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
              var paragraphProperties = {
              // c1v1: {growthFactor:3},
              };
              var illustrationProperties = {};
              $scope.listOfBooks = [];
              var currentVerse;
//              for (var i = 0; i < 31; i++) {
//                $scope.pages.push("red");
//              }
              
              
              $scope.renderRapuma = function() {
                compositionService.renderBook($scope.bookID,
                    function(result) {
                      $scope.renderInfo = result.data
                    });
              };
              var getBookHTML = function getBookHTML() {
                compositionService.getBookHTML($scope.bookID,
                    function(result) {
                      $scope.bookHTML = result.data;
                    });
              };
              var getParagraphProperties = function getParagraphProperties() {
                compositionService.getParagraphProperties($scope.bookID, function(result) {
                  paragraphProperties = result.data;
                });
              };
              var setParagraphProperties = function setParagraphProperties() {
                compositionService.setParagraphProperties(
                    $scope.bookID, paragraphProperties,
                    function(result) {
                      // nothing todo?
                    });
              };
              var getIllustrationProperties = function getIllustrationProperties() {
                compositionService.getIllustrationProperties(function(result) {
                  illustrationProperties = result.data;
                });
              };
              var setIllustrationProperties = function setIllustrationProperties() {
                compositionService.setIllustrationProperties(illustrationProperties,
                    function(result) {
                      // nothing todo?
                    });
              };
              var getRenderedPageForBook = function getRenderedPageForBook(
                  pageNum) {
                compositionService.getRenderedPageForBook(
                    $scope.bookID,
                    pageNum,
                    function(result) {
                      if (pageNum == 0 || pageNum > $scope.pages.length)
                        result.data = "";
                      if (pageNum % 2 == 0)
                        $scope.renderedImageLeft = result.data;
                      else
                        $scope.renderedImageRight = result.data;
                    });
              };
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
                compositionService.getPageDto(function getPageDto(result){
                  $scope.listOfBooks = result.data.books;
                  $scope.bookID = result.data.bookID;
                  $scope.bookHTML = result.data.bookHTML;
                  // TODO Fix this in php side
                  paragraphProperties = result.data.paragraphProperties;
                  if (paragraphProperties.length == 0) {
                    paragraphProperties = {};
                  }
                  illustrationProperties = result.data.illustrationProperties;
                  if (illustrationProperties.length == 0) {
                    illustrationProperties = {};
                  }
                  //console.log(paragraphProperties);
                  $scope.pages = result.data.pages;
                  $scope.selectedPage = 1;
                });
              };
              var getBookDto = function getBookDto(){
                initializeBook();
                compositionService.getBookDto($scope.bookID, function getBookDto(result){
                  $scope.bookHTML = result.data.bookHTML;
                  // TODO Fix this in php side
                  paragraphProperties = result.data.paragraphProperties;
                  if (paragraphProperties.length == 0) {
                    paragraphProperties = {};
                  }
                  illustrationProperties = result.data.illustrationProperties;
                  if (illustrationProperties.length == 0) {
                    illustrationProperties = {};
                  }
                  //console.log(paragraphProperties);
                  $scope.pages = result.data.pages;
                  $scope.selectedPage = 1;
                });
              };
              var initializeBook = function(){
                $scope.bookHTML = "<b>loading</b>";
                $scope.currentImage = "";
                currentVerse = "";
                $scope.verse = "";
                $scope.chapter = "";
                $scope.pages = [];
                $scope.renderedImageLeft = "";
                $scope.renderedImageRight = "";
                $scope.selectedPage = 1;
              };
              
              
              $scope.bookChanged = function bookChanged(){
                setParagraphProperties();
                setIllustrationProperties();
                getBookDto();
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
              $scope.illustrationSave = function illustrationChange() {
                illustrationProperties[$scope.currentImage].Location = $scope.location;
                illustrationProperties[$scope.currentImage].width = $scope.imageWidth;
                illustrationProperties[$scope.currentImage].scale = $scope.scale;
                illustrationProperties[$scope.currentImage].caption = $scope.caption;
                illustrationProperties[$scope.currentImage].useCaption = $scope.useCaption;
                illustrationProperties[$scope.currentImage].useIllustration = $scope.illustration;
              };
              
              var illustrationClicked = function() {
                $scope.verse = "";
                $scope.currentImage = $scope.paragraphNode.id.split("-")[1];
                if(!illustrationProperties[$scope.currentImage]) illustrationProperties[$scope.currentImage] = {
                  location: "",
                  width: 100,
                  scale: 1,
                  caption: "",
                  useCaption: false,
                  useIllustration:true
                }
                $scope.location = illustrationProperties[$scope.currentImage].location;
                $scope.imageWidth = parseInt(illustrationProperties[$scope.currentImage].width);
                $scope.scale = illustrationProperties[$scope.currentImage].scale;
                $scope.caption = illustrationProperties[$scope.currentImage].caption;
                $scope.useCaption = illustrationProperties[$scope.currentImage].useCaption;
                $scope.useIllustration = illustrationProperties[$scope.currentImage].useIllustration;
              };
              
              $scope.lock = function lock(){
                $scope.pages[$scope.selectedPage] = "green";
                setPageStatus();
              };
              $scope.paragraphChanged = function paragraphChanged(){
                paragraphProperties[currentVerse].growthFactor = $scope.paragraphGrowthFactor;
              };
              
              $scope.$watch('selectedPage', function() {
                $scope.selectedPage = parseInt($scope.selectedPage);
                if ($scope.selectedPage <= 0)
                  $scope.selectedPage = 1;
                if(!$scope.pages)return;
                if ($scope.selectedPage > $scope.pages.length)
                  $scope.selectedPage = $scope.pages.length;
                $scope.pageInput = $scope.selectedPage;
                if ($scope.selectedPage % 2 == 0) {
                  getRenderedPageForBook($scope.selectedPage);
                  getRenderedPageForBook($scope.selectedPage + 1);
                } else {
                  getRenderedPageForBook($scope.selectedPage - 1);
                  getRenderedPageForBook($scope.selectedPage);
                }
              });
              $scope.$watch('paragraphNode',function() {
                if (!$scope.paragraphNode)
                  return;
                if ($scope.paragraphNode.className == "illustration-placeholder")
                  illustrationClicked();
                else {
                  $scope.currentImage='';
                  currentVerse = $scope.paragraphNode.id;
                  var temp = currentVerse.replace("c", "").split("v");
                  $scope.verse = temp[1];
                  $scope.chapter = temp[0];
                  if(!paragraphProperties[currentVerse]) paragraphProperties[currentVerse] = {growthFactor : 0};
                  $scope.paragraphGrowthFactor = paragraphProperties[currentVerse].growthFactor;
                }
              });
              var getRenderedPageDto = function getRenderedPageDto(){
                initializeBook();
                renderedPageService.getRenderedPageDto(function getRenderedPageDto(result){
                  $scope.pages = result.data.pages;
                  $scope.renderedPage = result.data.renderedPage;
                  $scope.selectedPage = 1;
                  $scope.comments = result.data.comments;
                });
              };
              getRenderedPageDto();
              getPageDto();
              
            } ]);
