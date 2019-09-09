<?php

namespace Laradic\Config;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use Illuminate\View\Compilers\BladeCompiler;
use Throwable;

class ConfigValueCompiler
{

    /** @var \Illuminate\View\Compilers\BladeCompiler */
    protected $compiler;

    /** @var \Illuminate\Filesystem\Filesystem */
    protected $fs;

    /** @var string */
    protected $cachePath;

    /** @var Repository */
    private $repository;

    static $current;

    public function __construct(Repository $repository)
    {
        $this->fs         = new \Illuminate\Filesystem\Filesystem();
        $this->cachePath  = '/tmp/' . str_random(20);
        $this->repository = $repository;
    }


    protected function getCompiler()
    {
        if ( ! isset($this->compiler)) {
            if ($this->fs->exists($this->cachePath) === false) {
                $this->fs->makeDirectory($this->cachePath);
            }
            $this->compiler = new BladeCompiler($this->fs, $this->cachePath);
            $this->compiler->directive('get', function ($params) {
                $params = array_map('trim', explode(',', $params)); // trim($params[1], '"\'')
                $params = array_map(function ($value) {
                    if (Str::startsWith($value, '$') && array_key_exists($varKey = substr($value, 1), $this->vars)) {
                        $value = $this->vars[ $varKey ];
                    }
                    if ($value instanceof Arrayable) {
                        $value = $value->toArray();
                    }
                    if ($value instanceof \Illuminate\Contracts\Config\Repository) {
                        $value = $value->all();
                    }
                    return $value;
                }, $params);

                return $this->get($params[ 0 ], $params[ 1 ] ?? null);
            });
        }
        return $this->compiler;
    }

    protected $vars = [];

    /**
     * {@inheritdoc}
     */
    public function compileString($string, $vars = [])
    {
        if (empty($vars)) {
            return $this->getCompiler()->compileString($string);
        }
        $this->vars = $vars;
        $fileName   = uniqid('compileString', true) . '.php';
        $filePath   = $this->cachePath . DIRECTORY_SEPARATOR . $fileName;

        $pattern = '/(?<!@)get\((.*?)(,|\))/';
        $res     = preg_match_all($pattern, $string, $matches);
        if ($res > 0) {
            if ( ! Str::startsWith($param = $matches[ 1 ][ 0 ], [ '\'', '"' ])) {
                $param  = '"' . $param . '"';
                $string = preg_replace($pattern, 'get(' . $param . '$2', $string);
            }
        }
        $string = preg_replace($pattern, '\$__get($1$2', $string);

        $string = $this->getCompiler()->compileString($string);
        $this->fs->put($filePath, $string);
        try {
            $compiledString = $this->getCompiledContent($filePath, $vars);
        }
        catch (Throwable $e) {
            $this->fs->delete($filePath);
            throw $e;
        }
        $this->fs->delete($filePath);
        return $compiledString;
    }

    /**
     * getCompiledContent method.
     *
     * @param       $filePath
     * @param array $vars
     *
     * @return string
     */
    protected function getCompiledContent($filePath, array $vars = [])
    {
        if (is_array($vars) && ! empty($vars)) {
            extract($vars, EXTR_OVERWRITE);
        }
        $__get = function ($key, $default = null) {
            return $this->get($key, $default);
        };

        ob_start();
        $array  = require $filePath;
        $string = ob_get_contents();
        ob_end_clean();
        return $string !== '' || !is_array($array) ? $string : $array;
    }

    public function get($key, $default = null)
    {
        $value = $this->repository->get($key, $default);
        if (is_array($value)) {
            $value = '<?php return ' . var_export($value, true) . ';';
        }
        return $value;
    }
}
