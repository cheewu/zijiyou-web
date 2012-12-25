$(document).ready(function() {
	//添加click事件，为地图添加marker
	google.maps.event.addListener(map, 'click', function(event) {
		markerChange(event.latLng, false);
	});
	google.maps.event.addListener(map, 'zoom_changed', function() {
		$("#map_zoom").val(map.getZoom());
	});
	marker = new google.maps.Marker({ //如果坐标存在，则直接显示标记
		map: map, 
		position: position
	});
});
//画地图
map = new google.maps.Map(document.getElementById("google_map"), myOptions); 
//改变marker
function markerChange(position, setCenter) {
	setCenter && map.setCenter(position);
	if(marker){//清除之前标记
		marker.setMap(null);
	}
	marker = new google.maps.Marker({
		position: position,
		map: map
	});
	$("#lat").val(marker.position.lat());
	$("#lng").val(marker.position.lng());
}

function search() {
	var address = $("#address").val();
	if(!address){return;}
	geocoder.geocode( { 'address': address}, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK) {
			markerChange(results[0].geometry.location, true);
		} else {
			alert("Geocode was not successful for the following reason: " + status);
		}
	});
}
