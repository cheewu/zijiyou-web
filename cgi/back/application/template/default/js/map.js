/**
 * @author hourui
 * @charset UTF-8
 */
(function(){
  
  var _ = {};
  gMap = _;
  
  _.draw = function(id, opt, callback) {
    this.map    = new google.maps.Map($('#' + id)[0], opt);
    this.marker = new google.maps.Marker({
      map      : this.map, 
      position : opt.center
    });
    this.searcher = new google.maps.Geocoder();
    this.bindEvent(callback);
  }
  
  _.bindEvent = function(callback) {
    this.callback = callback;
    google.maps.event.addListener(this.map, 'click', function(event) {
      if (typeof(_.callback.recordCenter) != 'function') return;
      _.callback.recordCenter(event.latLng);
      _.marker.setPosition(event.latLng)
    });
    google.maps.event.addListener(this.map, 'zoom_changed', function() {
      if (typeof(_.callback.recordZoom) != 'function') return;
      _.callback.recordZoom(_.map.getZoom());
    });
  }
  
  _.search = function(q) {
    this.searcher.geocode({'address': q}, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
        var center = results[0].geometry.location;
        _.map.setCenter(center)
        _.marker.setPosition(center);
        _.callback.recordCenter(center);
      } else {
        alert("Address: '" + q + "' not found");
      }
    });
  }
})();