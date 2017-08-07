define([
  'jquery',
  'runway',
  'site/data/common-data'
], function($, runway, common) {
  runway.attachTo(document, {
    services: [ common ]
  });

});
