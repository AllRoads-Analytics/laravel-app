<?php

namespace App\Console\Commands;

use App\Services\BigQueryService;
use GuzzleHttp\Client as Guzzle;
use Illuminate\Console\Command;

class test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {
        $results = (new BigQueryService())->select(
            ['*'],
            'WHERE DATE(ts) > "2021-11-04" LIMIT 5'
        );

        foreach ($results as $row) {
            dump($row);
        }
    }

    protected function createTraffic() {
        // Guzzle
    }
}
