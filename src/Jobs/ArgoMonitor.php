<?php

namespace Theomessin\Argo\Jobs;

use Argo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ArgoMonitor implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $resource_id;

    public function __construct($resource_id)
    {
        $this->resource_id = $resource_id;
    }

    public function handle()
    {
        $status = Argo::status($this->resource_id);
        switch ($status) {
            case 'Running':
                self::dispatch($this->resource_id)->delay(now()->addMinute(1));
                break;
            default:
                // fire an event probably
        }
    }
}
