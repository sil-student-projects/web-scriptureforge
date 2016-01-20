// controller for setupProjectLayout
'use strict';

angular.module('typesetting.projectSetupLayout', ['jsonRpc', 'ui.bootstrap', 'bellows.services', 'ngAnimate', 'palaso.ui.notice', 'typesetting.layoutServices'])

    .controller('projectSetupLayoutCtrl', ['$scope', '$state', 'typesettingLayoutService', 'sessionService', 'modalService', 'silNoticeService', 'templateSaveService', 'templateLoadService', '$interval', '$rootScope',
      function($scope, $state, layoutService, sessionService, modal, notice, templateSaveObject, templateLoadObject, $interval, $rootScope) {
        var vm = $scope;

        // default settings
        /*
         vm.conf = {
         // margins
         insideMargin: 10,
         outsideMargin: 10,
         topMargin: 15,
         bottomMargin: 10,

         // columns
         bodyColumnsTwo: true,
         titleColumnsTwo: false,
         introColumnsTwo: false,
         columnGutterRule: false,
         columnShift: 5,
         columnGutterRuleSkip: 0,
         columnGutterFactor: 15,

         // header
         headerPosition: 5,
         useRunningHeader: true,
         useRunningHeaderRule: false,
         runningHeaderRulePosition: 4,

         runningHeaderTitleLeft: "empty",
         runningHeaderTitleCenter: "empty",
         runningHeaderTitleRight: "empty",

         runningHeaderEvenLeft: "firstref",
         runningHeaderEvenCenter: "pagenumber",
         runningHeaderEvenRight: "empty",

         runningHeaderOddLeft: "empty",
         runningHeaderOddCenter: "pagenumber",
         runningHeaderOddRight: "lastref",

         omitChapterNumberRH: false,
         showVerseReferences: true,
         omitBookReference: false,



         // footer
         footerPosition: 5,
         useRunningFooter: false,

         runningFooterEvenLeft: "empty",
         runningFooterEvenCenter: "empty",
         runningFooterEvenRight: "empty",

         runningFooterOddLeft: "empty",
         runningFooterOddCenter: "empty",
         runningFooterOddRight: "empty",

         runningFooterTitleLeft: "empty",
         runningFooterTitleCenter: "empty",
         runningFooterTitleRight: "empty",


         //footnotes
         useFootnoteRule: true,
         pageResetCallersFootnotes: false,

         omitCallerInFootnotes: false,
         //  omitCallerInFootnotes (string ‘f’)

         useSpecialCallerFootnotes: false,
         paragraphedFootnotes: true,
         useNumericCallersFootnotes: false,
         //specialCallerFootnotes: "\krn0.2em *\kern0.4em",


         // cross references
         useSpecialCallerCrossrefs: false,
         specialCallerCrossrefs: "\\kern0.2em *\\kern0.4em",
         useAutoCallerCrossrefs: true,
         omitCallerInCrossrefs: false,
         paragraphedCrossrefs: true,
         useNumericCallersCrossrefs: false,

         //'editing' features
         useBackground: false,
         backgroundComponents: "watermark",
         watermarkText: "DRAFT",
         useDiagnostic: false,
         diagnosticComponents: "",

         // print options
         pageSizeCode: "custom", // this shouldn't be visible in the UI
         pageHeight: 210,
         pageWidth: 148,
         printerPageSizeCode: "A4",

         useDocInfo: false,
         docInfoText: "",

         // body text
         bodyTextLeading: 12,
         bodyFontSize: 10,
         rightToLeft: false,
         justifyParagraphs: true,


         //Misc

         //advanced
         extraRightMargin: 0,
         chapterVerseSeperator: ":",

         };
         */

        //conf object for the settings that are loaded from the database. Items that are not meant to be in the database should
        // not be put in the conf object.
        vm.conf = {};

        //Default pageSizeCode in the database is 'custom' always, in the UI it is 'A5'. Default page sizes are handeled in the
        // front end and just edit the pageHeight and pageWidth variables sent to the database.
        vm.pageSizeCode = "A5";
        $scope.setPageSize = function setPageSize(pageCode) {
          console.log("TEST pageSize:" + pageCode, "end");

          switch (pageCode) {
            case 'A5':
              vm.conf.pageHeight = 210;
              vm.conf.pageWidth = 148;
              break;
            case 'A4':
              vm.conf.pageHeight = 298;
              vm.conf.pageWidth = 210;
              break;
            case 'US Letter':
              vm.conf.pageHeight = 279.4;
              vm.conf.pageWidth = 215.9;
              break;
            case 'custom':
              break;

          }
        }

        // the diagnosticComponents and backgroundComponents need a special function to set them in the conf object because they
        // are strings in the conf object but they are checkboxes in the UI. These next two functions turn the checkboxes into a string that is saved to the conf file.
        vm.diagnostics = {
          leading: false,
        };
        vm.diagnosticsComponentsUpdate = function() {
          var diags = [];
          var diag;
          for (diag in vm.diagnostics) {
            if (vm.diagnostics[diag]) {
              diags.push(diag);
            }
          };
          vm.conf.diagnosticComponents = diags.join(", ");
        };

        vm.components = {
          watermark: true,
        };
        vm.backgroundComponentsUpdate = function() {
          var comps = [];
          var comp;
          for (comp in vm.components) {
            if (vm.components[comp]) {
              comps.push(comp);
            }
          };
          vm.conf.backgroundComponents = comps.join(", ");
        };

        // Header and footer options for the ng-options that create the option dropdowns.
        vm.headerOptions = ["empty", "bookname", "rangeref", "firstref", "lastref", "pagenumber"];
        vm.footerOptions = vm.headerOptions;

        // Function to keep two variables mutually Exclusive
        $scope.mutuallyExclusive= function mutuallyExclusive(name){
          switch (name){
            case "background":
              if (vm.conf.useBackground == true) {
                vm.conf.useDiagnostic = false;
              };
              break;
            case "diagnostic":
              if (vm.conf.useDiagnostic == true) {
                vm.conf.useBackground = false;
              };
              break;
          }
        };

        $scope.saveButtonClick = function saveButtonClick() {
          console.log("TEST");
          saveLayoutSettings();
        };

        var getPageDto = function getPageDto() {
          layoutService.getPageDto(function(result) {
            vm.conf = result.data.layout;
          });
        };

        getPageDto();

        vm.width = 300;
        vm.height = 400;
        vm.css = {
          pagesContainer: {
            width: vm.width * 2 + 25,
          },
          page: {
            height: vm.height,
            width: vm.width,
          },
          leftMargins: {
            height: vm.height - vm.topMargin - vm.bottomMargin + "px",
            width: vm.width - vm.insideMargin - vm.outsideMargin + "px",
            marginTop: vm.topMargin + "px",
            marginRight: vm.insideMargin + "px",
          },
          rightMargins: {
            height: vm.height - vm.topMargin - vm.bottomMargin + "px",
            width: vm.width - vm.insideMargin - vm.outsideMargin + "px",
            marginTop: vm.topMargin + "px",
            marginLeft: vm.insideMargin + "px",
          },
        };

        // variable watchers
        function makeMarginWatch(size, margin, opposite, cssMargin, cssOpposite, mirror) {
          $scope.$watch("conf."+margin+"Margin", function() {
            vm[margin+"MarginMax"] = vm[size] - vm.conf[opposite+"Margin"];
            vm.css.leftMargins[size] = vm[size] - vm.conf[margin+"Margin"] - vm.conf[opposite+"Margin"] + "px";
            vm.css.leftMargins[cssOpposite] = vm.conf[margin+"Margin"] + "px";
            vm.css.rightMargins[size] = vm.css.leftMargins[size];
            if (mirror) {
              vm.css.rightMargins[cssMargin] = vm.css.leftMargins[cssOpposite];
            } else {
              vm.css.rightMargins[cssOpposite] = vm.css.leftMargins[cssOpposite];
            }
            if (vm[margin+"MarginMax"] < vm.conf[margin+"Margin"]) {
              vm.conf[margin+"Margin"] = vm[margin+"MarginMax"];
            }
            if (vm.conf[margin+"Margin"] < 0) {
              vm.conf[margin+"Margin"] = 0;
            }
          });
        }
        makeMarginWatch("width", "inside", "outside", "marginLeft", "marginRight", true);
        makeMarginWatch("width", "outside", "inside", "marginRight", "marginLeft", true);
        makeMarginWatch("height", "top", "bottom", "marginBottom", "marginTop");
        makeMarginWatch("height", "bottom", "top", "marginTop", "marginBottom");


        var saving = false;
        var saved = false;

        $scope.saveNotice = function saveNotice() {
          if (saving) return 'Saving';
          if (saved) return 'Saved';
          return '';
        };

        function saveLayoutSettings() {
          cancelAutoSaveTimer();
          saving = true;
          console.log('saveLayoutSettings');

          layoutService.save(vm.conf, function(result){
            saved = true;
            $scope.layoutForm.$setPristine();
            saving = false;
          });

          // TODO always clear 'saving' irrespective of successful save. IJH 2015-01
        };

        var autoSaveTimer;
        function startAutoSaveTimer() {
          if (angular.isDefined(autoSaveTimer)) {
            return;
          }
          autoSaveTimer = $interval(function() {
            saveLayoutSettings();
          }, 5000, 1);
        };
        function cancelAutoSaveTimer() {
          if (angular.isDefined(autoSaveTimer)) {
            $interval.cancel(autoSaveTimer);
            autoSaveTimer = undefined;
          }
        };

        $scope.$on('$destroy', function() {
          cancelAutoSaveTimer();
          saveLayoutSettings();
        });

        $scope.$on('$locationChangeStart', function(event, next, current) {
          cancelAutoSaveTimer();
          saveLayoutSettings();
        });

        $scope.$watch('layoutForm.$dirty', function(newValue) {
          if (newValue != undefined && newValue) {
            cancelAutoSaveTimer();
            saving = true;
            startAutoSaveTimer();
          }
        });

        vm.loadData = function(newConf) {
          $scope.newConf = newConf;
          vm.conf = $scope.newConf;
        }

        $rootScope.$on('handleLoadBroadcast', function() {
          $scope.loadTest = templateLoadObject.templateName;
          vm.loadData(templateLoadObject.newConf);
          //console.log(testData);
          console.log(vm);
          console.log("loaded");
        });

        $rootScope.$on('handleSaveBroadcast', function() {
          //console.log("handledSaveBroadcast");
          templateSaveObject.vm = vm;
        });
      }])
    .controller('templateCtrl', ['$scope', '$state', 'typesettingSetupService',  'sessionService', 'modalService', 'silNoticeService', 'templateSaveService', 'templateLoadService', '$location',
      function($scope, $state, typesettingSetupApi, sessionService, modal, notice, templateSaveObject, templateLoadObject, $location) {
        $scope.loadTemplateCollapsed = true;
        $scope.saveTemplateCollapsed = true;
        $scope.disableAddButton = true;
        $scope.saveTemplateName = "";
        $scope.loadTemplateName = "";

        $scope.uncollapseDivs = function(div){
          if (div ==="load"){
            if (!$scope.loadTemplateCollapsed){
              $scope.loadTemplateCollapsed = true;
            }else{
              $scope.loadTemplateCollapsed = false;
              $scope.saveTemplateCollapsed = true;
            }
          }
          if (div==="save"){
            if (!$scope.saveTemplateCollapsed){
              $scope.saveTemplateCollapsed = true;
            }else{
              $scope.saveTemplateCollapsed = false;
              $scope.loadTemplateCollapsed = true;
            }
          }
        };

        $scope.handleSaveClick = function() {
          templateSaveObject.prepForBroadcast($scope.saveTemplateName);
        };

        $scope.handleLoadClick = function() {
          templateLoadObject.prepForBroadcast($scope.loadTemplateName);
        };
      }]);
