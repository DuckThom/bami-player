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
        while (true)
        {
            $upcoming = Video::getUpcoming();

            if (isset($upcoming[0]))
            {
                $this->info('Now playing: ' . $upcoming[0]->name);
                exec('mpsyt playurl ' . $upcoming[0]->video_id);
                sleep(1);
            } else
            {
                $this->info('Playlist empty... waiting 5 seconds...');
                sleep(5);
            }
        }
    }
}
