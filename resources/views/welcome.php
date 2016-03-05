<!DOCTYPE html>
<html data-ng-app="BamiPlayerApp">

<head>
    <meta charset="utf-8">
    <title>Bami Player</title>
    <meta name="author" content="Thomas Wiringa">
    <meta name="description" content="Create and stream a YouTube playlist with ease">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css" type="text/css">
    <link rel="icon" href="/favicon.ico">
</head>

<body data-ng-controller="VideosController">

    <header>
        <h1>Bami<strong>Player</strong></h1>

        <form id="search" data-ng-submit="search()">
            <input id="query" name="q" type="text" placeholder="Search for a YouTube video" data-ng-model="query">
            <input id="submit" type="image" src="/img/search.png" alt="Search">
        </form>

        <nav ng-hide="streaming">
            <a id="cast" data-ng-click="startCast()" ng-show="castSupport">Cast</a>
            <a id="play" data-ng-click="startHost()">Host</a>
        </nav>

        <nav ng-show="streaming">
            <a id="stop" data-ng-click="stopStream()">Stop {{ stream.mode }}ing</a></a>
        </nav>
    </header>

    <div id="results">
        <div class="video" data-ng-repeat="video in results" data-ng-click="queue(video.id, video.title)">
            <img class="video-image" data-ng-src="{{ video.thumbnail }}">
            <p class="video-title">{{ video.title }}</p>
            <p class="video-author">{{ video.author }}</p>
            <p class="video-description">{{ video.description }}</p>
        </div>
    </div>

    <div id="player">
        <div id="placeholder">
            <div class="player-instructions">
                <h3>To start playing videos click on:</h3>

                <ul>
                    <li ng-hide="castSupport">Google Cast extension not found.</li>
                    <li ng-show="castSupport"><b data-ng-click="startCast()">Cast</b> - to cast to a Cast enabled device</li>
                    <li><b data-ng-click="startHost()">Host</b> - to play the playlist locally</li>
                </ul>
            </div>
        </div>
    </div>

    <div id="playlist">
        <p id="current">{{ youtube.videoTitle }}</p>

        <ol id="upcoming" data-ng-show="playlist">
            <li data-ng-repeat="video in upcoming">
                <p class="item-delete" data-ng-click="delete('upcoming', video.id)">delete</p>
                <p class="item-title">{{video.title}}</p><!-- Disable select to play for now data-ng-click="launch(video.id, video.title)" -->
            </li>
        </ol>

        <ol id="history" data-ng-hide="playlist">
            <li data-ng-repeat="video in history">
                <p class="item-title" data-ng-click="queue(video.id, video.title)">{{video.title}}</p>
            </li>
        </ol>

        <p id="tabs">
            <a ng-class="{on:playlist}" data-ng-click="tabulate(true)">Upcoming</a>
            <a ng-class="{on:!playlist}" data-ng-click="tabulate(false)">History</a>
        </p>
    </div>

    <footer>
        <em>Concept &amp; Design: <a href="http://lunamoonfang.nl">T. Wiringa</a></em>
        Built with <a href="http://angularjs.org/">AngularJS</a> and <a href="https://laravel.com/">Laravel</a>. Source code available on <a href="https://github.com/DuckThom/bami-player">GitHub</a>.
    </footer>

    <script src="/js/angular.min.js"></script>
    <script type="text/javascript" src="https://www.gstatic.com/cv/js/sender/v1/cast_sender.js"></script>
    <script src="/js/app.js"></script>
</body>
</html>
