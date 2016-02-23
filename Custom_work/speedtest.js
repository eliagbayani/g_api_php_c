
function $(element) {
  return document.getElementById(element);
}

var speedTest = {};

speedTest.pics = null;

function put_list() {

  var panel = $('markerlist');
  // var panel = document.getElementById('markerlist');
  
  panel.innerHTML = '';
  
  speedTest.pics = data.records;
  
  var numMarkers = speedTest.pics.length;
  $('total_markers').innerHTML = numMarkers;

  for (var i = 0; i < numMarkers; i++) {
    var titleText = speedTest.pics[i].catalogNumber;
    if (titleText === '') {
      titleText = 'No catalog number';
    }

    var item = document.createElement('DIV');
    var title = document.createElement('A');
    title.href = '#';
    title.className = 'title';
    title.innerHTML = titleText;

    item.appendChild(title);
    panel.appendChild(item);

    
    var latLng = new google.maps.LatLng(speedTest.pics[i].lat, speedTest.pics[i].lon);

    // var fn = speedTest.markerClickFunction(speedTest.pics[i], latLng);
    google.maps.event.addDomListener(title, 'click', fn);
    
    
    
  }//end looping of markers
  
  
};



