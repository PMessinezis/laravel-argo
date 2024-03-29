<?php

namespace Theomessin\Argo\Tests;

use Argo;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Theomessin\Argo\Events\ArgoFinished;
use Theomessin\Argo\Jobs\ArgoMonitor;
use Theomessin\Argo\Tests\TestCase;

class ArgoTest extends TestCase
{
    protected function yamlFiles_path($file = null)
    {
        $path = __DIR__ . '/yamlFiles/' . $file;
        return realpath($path);
    }

    /** @test */
    public function there_is_a_facade()
    {
        $this->assertTrue(\Argo::exists());
    }

    /** FOLLLOWINF TESTS DEPEND ON HAVING ARGO INSTALLED AND OPERATIONAL UNDER THE ACCOUNT OF THE TESTER */
    /** AND EXAMPLE ARGO .yaml FILES UNDER __DIR__/yamlFiles */

    /** @test */
    public function submit_returns_resource_ID()
    {
        //Act: Submit example .yaml file
        $file = $this->yamlFiles_path('example-hello-world.yaml');
        $resource_id = Argo::submit($file);

        //Assert: id is not null
        $this->assertNotNull($resource_id);
    }

    /** @test */
    public function status_returns_status_of_submited_resource()
    {
        //Arrange: Submit example .yaml file
        $file = $this->yamlFiles_path('example-hello-world.yaml');
        $resource_id = Argo::submit($file);

        if ($resource_id) {
            //Act: get status
            $status = Argo::status($resource_id);

            //Assert: status is not null
            $this->assertNotNull($status);
        } else {
            $this->fail('No resource ID returned from submit');
        }
    }

    /** @test */
    public function return_execution_logs()
    {
        $file = $this->yamlFiles_path('example-hello-world.yaml');
        $resource_id = Argo::submit($file);

        if ($resource_id) {
            //Act: get status until not running
            Argo::wait($resource_id, 40);
            $logs = Argo::logs($resource_id);

            //Assert: logs contain the string
            $this->assertStringContainsString('hello world', $logs ?? '');
        } else {
            $this->fail('No resource ID returned from submit');
        }
    }

    /** @test */
    public function submit_with_commandLine_parameters()
    {
        $file = $this->yamlFiles_path('example-parameters.yaml');
        $params = [
            'message' => 'Kati trehei ne tin Mary',
        ];
        $resource_id = Argo::submit($file, $params);

        if ($resource_id) {
            //Act: get status until not running
            Argo::wait($resource_id, 40);
            $logs = Argo::logs($resource_id);

            //Assert: logs contain the string
            $this->assertStringContainsString('Kati trehei ne tin Mary', $logs ?? '');
        } else {
            $this->fail('No resource ID returned from submit');
        }
    }

    /** @test */
    public function submit_with_blade_context()
    {
        $file = $this->yamlFiles_path('example-parameters.blade.yaml');
        $data = [
            'message' => 'Kati trehei ne ton Costa',
        ];
        $resource_id = Argo::submit($file, [], $data);
        if ($resource_id) {
            //Act: get status until not running
            Argo::wait($resource_id, 40);
            $logs = Argo::logs($resource_id);

            //Assert: logs contain the string
            $this->assertStringContainsString('Kati trehei ne ton Costa', $logs ?? '');
        } else {

            $this->fail('No resource ID returned from submit');
        }
    }

        /** @test */
    public function argo_list()
    {
        $file = $this->yamlFiles_path('example-hello-world.yaml');
        $resource_id = Argo::submit($file);

        if ($resource_id) {
            $list = Argo::list();
            //Assert: status is not null
            $this->assertStringContainsString($resource_id, $list);
        } else {
            $this->fail('No resource ID returned from submit');
        }
    }

    /** @test */
    public function monitoring_job_is_queued()
    {
        Queue::fake();

        // //Arrange: submit a workflow
        $file = $this->yamlFiles_path('example-hello-world.yaml');
        $resource_id = Argo::submit($file);

        //Act: start monitoring
        Argo::monitor($resource_id);

        //Assert:
        Queue::assertPushed(ArgoMonitor::class, function ($job) use ($resource_id) {
            return $job->resource_id === $resource_id;
        });
    }

    //    /** @test */ commented out since approach of job changed to wait instead of resubmitting
    public function monitoring_job_is_re_submitted_if_workflow_is_running()
    {
        Queue::fake();

        //Arrange: submit a workflow that takes a minute to complete
        $file = $this->yamlFiles_path('example-sleep.yaml');
        $seconds = 60;
        $resource_id = Argo::submit($file, compact('seconds'));

        //Act: start monitoring
        Argo::monitor($resource_id);

        //Assert: job pushed on queue once for this resource_id
        Queue::assertPushed(ArgoMonitor::class, 1);
        Queue::assertPushed(ArgoMonitor::class, function ($job) use ($resource_id) {
            return $job->resource_id === $resource_id;
        });

        //Act: handle job - it should resubmit itself since workflow is still running
        $job = new ArgoMonitor($resource_id);
        $job->handle();

        //Assert: job pushed again on queue
        Queue::assertPushed(ArgoMonitor::class, 2);
    }

    /** @test */
    public function event_is_fired_when_workflow_finishes()
    {
        Event::fake();

        //Arrange: submit a workflow that takes 5 seconds to complete and wait to finish
        $file = $this->yamlFiles_path('example-sleep.yaml');
        $seconds = 5;
        $resource_id = Argo::submit($file, compact('seconds'));
        Argo::wait($resource_id);

        //Act: execute the monitoring job
        $job = new ArgoMonitor($resource_id);
        $job->handle();

        //Assert: Event was fired
        Event::assertDispatched(ArgoFinished::class, function ($e) use ($resource_id) {
            return $e->resource_id === $resource_id;
        });
    }

    protected function tearDown(): void
    {
        Argo::deleteAll();
    }
}
