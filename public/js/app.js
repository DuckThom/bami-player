var app = angular.module('BamiPlayerApp', []);

// Run

app.run(function () {
    var tag = document.createElement('script');
    tag.src = "https://www.youtube.com/iframe_api";
    var firstScriptTag = document.getElementsByTagName('script')[0];
    firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
});

// Config

app.config( function ($httpProvider) {
    delete $httpProvider.defaults.headers.common['X-Requested-With'];
});

// Service

app.service('VideosService', ['$window', '$rootScope', '$log', '$http', '$timeout', function ($window, $rootScope, $log, $http, $timeout) {

    var service = this;
    var isCastEnabled;
    var castSession = null;
    var castMedia = null;
    var searched = false;
    var stillPlaying;

    var youtube = {
        ready: false,
        player: null,
        playerId: null,
        videoId: null,
        videoTitle: null,
        playerHeight: '480',
        playerWidth: '640',
        state: 'stopped'
    };

    var results = [];
    var upcoming = [];
    var history = [];

    var lists = [];
    lists['upcoming'] = upcoming;
    lists['history'] = history;

    var stream = { mode: 'unset' };

    $window.__onGCastApiAvailable = function(loaded, errorInfo) {
        if (loaded) {
            service.initializeCastApi();
        } else {
            isCastEnabled = false;
        }
    };

    this.initializeCastApi = function () {
        isCastEnabled = true;

        var sessionRequest = new chrome.cast.SessionRequest(chrome.cast.media.DEFAULT_MEDIA_RECEIVER_APP_ID);
        var apiConfig = new chrome.cast.ApiConfig(sessionRequest, service.sessionListener, service.receiverListener);
        chrome.cast.initialize(apiConfig, service.onInitSuccess, service.onError);
    };

    this.receiverListener = function (e) {
        if( e === chrome.cast.ReceiverAvailability.AVAILABLE) {
            $log.info("Chromecast status: " + e);
            $rootScope.castSupport = true;
        }
    };

    this.sessionListener = function (e) {
        castSession = e;

        if (castSession.media.length != 0) {
            onMediaDiscovered('onRequestSessionSuccess', castSession.media[0]);
        }
    };

    this.onInitSuccess = function (e) {
        $log.info("Google Cast API is ready");

        castSession = chrome.cast.Session;
        castSession.displayName = "Bami Player";
        $log.info(castSession);
    };

    this.onError = function (e) {
        $log.error(e);
    };

    this.onSuccess = function (e) {
        $log.info(e);
    };

    this.startCast = function () {
        if (typeof upcoming[0] != 'undefined') {
            var mediaInfo = new chrome.cast.media.MediaInfo("https://r20---sn-5hnednl7.googlevideo.com/videoplayback\?dur\=313.161\&key\=yt6\&lmt\=1456950795983922\&fexp\=9405981%2C9406819%2C9416126%2C9420452%2C9421979%2C9422342%2C9422596%2C9423340%2C9423661%2C9423662%2C9423749%2C9425283%2C9426150%2C9427600%2C9427678%2C9428656%2C9428990%2C9429236%2C9431244\&gcr\=nl\&initcwndbps\=992500\&ipbits\=0\&id\=o-AI1nl8S920F0jtr-sXSVT9lERVjFwYUAgE6w5CjxrfOO\&keepalive\=yes\&sver\=3\&sparams\=clen%2Cdur%2Cgcr%2Cgir%2Cid%2Cinitcwndbps%2Cip%2Cipbits%2Citag%2Ckeepalive%2Clmt%2Cmime%2Cmm%2Cmn%2Cms%2Cmv%2Cnh%2Cpl%2Crequiressl%2Csource%2Cupn%2Cexpire\&gir\=yes\&upn\=tlZSS3IJmUw\&ms\=au\&ip\=94.212.83.183\&pl\=15\&itag\=251\&mv\=m\&expire\=1457154871\&mm\=31\&mt\=1457133178\&clen\=5049995\&mime\=audio%2Fwebm\&requiressl\=yes\&nh\=IgpwZjAxLmFtczE1Kg4yMTMuNTEuMTU2LjIxMw\&source\=youtube\&mn\=sn-5hnednl7\&signature\=DD0325631A1FAC6F787C0FF74EE1504C7B5BEAA5.CC49E5BEF254A2E50D5E68748F4DC2DAFD28A9DC\&ratebypass\=yes", 'audio/webm');
            var request = new chrome.cast.media.LoadRequest(mediaInfo);
            castSession.loadMedia(request, service.onMediaDiscovered.bind(this, 'loadMedia'), service.onMediaError);

            /*$http.get('/v1/video/getStreamUrl', {
                id: upcoming[0].id
            }).then(function success(response) {
                    var url = response.data.payload.url;

                    $log.info(url);


                },
                function failure(response) {
                    $log.error(response);

                    return false;
                });*/

            return true;
        } else {
            return false;
        }
    };

    this.onMediaDiscovered = function(how, media) {
        castMedia = media;

        castMedia.play();
    };

    this.onMediaError = function(e) {
        $log.error(e);
    };

    this.stopCast = function () {
        castSession.stop(service.onSuccess, service.onError)
    };

    $window.onYouTubeIframeAPIReady = function () {
        $log.info('Youtube API is ready');
        youtube.ready = true;
        $rootScope.$apply();
    };

    function onYoutubeReady () {
        $log.info('YouTube Player is ready');

        if (typeof upcoming[0] != 'undefined')
        {
            service.launchPlayer(upcoming[0].id, upcoming[0].title);
            service.archiveVideo(upcoming[0].id, upcoming[0].title);
        }
    }

    function onYoutubeStateChange (event) {
        if (event.data == YT.PlayerState.PLAYING) {
            youtube.state = 'playing';
        } else if (event.data == YT.PlayerState.PAUSED) {
            youtube.state = 'paused';
        } else if (event.data == YT.PlayerState.ENDED) {
            youtube.state = 'ended';
            service.launchPlayer(upcoming[0].id, upcoming[0].title);
            service.archiveVideo(upcoming[0].id, upcoming[0].title);
        }
        $rootScope.$apply();
    }

    this.bindPlayer = function (elementId) {
        $log.info('Binding to ' + elementId);
        youtube.playerId = elementId;
    };

    this.createPlayer = function () {
        $log.info('Creating a new Youtube player for DOM id ' + youtube.playerId + ' and video ' + youtube.videoId);

        return new YT.Player(youtube.playerId, {
            height: youtube.playerHeight,
            width: youtube.playerWidth,
            videoId: 'S5PvBzDlZGs',
            playerVars: {
                'rel': 0,
                'showinfo': 0,
                'autoplay': 1
            },
            events: {
                'onReady': onYoutubeReady,
                'onStateChange': onYoutubeStateChange
            }
        });
    };

    this.checkCastSupport = function () {
        return isCastEnabled;
    };

    this.loadPlayer = function () {
        if (youtube.ready && youtube.playerId) {
            youtube.player = service.createPlayer();
        }
    };

    this.unloadPlayer = function () {
        if (youtube.ready && youtube.playerId) {
            if (youtube.player) {
                youtube.player.destroy();
            }
        }
    };

    this.launchPlayer = function (id, title) {
        if (youtube.player != null) {
            youtube.player.loadVideoById(id);
            youtube.videoId = id;
            youtube.videoTitle = title;

            youtube.player.playVideo();

            $http.put('/v1/video/now_playing',
                { name: title } ).then(
                function success(response) {
                    console.log(response);

                },
                function failure(response) {
                    console.log(response);
                }
            );

            return youtube;
        } else
            return false;
    };

    this.listResults = function (data) {
        results.length = 0;

        for (var i = data.items.length - 1; i >= 0; i--) {
            results.push({
                id: data.items[i].id.videoId,
                title: data.items[i].snippet.title,
                description: data.items[i].snippet.description,
                thumbnail: data.items[i].snippet.thumbnails.default.url,
                author: data.items[i].snippet.channelTitle
            });
        }

        return results;
    };

    this.queueVideo = function (id, title, save) {
        if (youtube.videoId == null && youtube.player != null) {
            $log.info('Skipping default video');
            service.launchPlayer(id, title);
        } else {
            if (save) {
                $http.put(
                    '/v1/video/store',
                    {video_id: id, name: title}
                ).then(function success(response) {
                    console.log(response);
                });
            }
        }
    };

    this.archiveVideo = function (id, title) {
        $log.info('Archiving video: ' + title);
        $http.put(
            '/v1/video/archive',
            { video_id : id, name : title }
        );
    };

    this.deleteVideo = function (list, id) {
        if (list == 'upcoming') {
            $http.delete(
                '/v1/video/delete/' + id
            );
        }
    };

    this.getYoutube = function () {
        return youtube;
    };

    this.getResults = function () {
        return results;
    };

    this.getUpcoming = function () {
        return upcoming;
    };

    this.getHistory = function () {
        return history;
    };

    this.startPolling = function () {
        service.pollServer();
    };

    this.pollServer = function () {
        //$log.info('Polling server');

        if (youtube.state == 'playing' || youtube.state == 'paused') {
            stillPlaying = true;
        }

        $http.get('/v1/video/update?playing=' + (stillPlaying ? 'true' : 'false')).then(
            function success(response) {
                var updateUpcoming = response.data.payload.upcoming;
                var updateHistory  = response.data.payload.history;
                var now_playing    = response.data.payload.playing;

                if (upcoming.length > 0)
                    upcoming.splice(0,upcoming.length);

                for(var i = 0; i < updateUpcoming.length; i++) {
                    upcoming.push({
                        id: updateUpcoming[i].video_id,
                        title: updateUpcoming[i].name
                    });
                }

                if (history.length > 0)
                    history.splice(0,history.length);

                for(var i = 0; i < updateHistory.length; i++) {
                    history.push({
                        id: updateHistory[i].video_id,
                        title: updateHistory[i].name
                    });
                }
                
                youtube.videoTitle = now_playing.name;

                $timeout(service.pollServer, 1000);
            },
            function failure(response) {
                $log.error(response);
                $timeout(service.pollServer, 5000)
            }
        );
    };

    this.stopPlaying = function () {
        $http.delete('/v1/video/stop_playing').then(
            function success(response) {
                console.log(response);
            },
            function failure(response) {
                console.log(response);
            }
        );
    };

}]);

