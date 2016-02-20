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
==============================================================================
==============================================================================
==============================================================================
==============================================================================
