<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FetchWeeklyQuote extends Command
{
    protected $signature = 'quote:weekly';
    protected $description = 'Fetch weekly famous quote';

    public function handle()
    {
        $res = Http::get("https://zenquotes.io/api/random");

        if ($res->successful()) {
            $data = $res->json()[0];

            Cache::put("weekly_quote", [
                "text" => $data["q"],
                "author" => $data["a"],
            ], now()->addDays(8));

            $this->info("Weekly quote updated!");
        }
    }
}
