<?php

namespace Theomessin\Argo;

use Exception;
use Illuminate\Support\Str;

class Yaml
{

    protected $validExtensions = [
        'blade.yaml',
    ];
    protected $view;
    protected $viewInstance;
    protected $fileName;
    protected $bladeFile;

    public function __construct($fileName = null)
    {
        $this->view = View();
        foreach ($this->validExtensions as $ext) {
            $this->view->addExtension($ext, 'blade');
        }
        if ($fileName) {
            $this->setFileName($fileName);
        }
    }

    public function setFileName($fileName)
    {
        $bladeFile = null;
        $baseName = basename($fileName);
        foreach ($this->validExtensions as $ext) {
            if (Str::endsWith($baseName, '.' . $ext)) {
                $bladeFile = Str::replaceLast('.' . $ext, '', $baseName);
                break;
            }
        }
        if (!$bladeFile) {
            $err = "Yaml blade file [$fileName] should end with " . implode(' or ', $this->validExtensions);
            throw new Exception($err);
        }
        $this->fileName = $fileName;
        $this->bladeFile = $bladeFile;
        $this->adjustViewLocations();
    }

    protected function adjustViewLocations()
    {
        $path = dirname($this->fileName);
        if ($path) {
            $this->view->addLocation($path);
        }
    }

    public function view()
    {
        if (! $this->viewInstance) {
            $this->viewInstance = $this->view->make($this->bladeFile);
        }
        return $this->viewInstance;
    }

    public function with($data)
    {
        $this->view()->with($data);
        return $this;
    }

    public function render($data = null)
    {
        return $this->view()->with($data)->render();
    }

    public function compile($data = null)
    {
        $compiledFilesPath = storage_path('argo');
        if (! file_exists($compiledFilesPath)) {
            mkdir($compiledFilesPath);
        }
        if (! is_writable($compiledFilesPath)) {
            $err = "Directory $compiledFilesPath is not accessible to create compiled file for " . $this->fileName;
            throw new Exception($err);
        }
        $output = $this->render($data);
        $compiledFile = storage_path('argo/' . $this->bladeFile . '_' . Str::uuid() . '.yaml');
        file_put_contents($compiledFile, $output);
        return $compiledFile;
    }
}
