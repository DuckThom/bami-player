<!DOCTYPE html>
<html lang="en" data-ng-app="BamiPlayerApp">
    <head>
        <meta charset="utf-8">

        <title>Bami Player</title>

        <meta name="author" content="Thomas Wiringa">
        <meta name="description" content="Quickly create and stream a playlist of YouTube videos with ease">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="icon" href="/favicon.ico">

        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

        <!-- Home-made css -->
        <link rel="stylesheet" href="/css/app.css" type="text/css">

    </head>

    <body data-ng-controller="VideosController">

        <div class="container">
            <header>
                <nav class="navbar navbar-bamiplayer">
                    <div class="container-fluid">
                        <!-- Brand and toggle get grouped for better mobile display -->
                        <div class="navbar-header">
                            <h4 class="navbar-text">
                                Bami<strong>Player</strong>
                            </h4>
                        </div>

                        <!-- Collect the nav links, forms, and other content for toggling -->
                        <div class="collapse navbar-collapse">
                            <form class="navbar-form navbar-left" role="search" data-ng-submit="search()">
                                <div class="form-group">
                                    <input id="query" name="query" type="text" class="form-control" placeholder="Search">
                                </div>
                                <button type="submit" class="btn btn-default">Submit</button>
                            </form>

                            <ul class="nav navbar-nav navbar-right">
                                <li ng-hide="streaming" ng-if="!youtube.videoTitle"><a data-ng-click="startCast()" ng-show="castSupport">Cast</a></li>
                                <li ng-hide="streaming" ng-if="!youtube.videoTitle"><a data-ng-click="startHost()">Host</a></li>

                                <li ng-show="streaming"><a data-ng-click="stopStream()">Stop {{ stream.mode }}ing</a></a></li>
                            </ul>
                        </div><!-- /.navbar-collapse -->
                    </div><!-- /.container-fluid -->
                </nav>

                <div class="now-playing" ng-if="youtube.videoTitle">
                    <h4><div class='sp sp-wave'></div> {{ youtube.videoTitle }}</h4>
                </div>
            </header>

            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6 no-pad-right">
                        <div class="results">
                            <div class="video" ng-show="searched" ng-repeat="video in results" ng-click="queue(video.id, video.title)">
                                <img class="video-image" data-ng-src="{{ video.thumbnail }}">
                                <p class="video-title">{{ video.title }}</p>
                                <p class="video-author">{{ video.author }}</p>
                                <p class="video-description">{{ video.description }}</p>
                            </div>

                            <div class="search-instructions" ng-hide="searched">
                                <h4>Search for videos by filling out the search box<br /> and pressing Submit/Enter</h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 no-pad-left">
                        <div class="player">
                            <div id="placeholder">
                                <div class="player-instructions" ng-if="!youtube.videoTitle">
                                    <h3>To start playing videos click on:</h3>

                                    <ul>
                                        <li ng-hide="castSupport">Google Cast extension not found.</li>
                                        <li ng-show="castSupport"><b ng-click="startCast()">Cast</b> - to cast to a Cast enabled device</li>
                                        <li><b ng-click="startHost()">Host</b> - to play the playlist locally</li>
                                    </ul>
                                </div>

                                <div class="player-instructions" ng-if="youtube.videoTitle">
                                    <h3 ng-if="youtube.videoTitle" class="hosting-warning">This playlist is currently being hosted.</h3>

                                    <h3>Hosting here is unavailable.</h3>
                                </div>
                            </div>
                        </div>

                        <div class="playlist">

                            <div class="text-center" ng-if="youtube.voteskip.status == 4">
                                Skip this song?
                            </div>

                            <table class="table text-center" id="voteskip" ng-if="youtube.voteskip.status == 0 || youtube.voteskip.status == 1">
                                <tbody>
                                    <tr class="vote-skip" ng-if="youtube.voteskip.status == 0">
                                        <td ng-click="startVoteSkip()">Vote to skip</td>
                                    </tr>

                                    <tr class="vote-skip"  ng-if="youtube.voteskip.status == 1">
                                        <td ng-click="voteToSkip()">Vote to skip ({{youtube.voteskip.yes}} 'Yes' votes. Needed: {{youtube.voteskip.needed}})</td>
                                    </tr>
                                </tbody>
                            </table>

                            <table class="table" id="upcoming" ng-show="playlist">
                                <tbody>
                                    <tr ng-repeat="video in upcoming">
                                        <td>{{video.title}}</td>
                                        <td class="remove-item text-center" ng-click="delete('upcoming', video.id)"><i class="glyphicon glyphicon-remove"></i></td>
                                    </tr>
                                </tbody>
                            </table>

                            <table class="table" id="history" ng-hide="playlist">
                                <tbody>
                                    <tr ng-repeat="video in history">
                                        <td data-ng-click="queue(video.id, video.title)">{{video.title}}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="row tabs">
                                <div class="col-xs-6 no-pad-right">
                                    <a class="tab-btn" ng-class="{on:playlist}" ng-click="tabulate(true)">Upcoming</a>
                                </div>
                                <div class="col-xs-6 no-pad-left">
                                    <a class="tab-btn" ng-class="{on:!playlist}" ng-click="tabulate(false)">History</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <footer>
                <div class="pull-left hidden-sm hidden-xs">
                    Built with <a href="https://angularjs.org/">AngularJS</a> and <a href="https://laravel.com/">Laravel</a>. Source code available on <a href="https://github.com/DuckThom/bami-player">GitHub</a>.
                </div>
                <div class="pull-right">
                    <em>Made by: <a href="https://lunamoonfang.nl">T. Wiringa</a> - Original design by: <a href="https://jgthms.com/">J. Thomas</a></em>
                </div>
            </footer>
        </div>

        <?php if (env('DEMO_MODE', true)): ?>
            <div class="demo-block">
                <p>
                    This app is in demo mode which means that the database will be emptied at midnight UTC if there are no active users.
                </p>
            </div>
        <?php endif; ?>

        <script type="text/javascript" src="//code.jquery.com/jquery-2.1.1.min.js"></script>
        <script src="//cdn.jsdelivr.net/fingerprintjs2/1.1.1/fingerprint2.min.js"></script>
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
        <script src="//www.gstatic.com/cv/js/sender/v1/cast_sender.js"></script>

        <script src="/js/angular.min.js"></script>
        <script src="/js/app.js"></script>
    </body>
</html>