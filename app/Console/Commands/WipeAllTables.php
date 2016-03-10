<?php

namespace App\Console\Commands;

use App\Helpers\Video;
use App\History;
use App\Upcoming;
use App\Vote;
use App\Playing;
use Illuminate\Console\Command;

class WipeAllTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:nuke';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Nuke the database, used in the demo version';

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
        $this->info('Checking for active users...');

        if (Video::isPlaying())
        {
            $this->warn('A video is playing right now!');
            if ($this->confirm('It appears that someone it using the app right now. Are you sure you want to nuke it? [y|N]')) {
                self::nuke_db();
            } else {
                $this->info('Whew, crisis averted...');
            }
        } else {
            $this->info('No active users, nuking database...');

            self::nuke_db();
        }
    }

    /**
     * Perform the nuking
     */
    public function nuke_db()
    {
        $this->error('Code RED! A nuke is incoming in 3...');
        sleep(1);
        $this->error('2...');
        sleep(1);
        $this->error('1...');
        sleep(1);
        $this->error('BOOM');

        Vote::truncate();
        Playing::truncate();
        Upcoming::truncate();
        History::truncate();

        $this->info('Everything is gone... R.I.P.');
    }
}
