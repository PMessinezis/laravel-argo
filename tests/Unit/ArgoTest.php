<?php

namespace Theomessin\Argo\Tests;

use Argo;
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
    public function return_execution_output()
    {
        $file = $this->yamlFiles_path('example-hello-world.yaml');
        $resource_id = Argo::submit($file);

        if ($resource_id) {
            //Act: get status until not running
            Argo::wait($resource_id, 40);
            $output = Argo::output($resource_id);
            //Assert: status is not null
            $this->assertStringContainsString('hello world', $output);
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
            $output = Argo::output($resource_id);
            //Assert: status is not null
            $this->assertStringContainsString('Kati trehei ne tin Mary', $output);
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
            $output = Argo::output($resource_id);
            //Assert: status is not null
            $this->assertStringContainsString('Kati trehei ne ton Costa', $output);
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

    protected function tearDown(): void
    {
        Argo::deleteAll();
    }
}
