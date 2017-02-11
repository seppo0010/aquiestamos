(function() {
    var mapReady = false;
    var settings = null;

    window.aeMapReady = function() {
        mapReady = true;
        if (settings) aeFetchLocations();
    }

    window.aeSettings = function (_settings) {
        settings = _settings;
        if (mapReady) aeFetchLocations();
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
        var map = new google.maps.Map(document.getElementById('ae_map'), {
            zoom: 4,
            center: {lat: -35.376184, lng: -63.998128}
        });
        var markers = locations.map(function(location, i) {
            return new google.maps.Marker({position: location});
        });
        var markerCluster = new MarkerClusterer(map, markers, {
            imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'
        });
    }
})();
