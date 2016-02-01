'use strict';

angular.module('typesetting.services', ['jsonRpc'])
  .service('typesettingEditService', ['jsonRpc',
  function(jsonRpc) {
    jsonRpc.connect('/api/sf');
    this.render = function render(callback) {
      jsonRpc.call('typesetting_rapuma_render', [], callback);
    };
    this.setProjectConfig = function setProjectConfig(projectID,projectIDCode, projectCIDLIST, callback) {
      jsonRpc.call('typesetting_settings_project_config', [projectID,projectIDCode, projectCIDLIST], callback);
    };
    this.editorDto = function(callback) {
    };

  },])
  .service('typesettingSetupService', ['jsonRpc',
  function(jsonRpc) {
    jsonRpc.connect('/api/sf');

    this.setupPageDto = function(callback) {
    };

  },])
  .service('typesettingAssetService', ['jsonRpc', function(jsonRpc) {
    jsonRpc.connect('/api/sf');
    this.readAssets = function readAssets(callback) {
      jsonRpc.call('typesetting_readAssetsDto', [], callback);
    };
  },])

  //This factory communicates with the layout controller and sends the jsonRpc call
  .factory('templateSaveService', function($rootScope, jsonRpc) {
    var templateSaveObject = {};

    //Other controllers can add their data to the  variable
    templateSaveObject.prepForBroadcast = function(templateName) {
      templateSaveObject.templateName = templateName;
      templateSaveObject.broadcastItem();
    };

    templateSaveObject.broadcastItem = function() {
      $rootScope.$broadcast('handleSaveBroadcast');

      //console.log(templateSaveObject);
      jsonRpc.connect('/api/sf');
      console.log('blobbo');
      jsonRpc.call('template_save', [templateSaveObject], function(result) {
        if (result.ok) {
          console.log(result);
          console.log('saved');
        }
      });
    };

    return templateSaveObject;
  })

  //This factory communicates with the layout controller and sends the jsonRpc call
  .factory('templateLoadService', function($rootScope, jsonRpc) {
    var templateLoadObject = {};

    //The load template name gets passed through here
    templateLoadObject.prepForBroadcast = function(templateName) {
      templateLoadObject.templateName = templateName;
      jsonRpc.call('template_load', [templateName], function(result) {
        if (result.ok) {
          console.log(result);
          console.log('LOADED!');

          //Load the collected data into some object that the controller can access.
          templateLoadObject.newConf = result.data;
          templateLoadObject.broadcastItem();
        } else {
          console.log('We could not find your file. It\'s just a minor mistake.');
        }
      });

      templateLoadObject.broadcastItem();
    };

    //Broadcasts to the controller to either receive/send template data
    templateLoadObject.broadcastItem = function() {
      $rootScope.$broadcast('handleLoadBroadcast');
    };

    return templateLoadObject;
  })

  ;
