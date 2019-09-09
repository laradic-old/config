<?php

namespace Laradic\Config;


use Laradic\Support\Macros\Arr\Merge;
use Illuminate\Support\Arr;

class Repository extends \Illuminate\Config\Repository implements \Laradic\Contracts\Config\Repository
{
    /** @var ConfigValueCompiler */
    protected $compiler;


    public function __construct(array $items = [])
    {
        parent::__construct($items);
    }

    public function get($key, $default = null)
    {
        $value = parent::get($key, $default);
        $value = $this->process($value, $key, $default);
        return $value;
    }

    public function raw($key, $default = null)
    {
        return parent::get($key, $default);
    }

    public function process($value, $key = null, $default = null)
    {
        $context = $this->getContext();
        if (is_string($value)) {
            $value = $this->compiler->compileString($value, $context);
        }
        if (is_array($value)) {
            foreach ($value as $key => &$val) {
//                $value[ $key ] = $this->process($val);
                $val = $this->process($val);
            }
        }
        return $value;
    }

    public function getContext()
    {
        $context = collect($this->all())->transform(function ($value) {
            if (Arr::accessible($value)) {
                $value = new static($value);
            }
            return $value;
        })->all();
        return $context;
    }

    public function merge($data)
    {
        $merger      = new Merge();
        $this->items = $merger()($this->items, $data);
        return $this;
    }

    public function mergeAt($key, $data)
    {
        $merger = new Merge();
        $value  = $this->raw($key, []);
        $value  = $merger()($value, $data);
        $this->set($key, $value);
        return $this;
    }

    /**
     * setValueCompiller method
     *
     * @param ConfigValueCompiler
     */
    public function setValueCompiller($compiler)
    {
        $this->compiler = $compiler;
        return $this;
    }
}
