'use strict';

angular.module('typesetting.renderedPageServices', ['jsonRpc'])
  .service('typesettingRenderedPageService', ['jsonRpc',
  function(jsonRpc) {
    jsonRpc.connect('/api/sf');



    this.getPageDto = function getRenderedPageDto(callback) {
      jsonRpc.call('typesetting_rendered_page_getRenderedPageDto', [], callback);
    };

    this.setRenderedPageComments = function setRenderedPageComments(bookId, pageNumber, callback) {
      jsonRpc.call('typesetting_rendered_page_setRenderedPageComment', [bookId, pageNumber], callback);
    };

  }]);

