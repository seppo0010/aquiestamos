(function() {
    var map = null;
    var mapReady = false;
    var settings = null;
    var mapDefaults = null;;
    var markerCluster = null;
    var checkinAfterInit = false;

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
                lat: center.lat(),
                lon: center.lng(),
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
                var center = map.getCenter();
                document.cookie = 'ae_checkin_location=' + center.lat() + ',' + center.lng() + ',' + map.getZoom() + ';path=/'
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
            if (!mapDefaults) {
                if (map) {
                    map.panTo(center);
                    map.setZoom(zoom);
                } else {
                    mapDefaults = {
                        zoom: zoom,
                        center: center
                    };
                }
            }
        });
    }

    function aeFetchLocations() {
        if (document.cookie) {
            var key = 'ae_checkin_location=';
            var cookie = document.cookie;
            var start = cookie.indexOf(key);
            if (start !== -1) {
                var end = cookie.indexOf(';', start);
                if (end === -1) {
                    end = cookie.length;
                }
                var coords = unescape(cookie.substr(start + key.length, end)).split(',');
                if (coords.length == 3) {
                    mapDefaults = {
                        zoom: parseFloat(coords[2]),
                        center: {lat: parseFloat(coords[0]), lng: parseFloat(coords[1])},
                    };
                    checkinAfterInit = true;
                }
                document.cookie = 'ae_checkin_location=;expires=Thu, 01 Jan 1970 00:00:01 GMT;path=/'
            }
        }
        jQuery.ajax({
            url: settings.base_url + 'ae/v1/checkin',
            headers: {'x-wp-nonce':settings.nonce},
        }).done(function (response) {
            aeInitMap(response);
        });
    };

    function aeInitMap(locations) {
        map = new google.maps.Map(document.getElementById('ae_map'), mapDefaults || {
            zoom: 4,
            center: {lat: -35.376184, lng: -63.998128}
        });
        var markers = locations.map(function(location, i) {
            return new google.maps.Marker({position: location});
        });
        markerCluster = new MarkerClusterer(map, markers, {
            imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'
        });
        if (checkinAfterInit) {
            aeDoCheckin();
        }
    }
})();
