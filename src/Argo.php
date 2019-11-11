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

    protected function exec($cmd, $escapeCommand = true, $withFlags = true)
    {
        if ($escapeCommand) {
            $cmd = escapeshellcmd($cmd);
        }
        return shell_exec($cmd . ( $withFlags ? $this->flags() : '') . ' 2>&1');
    }

    public function flags()
    {
        $flags = '';
        foreach (config('argo.global-flags') as $flag => $value) {
            if ($value !== null) {
                if (is_array($value)) {
                    foreach ($value as $v) {
                        $flags .= " --$flag='$v'";
                    }
                } else {
                    $flags .= " --$flag='$value'";
                }
            }
        }
        return $flags;
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
        $result = $this->exec($cmd);
        try {
            $json = json_decode($result);
            if (is_object($json)) {
                $id = $json->metadata->name;
            } else {
                $id = null;
            }
        } catch (Exception $e) {
            $id = null;
        }
        if (!$id) {
            throw new Exception($result);
        }
        // comment out during development for debuging output of compile
        if ($tmpfile) {
            unlink($tmpfile) ;
        }
        return $id;
    }

    public function get($id)
    {
        $cmd = 'argo get ' . $id . ' -o json';
        $result = $this->exec($cmd);
        $json = json_decode($result);
        return $json;
    }

    public function status($id)
    {
        if ($id) {
            $json = $this->get($id);
            if (is_object($json)) {
                if (isset($json->status->phase)) {
                    $status = $json->status->phase;
                } else {
                    dump($json);
                    dump($this->list);
                    throw new Exception("Unexpected result from argo get $id");
                }
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
        $this->exec($cmd);
    }

    public function deleteAll()
    {
        $cmd = 'argo delete --all' ;
        $this->exec($cmd);
    }

    public function list()
    {
        $cmd = 'argo list -o wide' ;
        return $this->exec($cmd);
    }

    public function logs($id)
    {
        if ($id) {
            $json = $this->get($id);
            if (is_object($json)) {
                $cmd = 'argo logs ' . $id . ' --no-color ' . $this->flags() ;
                $cmd .= ' || ' . $cmd . ' -w ';
                $logs = $this->exec($cmd, false, false);
                return $logs;
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
