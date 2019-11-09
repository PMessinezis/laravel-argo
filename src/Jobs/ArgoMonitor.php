<?php

namespace Theomessin\Argo\Jobs;

use Argo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Theomessin\Argo\Events\ArgoFinished;

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
        // This approach might block the queue - should be tested during integration stress test
        Argo::wait($this->resource_id);
        event(new ArgoFinished($this->resource_id));

        // This approach checks if finished and if not resubmits the job after 10''
        // ( the period is arbitrary - it could be given/overriden as constructor argument instead)
        // $status = Argo::status($this->resource_id);
        // switch ($status) {
        //     case 'Running':
        //         self::dispatch($this->resource_id)->delay(now()->addSeconds(10));
        //         break;
        //     default:
        //         event(new ArgoFinished($this->resource_id));
        // }
    }
}
