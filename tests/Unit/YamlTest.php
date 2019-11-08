<?php

namespace Theomessin\Argo;

use Theomessin\Argo\Tests\TestCase;
use Theomessin\Argo\Yaml;

class YamlTest extends TestCase
{
    protected function createHelloWorld()
    {
        $fileName = __DIR__ . '/yamlFiles/hello-world.blade.yaml';
        $content = <<<'YAML'
apiVersion: argoproj.io/v1alpha1
kind: Workflow                  # new type of k8s spec
metadata:
  generateName: hello-world-    # name of the workflow spec
spec:
  entrypoint: whalesay          # invoke the whalesay template
  templates:
  - name: whalesay              # name of the template
    container:
      image: docker/whalesay
      command: [cowsay]
      args: ["{{ $message }}"]
      resources:                # limit the resources
        limits:
          memory: 32Mi
          cpu: 100m
YAML;
        file_put_contents($fileName, $content);
        return $fileName;
    }

    /** @test */
    public function yaml_render_blade_yaml_file(Type $var = null)
    {
        //Arrange: create a .blade.yaml file
        $fileName  = $this->createHelloWorld();

        //Arrange: get a Yaml object
        $yaml = new Yaml($fileName);

        //Arrange: setup environment variables
        $data = ['message' => 'my-test-message'];

        //Act: compile to string
        $output = $yaml->render($data);

        //Assert: output contains first line of file
        $this->assertStringContainsString('apiVersion: argoproj.io/v1alpha1', $output);

        //Assert: output contains first line of file
        $this->assertStringContainsString('my-test-message', $output);
    }

    /** @test */
    public function yaml_compile_to_storage(Type $var = null)
    {
        //Arrange: create a .blade.yaml file
        $fileName  = $this->createHelloWorld();

        //Arrange: get a Yaml object
        $yaml = new Yaml($fileName);

        //Arrange: setup environment variables
        $data = ['message' => 'my-test-message'];

        //Act: compile to file and get compiled file contents
        $compiledFile = $yaml->compile($data);
        $output = file_get_contents($compiledFile);

        //Assert: output contains first line of file
        $this->assertStringContainsString('apiVersion: argoproj.io/v1alpha1', $output);

        //Assert: output contains first line of file
        $this->assertStringContainsString('my-test-message', $output);
    }
}
