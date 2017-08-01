<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class InstagramCrawler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'instagram:crawler';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Instagram Crawler';

    /**
     * Create a new command instance.
     *
     * @return void
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
        //

        $this->line("Some text");
        $this->info("Hey, watch this !");
        $this->comment("Just a comment passing by");
        $this->question("Why did you do that?");
        $this->error("Ops, that should not happen.");
    }
}
