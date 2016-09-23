'use strict';

/* Filters */
// need load the moment.js to use this filter. 
angular.module('app')
  .filter('fromNow', function() {
    return function(date) {
      return moment(date).fromNow();
    }
  })

  .filter('unixTime', function() {
    return function (date) {
        return new Date(date).getTime() / 1000;
    }
  });