'use strict';

angular.module('typesetting.compositionServices', ['jsonRpc'])
  .service('typesettingCompositionService', ['jsonRpc',
  function(jsonRpc) {
    jsonRpc.connect('/api/sf');

    this.getBookHTML = function getBookHTML(bookId, callback) {
      jsonRpc.call('typesetting_composition_getBookHTML', [bookId], callback);
    };

    this.getParagraphProperties = function getParagraphProperties(bookId, callback) {
      jsonRpc.call('typesetting_composition_getParagraphProperties', [bookId], callback);
    };

    this.setParagraphProperties = function setParagraphProperties(bookId, properties, callback) {
      jsonRpc.call('typesetting_composition_setParagraphProperties', [bookId, properties], callback);
    };

    this.renderBook = function renderBook(bookId, callback) {
      jsonRpc.call('typesetting_composition_renderBook', [bookId], callback);
    };

    this.getRenderedPageForBook = function getRenderedPageForBook(bookId, pageNumber, callback) {
      jsonRpc.call('typesetting_composition_getRenderedPageForBook', [bookId, pageNumber], callback);
    };

    this.getPageDto = function getPageDto(callback) {
      jsonRpc.call('typesetting_composition_getPageDto', [], callback);
    };

    this.getBookDto = function getBookDto(bookId, callback) {
      jsonRpc.call('typesetting_composition_getBookDto', [bookId], callback);
    };

    this.getIllustrationProperties = function getIllustrationProperties(callback) {
      jsonRpc.call('typesetting_composition_getIllustrationProperties', [], callback);
    };

    this.setIllustrationProperties = function setIllustrationProperties(properties, callback) {
      jsonRpc.call('typesetting_composition_setIllustrationProperties', [properties], callback);
    };

    this.getPageStatus = function getPageStatus(bookId, callback) {
      jsonRpc.call('typesetting_composition_getPageStatus', [bookId], callback);
    };

    this.setPageStatus = function setPageStatus(bookId, pages, callback) {
      jsonRpc.call('typesetting_composition_setPageStatus', [bookId, pages], callback);
    };

  },])
  .service('typesettingSetupService', ['jsonRpc',
  function(jsonRpc) {
    jsonRpc.connect('/api/sf');

    this.setupPageDto = function(callback) {
    };

  },]);

