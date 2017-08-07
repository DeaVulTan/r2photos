define([
  'jquery',
  'runway',
  '../../app/site/data/common-data'
], function($, runway, common) {
  runway.attachTo(document, {
    services: [ common ]
  });

});
