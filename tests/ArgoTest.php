<?php

namespace Theomessin\Argo\Tests;

use Theomessin\Argo\Tests\TestCase;

class ArgoTest extends TestCase
{
    /** @test */
    public function there_is_a_facade()
    {
        $this->assertTrue(\Argo::exists());
    }
}
