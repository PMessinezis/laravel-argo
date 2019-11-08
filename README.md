# Usage :

```
Use Argo;
```

## submit

```
$id = Argo::submit($file,$argoCommandLineParams = [], $bladeContext = null);

```
$id will be used as parameter to subsequent methods that manage and query the status and output of the submited workflow. 

$bladeContext is used when the file name extension is `.blade.yaml`. Files with extension `.blafe.yaml ` are first autocompiled to a ***tempFile*.yaml** under `storage_path('argo')` and then the compiled file is submitted to argo. 

The optional arguments are expected to be associative arrays, e.g. :

```
$argoCommandLineParams = [ 
    'paramName1' => 'paramValue1' ,
    ...
];
``` 
and 
```
$bladeContext = [
    'variable1' => $variable1,
    ...
];

```

## status

```
$status = Argo::status($id);
```
Return value is one of `["Running", "Failed", "Succeeded"]` or null if something went wrong, e.g. file syntax validation failed

## wait
```
Argo::wait($id, $timeout = 0);
```
returns once the workflow execution is completed or, if timeout >0, when timeout expires. 

## output

```
$output = Argo::output($id);
```

## get

```
$obj = Argo::get($id);
```
returns all details about the specific resource as an object, e.g. 
```
{
  +"metadata": {
    +"name": "hello-world-parameters-d7fcc"
    +"generateName": "hello-world-parameters-"
    +"namespace": "default"
    +"selfLink": "/apis/argoproj.io/v1alpha1/namespaces/default/workflows/hello-world-parameters-d7fcc"
    +"uid": "16eb9aec-9260-40cf-8bbf-223448b60e94"
    +"resourceVersion": "191595"
    +"generation": 2
    +"creationTimestamp": "2019-11-08T16:42:59Z"
    +"labels": {
      +"workflows.argoproj.io/phase": "Running"
    }
  }
  +"spec": {
    +"templates": array:1 [
      0 => {
        +"name": "whalesay"
        +"inputs": {
          +"parameters": array:1 [
            0 => {
              +"name": "message"
            }
          ]
        }
        +"outputs": {}
        +"metadata": {}
        +"container": {
          +"name": ""
          +"image": "docker/whalesay"
          +"command": array:1 [
            0 => "cowsay"
          ]
          +"args": array:1 [
            0 => "{{inputs.parameters.message}}"
          ]
          +"resources": {}
        }
      }
    ]
    +"entrypoint": "whalesay"
    +"arguments": {
      +"parameters": array:1 [
        0 => {
          +"name": "message"
          +"value": "Kati trehei ne tin Mary"
        }
      ]
    }
  }
  +"status": {
    +"phase": "Running"
    +"startedAt": "2019-11-08T16:42:59Z"
    +"finishedAt": null
    +"nodes": {
      +"hello-world-parameters-d7fcc": {
        +"id": "hello-world-parameters-d7fcc"
        +"name": "hello-world-parameters-d7fcc"
        +"displayName": "hello-world-parameters-d7fcc"
        +"type": "Pod"
        +"templateName": "whalesay"
        +"phase": "Pending"
        +"startedAt": "2019-11-08T16:42:59Z"
        +"finishedAt": null
        +"inputs": {
          +"parameters": array:1 [
            0 => {
              +"name": "message"
              +"value": "Kati trehei ne tin Mary"
            }
          ]
        }
      }
    }
  }
}

```
## list

```
$text = Argo::list();
```
## blade.yaml
A yaml file that contains blade directives. Double braces that are part of argo syntax must be escaped with @, i.e. `"@{{inputs.parameters.message}}"`. Example :
```
apiVersion: argoproj.io/v1alpha1
kind: Workflow
metadata:
  generateName: hello-world-parameters-
spec:
  entrypoint: whalesay
  arguments:
    parameters:
    - name: message
      value: {{ $message }}

  templates:
  - name: whalesay
    inputs:
      parameters:
      - name: message       # parameter declaration
    container:
      image: docker/whalesay
      command: [cowsay]
      args: ["@{{inputs.parameters.message}}"]
```
