<?php

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

                // Execute mps-youtube to play the video from the command line
                exec('mpsyt playurl ' . $upcoming[0]->video_id);

                // Remove the video from upcoming
                Video::archive($upcoming[0]->video_id);

                // Sleep for 1 second
                sleep(1);
            } else
            {
                // No videos in upcoming
                $this->info('Playlist empty... waiting 5 seconds...');

                // Sleep 5 seconds
                sleep(5);
            }
        }
    }
}
