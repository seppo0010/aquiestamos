(function() {
    var mapReady = false;
    var nonce = null;

    window.aeMapReady = function() {
        mapReady = true;
        if (nonce) aeFetchLocations();
    }

    window.aeSetNonce = function (_nonce) {
        nonce = _nonce;
        if (mapReady) aeFetchLocations();
    }

    function aeFetchLocations() {
        jQuery.ajax({
            url: '/index.php/wp-json/ae/v1/checkin',
            headers: {'x-wp-nonce':nonce},
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
