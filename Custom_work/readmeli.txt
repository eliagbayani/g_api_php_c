Find out what kind of credentials you need 
Calling Google Maps Geocoding API from a UI-based platform

You already have credentials that are suitable for this purpose

Don't want to use this existing API key? Create a new API key

Server key 1
Key:            AIzaSyCXt2WPrcQniaMomonEruEOi3EHYlGEi3U
Type:           Server
Creation date:  May 25, 2015, 1:14:52 AM
==============================================================================
http://stackoverflow.com/questions/20807974/how-to-use-the-importrows-method-of-google-fusion-tables-api-using-google-client

function makeApiCallImportRows() { 
  var request = gapi.client.request({
    'path': '/upload/fusiontables/v1/tables/'+tableId+'/import',
    'method': 'POST',
    'params': {'uploadType': 'media'},
    'headers' : {'Content-Type' : 'application/octet-stream'},
    'body': '9,9,9,2013\n8,8,8,2014\n'
  });
  request.execute(function(resp) {
    alert(resp.toSource());
    });
}

==============================================================================
Fusion table limitations:
http://stackoverflow.com/questions/11952166/what-are-the-technical-limitations-when-using-fusion-tables
http://www.niemanlab.org/2012/06/google-loosens-its-limits-and-pricing-on-maps-api/
==============================================================================
Fusion codebase and developer site:
https://code.google.com/u/115787363615701588400/
==============================================================================
Map infoWindow: https://support.google.com/fusiontables/answer/171216
https://support.google.com/fusiontables/answer/3081246?hl=en&ref_topic=2575652
==============================================================================
Wizard: http://fusion-tables-api-samples.googlecode.com/svn/trunk/FusionTablesLayerWizard/src/index.html
==============================================================================
