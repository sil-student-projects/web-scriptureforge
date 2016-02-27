//controller for setupProjectAssets
'use strict';

angular.module('typesetting.Setup', ['jsonRpc', 'ui.bootstrap', 'bellows.services', 'ngAnimate', 'palaso.ui.notice', 'typesetting.services', 'angularFileUpload', 'palaso.ui.mockUpload'])
  .controller('SetupCtrl', ['$scope', '$state', '$upload', 'typesettingAssetService', 'sessionService', 'modalService', 'silNoticeService',
  function($scope, $state, $upload, typesettingAssetService, sessionService, modal, notice) {

    $scope.sections = [
      {
        title:'ParaTExt Texts',
        fileType:'usfm-zip',
        assets:[],
        limit:1,
      },
      {
        title:'Illustrations',
        fileType:'png',
        assets:[],
      },
      /*{'title':'Cover / Maps',
       'fileType':'png',
       'assets':[]},
       {'title':'Fonts',
       'fileType':'ttf',
       'assets':[]},
       {'title':'Front & Back Matter',
       'fileType':'pdf',
       'assets':[]},*/
    ];

    // Get any existing assets from the database on load
    typesettingAssetService.readAssets(function(result) {
      var assets = result.data.entries;
      for (var i in assets) {
        if (assets[i].type == 'usfm-zip') {
          $scope.sections[0].assets.push(assets[i]);
        } else if (assets[i].type == 'png') { // WARNING This will cause problems if there are cover/maps (or other assets) used that are pngs will need another form of identification
          $scope.sections[1].assets.push(assets[i]);
        }
      }
    });

    // For the buttons.
    $scope.newTextCollapsed = true;

    $scope.isCollapsed = function isCollapsed(section, collapsed) {
      if (angular.isDefined(section.limit) && section.assets.length >= section.limit) {
        return true;
      }

      return collapsed;
    };

    $scope.toggleCollapse = function toggleCollapse(section, collapsed) {
      if (section.assets.length >= section.limit) {
        notice.push(notice.INFO, 'Only ' + section.limit + ' ' + section.fileType + ' is allowed for this asset.');
        return true;
      }

      return !collapsed;
    };

    $scope.onFileSelect = function($files, section) {

      // take the first file only
      var file = $files[0];
      if (angular.isDefined(file)) {
        section.tempFile = file; // This is to display the name of the file temporarily during upload
        if (file.size <= sessionService.fileSizeMax()) {
          $upload.upload({
            // upload.php script
            url: '/upload/sf-typesetting/' + section.fileType,

            // headers: {'myHeaderKey': 'myHeaderVal'},
            data: {
              filename: file.name,
            },
            file: file,
          }).progress(function(evt) {
            $scope.progress = parseInt(100.0 * evt.loaded / evt.total);
          }).success(function(data, status, headers, config) {
            if (data.result) {
              $scope.progress = 100.0;
              $scope.uploadResult = 'File uploaded successfully.';
              notice.push(notice.SUCCESS, $scope.uploadResult);
              var asset = data.data;
              asset.name = asset.fileName;
              asset.type = section.fileType;
              asset.uploaded = true;
              delete(asset.fileName);
              section.assets.push(asset);
            } else {
              $scope.progress = 0;
              notice.push(notice.ERROR, data.data.errorMessage);
              if (data.data.errorType == 'UserMessage') {
                $scope.uploadResult = data.data.errorMessage;
              }
            }

            section.tempFile = null;
          });
        } else {
          $scope.progress = 0;
          section.tempFile = null;
          $scope.uploadResult = file.name + ' is too large.';
        }
      }
    };

    $scope.deleteFile = function deleteFile(assets, index) {
      assets.splice(index, 1);
      $scope.uploadResult = 'File deleted sucessfully.';
      notice.push(notice.SUCCESS, $scope.uploadResult);
    };

    // For the buttons.
    $scope.newTextCollapsed = true;

    /* This is necessary to properly bind the uploaded file to the input element to submit it */
    $scope.callUploadClickEvent = function(elementId) {
      document.getElementById(elementId).click();
    };

    // Add Text
    $scope.addText = function() {
      var model = {};
      model.id = '';
      model.title = $scope.title;
      model.content = $scope.content;
      /*
            textService.update(model, function(result) {
              if (result.ok) {
                notice.push(notice.SUCCESS, "The text '" + model.title + "' was added successfully");
              }
              $scope.getPageDto();
            });
       */
    };
  },]);
