(function($) {
    var map = null;
    var mapReady = false;
    var settings = null;
    var mapDefaults = null;;
    var markerCluster = null;
    var checkinAfterInit = false;
    var since = null;
    var icon = null;
    var myIcon = null;
    var cookieName = 'ae_checkin_location';
    var openwindow = null;

    window.aeMapReady = function() {
        mapReady = true;
        aeFetchLocations();
    }

    $(function() {
        $('body').on('click', '[data-ae-close]', function(e) {
            e.preventDefault();
            $('.ae_modal').hide();
        });
    });

    function aeDoCheckin() {
        $('#ae_login').hide();
        $('#ae_checkin').hide();
        $('#ae_thanks').show();
        var center = map.getCenter();
        $.ajax({
            url: settings.base_url + 'ae/v1/checkin',
            method: 'POST',
            headers: {'x-wp-nonce':settings.nonce},
            data: {
                lat: center.lat(),
                lon: center.lng(),
                content: aeGetCheckinText(),
                status: 'publish',
            }
        }).done(function (response) {
            aePollLocations();
        });
    }

    // TODO: serialize the whole form
    function aeGetCheckinText() {
        return $('#ae_checkin form input[name="content"]').val();
    }

    function aeSetCheckinText(text) {
        return $('#ae_checkin form input[name="content"]').val(text || '');
    }

    window.aeSettings = function (_settings) {
        settings = _settings;
        icon = settings.marker ? new google.maps.MarkerImage(settings.marker.url,
            new google.maps.Size(settings.marker.width, settings.marker.height),
            new google.maps.Point(0, 0),
            new google.maps.Point(settings.marker.vertexX, settings.marker.vertexY)
        ) : null;
        myIcon = settings.my_marker ? new google.maps.MarkerImage(settings.my_marker.url,
            new google.maps.Size(settings.my_marker.width, settings.my_marker.height),
            new google.maps.Point(0, 0),
            new google.maps.Point(settings.my_marker.vertexX, settings.my_marker.vertexY)
        ) : null;
        var handleCheckin = function(evt) {
            evt.preventDefault();
            if (settings.loggedin) {
                aeDoCheckin();
            } else {
                var center = map.getCenter();
                document.cookie = cookieName + '=' + escape(JSON.stringify({
                    lat: center.lat(),
                    lng: center.lng(),
                    zoom: map.getZoom(),
                    checkinText: aeGetCheckinText(),
                })) + ';path=/'
                $('#ae_login').show();
                $('#ae_checkin').hide();
            }
        };
        $('#ae_checkin a[data-checkin]').click(handleCheckin);
        $('#ae_checkin form[data-checkin]').submit(handleCheckin);
        aeFetchLocations();
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
                        center: center,
                    };
                }
            }
        });
    }

    function aePollLocations() {
        var separator = settings.base_url.indexOf('?') === -1 ? '?' : '&';
        $.ajax({
            url: settings.base_url + 'ae/v1/checkin' + separator + 'since=' + (since || ''),
            headers: {'x-wp-nonce':settings.nonce},
        }).done(function (response) {
            since = response.since;
            $('.ae-count').text(response.count);
            aeAddLocations(response.results);
        });
    }

    function aeFetchLocations() {
        if (!settings || !mapReady) {
            return;
        }
        if (document.cookie) {
            var key = cookieName + '=';
            var cookie = document.cookie;
            var start = cookie.indexOf(key);
            if (start !== -1) {
                var end = cookie.indexOf(';', start);
                if (end === -1) {
                    end = cookie.length;
                }
                try {
                    var cookieData = JSON.parse(unescape(cookie.substr(start + key.length, end - start - key.length)));
                    mapDefaults = {
                        zoom: parseFloat(cookieData.zoom),
                        center: {lat: cookieData.lat, lng: cookieData.lng},
                    };
                    aeSetCheckinText(cookieData.checkinText);
                    if (settings.loggedin) {
                        checkinAfterInit = true;
                    }
                } catch (e) {
                }
                document.cookie = cookieName + '=;expires=Thu, 01 Jan 1970 00:00:01 GMT;path=/'
            }
        }
        $.ajax({
            url: settings.base_url + 'ae/v1/checkin',
            headers: {'x-wp-nonce':settings.nonce},
        }).done(function (response) {
            since = response.since;
            aeInitMap(response.results);
        });
        setInterval(aePollLocations, 30000);
    };

    function aeAddLocations(locations) {
        markerCluster.addMarkers(locations.map(function(post) {
            var infowindow;
            if (settings.checkin_html) {
                var post_content = $('<div>').text(post.post_content || '').html();
                var html = settings.checkin_html.replace(/\[ae-checkin-text\]/g, post_content);
                if (html) {
                    infowindow  = new google.maps.InfoWindow({
                        content: html,
                    });
                }
            }
            var marker = new google.maps.Marker({
                position: {
                    lat: post.lat,
                    lng: post.lng,
                },
                icon: post.current_user ? myIcon : icon,
            });
            google.maps.event.addListener(marker, 'click', function() {
                if (infowindow) {
                    infowindow.open(map, marker);
                }
                if (openwindow) {
                    openwindow.close();
                }
                if (openwindow === infowindow) {
                    openwindow = null;
                } else {
                    openwindow = infowindow;
                }
            });
            return marker;
        }));
    }
    function aeInitMap(locations) {
        var options = mapDefaults || {
            streetViewControl: false,
            zoom: 4,
            center: {lat: -35.376184, lng: -63.998128},
        };
        options.styles = settings.styles;
        options.mapTypeControlOptions = {mapTypeIds: google.maps.MapTypeId.ROADMAP};
        map = new google.maps.Map(document.getElementById('ae_map'), options);
        markerCluster = new MarkerClusterer(map, [], settings.cluster_options);
        aeAddLocations(locations);
        if (checkinAfterInit) {
            aeDoCheckin();
        }
    }
})(jQuery);