// Controller

app.controller('VideosController', function ($scope, $http, $log, VideosService) {

    init();

    function init() {
        $scope.youtube = VideosService.getYoutube();
        $scope.results = VideosService.getResults();
        $scope.upcoming = VideosService.getUpcoming();
        $scope.history = VideosService.getHistory();
        $scope.playlist = true;
        $scope.streaming = false;

        setTimeout(VideosService.startPolling, 1000);
        setTimeout(function() {
            $scope.castSupport  = VideosService.checkCastSupport();
        }, 500);
    }

    $scope.launch = function (id, title) {
        VideosService.launchPlayer(id, title);
        VideosService.archiveVideo(id, title);
        //$log.info('Launched id:' + id + ' and title:' + title);
    };

    $scope.queue = function (id, title) {
        VideosService.queueVideo(id, title, true);
        VideosService.deleteVideo('history', id);
        //$log.info('Queued id:' + id + ' and title:' + title);
    };

    $scope.delete = function (list, id) {
        VideosService.deleteVideo(list, id);
    };

    $scope.search = function () {
        $scope.searched = true;

        $http.get('https://www.googleapis.com/youtube/v3/search', {
                params: {
                    key: 'AIzaSyANLGjM3FH5DsqkkLHFO_K5QOb5SdF47qk',
                    type: 'video',
                    maxResults: '8',
                    part: 'id,snippet',
                    fields: 'items/id,items/snippet/title,items/snippet/description,items/snippet/thumbnails/default,items/snippet/channelTitle',
                    videoSyndicated: true, // Limit search to videos that are allowed to play outside youtube.com
                    q: $('#query').val()
                }
            })
            .success( function (data) {
                VideosService.listResults(data);
                $log.info(data);
            })
            .error( function () {
                $log.info('Search error');
            });
    };

    $scope.tabulate = function (state) {
        $scope.playlist = state;
    };

    $scope.startCast = function () {
        $log.info('Starting cast...');
        $scope.stream = {mode: 'cast'};
        $scope.streaming = true;

        if (VideosService.startCast() == false) {
            this.stopStream();
            alert('Please add videos to the playlist before casting');
        }
    };

    $scope.startHost = function () {
        $log.info('Entering host mode...');
        $scope.stream = {mode: 'host'};
        $scope.streaming = true;

        VideosService.bindPlayer('placeholder');
        VideosService.loadPlayer();
    };

    $scope.stopStream = function () {
        if ($scope.stream.mode == 'cast') {
            $log.info('Stopping cast...');

            VideosService.stopCast();
        } else if ($scope.stream.mode == 'host') {
            $log.info('Stopping host mode...');

            VideosService.unloadPlayer();
        } else {
            $log.info('Can\'t stop stream: unknown stream type');
        }

        if ($scope.streaming == true) {
            $scope.stream = {mode: 'unset'};
            $scope.streaming = false;
        }

        VideosService.stopPlaying();
    };
});
