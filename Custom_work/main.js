var map;
var layer_0;
function initialize() {
    map = new google.maps.Map(document.getElementById('map-canvas'), {center: new google.maps.LatLng(data.center_lat, data.center_lon), zoom: 3, mapTypeId: google.maps.MapTypeId.ROADMAP});
    layer_0 = new google.maps.FusionTablesLayer({
        query: {select: "'location'", from: data.tableID},
        map: map,
        heatmap: { enabled: false }
    });
      
    google.maps.event.addDomListener(document.getElementById('heatmap'), 'click', function() {
        var heatmap = document.getElementById('heatmap');
        layer_0.setOptions({
        heatmap: {enabled: heatmap.checked}
        });
    });
    
    document.getElementById('heatmap').checked = false; //intial state
    add_publishers();
}

function select_change() {
    var heatmap = document.getElementById('heatmap');
    var heatmap_label = document.getElementById('heatmap_label');
    var whereClause;
    var searchString = document.getElementById('select_publisher').value.replace(/'/g, "\\'");
    if (searchString != '--ALL--') {
        whereClause = "'publisher' = '" + searchString + "'";
        heatmap.checked = false;
        layer_0.setOptions({heatmap: {enabled: false}});
        heatmap.disabled = true;
        heatmap_label.style['color'] = 'gray';
    }
    else { heatmap.disabled = false;
           heatmap_label.style['color'] = 'black';
    }
    layer_0.setOptions({query: {select: "'location'", from: data.tableID, where: whereClause}});
}

function add_publishers() {
    var select = document.getElementById('select_publisher');
    publishers = data.publishers;
    for (var i = 0; i < publishers.length; i++) 
    {
        var opt = document.createElement('option');
        opt.value = publishers[i];
        opt.innerHTML = publishers[i];
        select.appendChild(opt);
    }
}
google.maps.event.addDomListener(window, 'load', initialize);
