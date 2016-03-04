var app = angular.module('JukeTubeApp', []);

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

    $window.onYouTubeIframeAPIReady = function () {
        $log.info('Youtube API is ready');
        youtube.ready = true;
        service.bindPlayer('placeholder');
        service.loadPlayer();

        $rootScope.$apply();
    };

    function onYoutubeReady (event) {
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

    this.loadPlayer = function () {
        if (youtube.ready && youtube.playerId) {
            if (youtube.player) {
                youtube.player.destroy();
            }
            youtube.player = service.createPlayer();
        }
    };

    this.launchPlayer = function (id, title) {
        youtube.player.loadVideoById(id);
        youtube.videoId = id;
        youtube.videoTitle = title;

        return youtube;
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
        if (save) {
            $http.put(
                '/v1/video/store',
                {video_id: id, name: title}
            );
        }

        if (youtube.player.videoId == 'S5PvBzDlZGs') {
            service.launchPlayer(upcoming[0].id, upcoming[0].name);
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

        $http.get('/v1/video/update').then(
            function success(response) {
                var updateUpcoming = response.data.payload.upcoming;
                var updateHistory  = response.data.payload.history;

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

                $timeout(service.pollServer, 1000);
            },
            function failure(response) {
                $log.error(response);
                $timeout(service.pollServer, 5000)
            }
        );
    }

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

        VideosService.startPolling();
    }

    $scope.launch = function (id, title) {
        VideosService.launchPlayer(id, title);
        VideosService.archiveVideo(id, title);
        $log.info('Launched id:' + id + ' and title:' + title);
    };

    $scope.queue = function (id, title) {
        VideosService.queueVideo(id, title, true);
        VideosService.deleteVideo('history', id);
        $log.info('Queued id:' + id + ' and title:' + title);
    };

    $scope.delete = function (list, id) {
        VideosService.deleteVideo(list, id);
    };

    $scope.search = function () {
        $http.get('https://www.googleapis.com/youtube/v3/search', {
                params: {
                    key: 'AIzaSyANLGjM3FH5DsqkkLHFO_K5QOb5SdF47qk',
                    type: 'video',
                    maxResults: '8',
                    part: 'id,snippet',
                    fields: 'items/id,items/snippet/title,items/snippet/description,items/snippet/thumbnails/default,items/snippet/channelTitle',
                    q: this.query
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
});
