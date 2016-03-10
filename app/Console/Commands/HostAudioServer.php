<?php declare(ticks = 1);

namespace App\Console\Commands;

use App\Video;
use Illuminate\Console\Command;

class HostAudioServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'server:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start playing YouTube videos (Requires mps-youtube)';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        pcntl_signal(SIGINT, function() {
            $this->info('Stopping server...');

            Video::stopPlaying();

            exit(0);
        });

        // Check if the playlist is being hosted somewhere
        if (!Video::isPlaying()) {
            // Check if mpsyt exists
            if (is_executable('/usr/bin/mpsyt') || is_executable('/usr/local/bin/mpsyt'))
            {
                \File::put(storage_path('app/server-is-playing'), 'lael');

                // Loop forever, use Ctrl-C to stop the command
                while (true)
                {
                    $this->info('Use Ctrl-C to stop the server');

                    // Get the upcoming list
                    $upcoming = Video::getUpcoming();

                    if (isset($upcoming[0]))
                    {
                        // Show which video we are about to play
                        $this->info('Now playing: ' . $upcoming[0]->name);

                        // Set the now playing text
                        Video::nowPlaying($upcoming[0]->name);

                        // Remove the video from upcoming
                        Video::archive($upcoming[0]->video_id);

                        // Execute mps-youtube to play the video from the command line
                        passthru('mpsyt playurl ' . $upcoming[0]->video_id);

                        // Sleep for 1 second
                        sleep(1);
                    } else
                    {
                        // No videos in upcoming
                        $this->info('Playlist empty... waiting 5 seconds...');

                        // Show the clients that the server is waiting for input
                        Video::nowPlaying('Server waiting for playlist input...');

                        // Sleep 5 seconds
                        sleep(5);
                    }
                }
            } else {
                $this->error("'mpsyt' not found in /usr/bin and /usr/local/bin");

                exit(1);
            }
        } else {
            $this->error("The playlist is already being hosted");

            exit(1);
        }

        // Try to delete the now playing file to be sure
        if (\File::exists(storage_path('app/server-is-playing')))
            \File::delete(storage_path('app/server-is-playing'));

        // Return true by default
        exit(0);
    }
}
