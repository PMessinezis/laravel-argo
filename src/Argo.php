<?php

namespace Theomessin\Argo;

use Exception;
use Illuminate\Support\Str;
use Theomessin\Argo\Jobs\ArgoMonitor;
use Theomessin\Argo\Yaml;

class Argo
{
    public function exists()
    {
        return true;
    }

    protected function exec($cmd, $escapeCommand = true)
    {
        if ($escapeCommand) {
            $cmd = escapeshellcmd($cmd);
        }
        return shell_exec($cmd . ' 2>&1');
    }

    public function compile($file, $data)
    {
         $yaml = new Yaml($file);
         $compiled = $yaml->compile($data);
         return $compiled;
    }

    public function submit($file, array $commandLineParameters = [], $bladeContext = null)
    {
        $tmpfile = null;
        if (Str::endsWith($file, '.blade.yaml')) {
            $tmpfile = $this->compile($file, $bladeContext);
            $file = $tmpfile;
        }
        $params = '';
        if ($commandLineParameters) {
            foreach ($commandLineParameters as $param => $value) {
                $params .= ' -p ' . $param . "='$value' " ;
            }
        }
        $cmd = 'argo submit ' . $file . $params . ' -o json';
        $output = $this->exec($cmd);
        try {
            $json = json_decode($output);
            if (is_object($json)) {
                $id = $json->metadata->name;
            } else {
                $id = null;
            }
        } catch (Exception $e) {
            $id = null;
        }
        if (!$id) {
            throw new Exception($output);
        }
        // commented out during development jic for debuging blade.yaml
        // if ($tmpfile) {
        //     unlink($tmpfile) ;
        // }
        return $id;
    }

    public function get($id)
    {
        $cmd = 'argo get ' . $id . ' -o json';
        $output = $this->exec($cmd);
        $json = json_decode($output);
        return $json;
    }

    public function status($id)
    {
        if ($id) {
            $json = $this->get($id);
            if (is_object($json)) {
                $status = $json->status->phase;
            } else {
                throw new Exception("Resource ID $id not found");
            }
        } else {
            throw new Exception('Resource ID not provided');
        }
        return $status;
    }

    public function finished($id)
    {
        return ($this->status() != 'Running');
    }

    public function delete($id)
    {
        $cmd = 'argo delete ' . $id;
        $output = $this->exec($cmd);
    }

    public function deleteAll()
    {
        $cmd = 'argo delete --all' ;
        $output = $this->exec($cmd);
    }

    public function list()
    {
        $cmd = 'argo list -o wide' ;
        return $this->exec($cmd);
    }

    public function output($id)
    {
        if ($id) {
            $json = $this->get($id);
            if (is_object($json)) {
                $cmd = 'argo logs --no-color ' . $id . ' || argo logs --no-color -w ' . $id;
                $output = $this->exec($cmd, false);
                return $output;
            } else {
                throw new Exception("Resource ID $id not found");
            }
        } else {
            throw new Exception('Resource ID not provided');
        }
        return null;
    }

    public function wait($id, $maxWaitSeconds = 0)
    {
        if ($id) {
            $c = 1;
            while (($s = $this->status($id)) == 'Running') {
                sleep(1);
                $c++;
                if ($maxWaitSeconds > 0 && $c > $maxWaitSeconds) {
                    break;
                }
            }
        }
    }

    public function monitor($id)
    {
        ArgoMonitor::dispatch($id);
    }
}
