(function() {
    var map = null;
    var mapReady = false;
    var settings = null;
    var mapDefaults = {
        zoom: 4,
        center: {lat: -35.376184, lng: -63.998128}
    };
    var markerCluster = null;

    window.aeMapReady = function() {
        mapReady = true;
        if (settings) aeFetchLocations();
    }

    function aeDoCheckin() {
        jQuery('#ae_login').hide();
        jQuery('#ae_checkin').hide();
        jQuery('#ae_thanks').show();
        var center = map.getCenter();
        jQuery.ajax({
            url: settings.base_url + 'ae/v1/checkin',
            method: 'POST',
            headers: {'x-wp-nonce':settings.nonce},
            data: {
                lat: center.lat,
                lon: center.lng,
            }
        }).done(function (response) {
            if (markerCluster) {
                markerCluster.addMarkers([new google.maps.Marker({position: center})]);
            }
        });
    }

    window.aeSettings = function (_settings) {
        settings = _settings;
        jQuery('#ae_checkin').click(function(evt) {
            evt.preventDefault();
            if (settings.loggedin) {
                aeDoCheckin();
            } else {
                jQuery('#ae_login').show();
                jQuery('#ae_checkin').hide();
            }
        });
        if (mapReady) aeFetchLocations();
    }

    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(function(position) {
            var center = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };
            var zoom = 15;
            if (map) {
                map.panTo(center);
                map.setZoom(zoom);
            } else {
                mapDefaults = {
                    zoom: zoom,
                    center: center
                }
            }
        });
    }

    function aeFetchLocations() {
        jQuery.ajax({
            url: settings.base_url + 'ae/v1/checkin',
            headers: {'x-wp-nonce':settings.nonce},
        }).done(function (response) {
            aeInitMap(response);
        });
    };

    function aeInitMap(locations) {
        map = new google.maps.Map(document.getElementById('ae_map'), mapDefaults);
        var markers = locations.map(function(location, i) {
            return new google.maps.Marker({position: location});
        });
        markerCluster = new MarkerClusterer(map, markers, {
            imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'
        });
    }
})();