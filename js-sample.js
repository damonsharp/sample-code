jQuery.noConflict();

var Mercury = Mercury || {};

(function($){

    Mercury.SelfDetail = {

        // Initialize self detail and load up the presentation container,
        // messages, and/or both
        init: function() {

            // Initialize the message for display to the user during public/private check
            var msg = '';

            this.domain = document.domain;

            // Prevent errors for older browsers and ie with no console.log function
            if ( ! window.console ){
                window.console = {
                    log: function(){}
                };
            }

            // Check for public/private, then view count. Display messages accordingly
            // availability, viewCount and macViews variables set in /self/index.cshtml
            // availability === 1 = private self detail
            if ( availability === 1 ) {

                // If view count is between 45 and 49, display a message with the
                // number of views left.
                if ( ( viewCount > ( maxViews - 5 ) ) && viewCount < maxViews ) {

                    var remainingViews = maxViews - viewCount,
                        // Deal with singular and plural views left
                        remainingViewsText = ( remainingViews === 1 ) ? ' view ' : ' views ';

                    msg = 'This presentation has ' + remainingViews + remainingViewsText + 'left.';
                    this.displayMessage(msg, true);

                // If the number of views has reached the max view count
                // display a message that they will need to request more views
                } else if ( viewCount == maxViews ) {

                    msg = 'This is the last view available for this presentation. To request more views, please contact your representative.';
                    this.displayMessage(msg, true);

                // When code === 2 = max number of views exceeded,
                // display a message asking them to request more views.
                } else if ( code === 2 ) {

                    msg = 'This presentation has 0 views left.  To request more views, please contact your representative.';
                    this.displayMessage(msg);

                // The visitor is viewing the presentation for the 1st through ( 5 less than the max ) views,
                // so just setup the container and let them view it without any messages displayed.
                } else {

                    this.setupContainer();

                }

            // This is a public self detail, and there are no max view limits, so just setup the container
            // and show the presentation.
            } else if ( availability === 2 ) {

                this.setupContainer();

            }

        },

        // If proper variables are setup, let's create the container for the presentation
        // and resize it to fit the presentation inside it.
        setupContainer: function() {

            var self = this;

            if ( presentationCuid || 0 < presentationCuid.length ) {
                domain = "demo.iqmercury.com";

                var iFrame = $('#pres-container'),
                    presentationURL = 'http://' + this.domain + '/Presentations/';

                $.getJSON(

                    presentationURL + presentationCuid + '/config.json',
                    function(data){

                        iFrame.attr( 'src', presentationURL + presentationCuid + '/' + data.ivaPath );

                    }

                );
                $('#iframe-parent').removeClass('hidden');

                // Once the iframe container is setup let's send a "presentation started" metric
                // and setup one for unload, so if the user closes their browser/tab, we'll send
                // a "presentation ended" metric as well.
                self.metrics.StoreMetric('Presentation started', false);

                $(window.parent).on('unload', function(){

                    self.metrics.StoreMetric('Presentation ended by closing browser', true);

                });

                this.resizeIframe();

            }

        },

        // Map metric calls from the iframe layer to the proper
        // function and execute.
        metrics: {

            // Map the Mercury Metric API call to the appropriate method below.
            // If the method doesn't exist, console a message via this.DebugTrace()
            call: function(functionName, args) {

                this[functionName] ? this[functionName](args) : this.DebugTrace('"' + functionName + '" is an invalid API function call.');

            },

            // Store the metric on the server
            StoreMetric: function ( args, closeBrowser ) {

                if ( 'undefined' === closeBrowser ) {

                    closeBrowser = false;

                }

                var self = Mercury.SelfDetail.metrics,
                    metricData = {

                        "Data": args ? args : '',
                        "EventDateTime": self.getIsoDateString(),
                        "PresentationId": presentationId,
                        "UserId": userId,
                        "Latitude": null,
                        "Longitude": null,
                        "PlayerLayer": 3,
                        /*"MetricPacketId": 434534,*/
                        "MetricAuthentication": metricAuth

                    },
                    asynchronous = ( true == closeBrowser ) ? false : true;

                // Authenticate and submit data to server
                // metricUser & metricAuth set on /self/index view
                $.ajax({
                    async: asynchronous,
                    type: 'POST',
                    url: "http://" + Mercury.SelfDetail.domain + "/api/metric/inperson",
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader("Authorization", "Basic " + base64.encode(metricUser + ":" + metricAuth));
                    },
                    data: metricData,
                    dataType: 'JSON',
                    success: function(data){}
                });

                self.DebugTrace(args);

            },

            // Function used for content development testing
            DebugTrace: function ( msg ) {

                console.log(msg);

            },

            EndPresentation: function ( args ) {

                var self = this,
                    msg = 'The presentation has ended.  You may now close your browser.';

                // Store a metric
                self.StoreMetric(args);

                // Display a message to the user that they can close their browser
                Mercury.SelfDetail.displayMessage( msg, false, true );

            },

            getIsoDateString: function () {

                var d = new Date(),
                    tZoneOffsetMinutes = d.getTimezoneOffset(),
                    tZoneOffsetHours = tZoneOffsetMinutes / 60,
                    minutes = ( "0" + (tZoneOffsetMinutes % 60) ).slice(-2),
                    divider = tZoneOffsetMinutes < 0 ? '-' : '+';

                function pad(n){
                    return n < 10 ? '0' + n : n;
                }

                return d.getFullYear() + '-'
                    + pad( d.getMonth() + 1 ) + '-'
                    + pad( d.getDate() ) + 'T'
                    + pad( d.getHours() ) + ':'
                    + pad( d.getMinutes() ) + ':'
                    + pad( d.getSeconds() ) + divider
                    + pad( tZoneOffsetHours ) + ':' + minutes;

            }

        },

        // Display the proper message on the presentation page depending on
        // the logic above
        displayMessage: function( msg, showButton, endPresentation ) {

            var self = this,
                messageBox = $('#self-detail-msg'),
                messageBoxButton = messageBox.find('button'),
                iframeParent = $('#iframe-parent');

            if ( typeof(showButton) === 'undefined' ) {
                showButton = false;
            }

            if ( typeof(endPresentation) === 'undefined' ) {
                endPresentation = false;
            }

            if ( true == endPresentation ) {
                iframeParent.remove();
                messageBoxButton.remove();
            }

            if ( true == showButton ) {

                messageBoxButton
                    .removeClass('hidden')
                    .on('click', function(){

                        messageBox.addClass('hidden');
                        messageBoxButton.addClass('hidden');
                        self.setupContainer();

                    });

            }

            messageBox.removeClass('hidden').find('p').text(msg);

        },


        // Resize iframe to fit the content
        resizeIframe: function() {

            var iFrames = $('iframe');

            function iResize() {

                for (var i = 0, j = iFrames.length; i < j; i++) {
                    iFrames[i].style.height = iFrames[i].contentWindow.outerHeight + 'px';

                }
            }

            if ( $.browser.safari || $.browser.opera ) {

                iFrames.load(function(){
                    setTimeout(iResize, 0);
                });

                for (var i = 0, j = iFrames.length; i < j; i++) {
                    var iSource = iFrames[i].src;
                    iFrames[i].src = '';
                    iFrames[i].src = iSource;
                }

            } else {

                iFrames.load(function() {

                    this.style.height = this.contentWindow.outerHeight + 'px';

                });

            }

        }

    };

    Mercury.SelfDetail.init();

})(jQuery);