'use strict';

angular.module('typesetting.renderedPageServices', ['jsonRpc'])
  .service('typesettingRenderedPageService', ['jsonRpc',
  function(jsonRpc) {
    jsonRpc.connect('/api/sf');

    this.getRenderedPageDto = function getRenderedPageDto(callback) {
      jsonRpc.call('typesetting_rendered_page_getRenderedPageDto', [], callback);
    };

  },])
    .service('typesettingSetupService', ['jsonRpc',
  function(jsonRpc) {
    jsonRpc.connect('/api/sf');

    this.setupPageDto = function(callback) {
    };

  },]);

