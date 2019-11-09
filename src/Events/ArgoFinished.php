<?php

namespace Theomessin\Argo\Events;

class ArgoFinished
{
    public $resource_id;

    /**
     * Create a new event instance.
     *
     * @param  $resource_id
     * @return void
     */
    public function __construct($resource_id)
    {
        $this->resource_id = $resource_id;
    }
}
