<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Http\File;

use Kant\Kant;
use Kant\Support\Arr;

class UploadedFile extends BaseUploadedFile
{

    /**
     * Get the fully qualified path to the file.
     *
     * @return string
     */
    public function path()
    {
        return $this->getRealPath();
    }

    /**
     * Get the file's extension.
     *
     * @return string
     */
    public function extension()
    {
        return $this->guessExtension();
    }

    /**
     * Get the file's extension supplied by the client.
     *
     * @return string
     */
    public function clientExtension()
    {
        return $this->guessClientExtension();
    }

    /**
     * Get a filename for the file that is the MD5 hash of the contents.
     *
     * @param string $path            
     * @return string
     */
    public function hashName($path = null)
    {
        if ($path) {
            $path = rtrim($path, '/') . '/';
        }
        
        return $path . md5_file($this->path()) . '.' . $this->extension();
    }

    /**
     * Store the uploaded file on a filesystem disk.
     *
     * @param string $path            
     * @param array $options            
     * @return string|false
     */
    public function store($path, $options = [])
    {
        return $this->storeAs($path, $this->hashName(), $this->parseOptions($options));
    }

    /**
     * Store the uploaded file on a filesystem disk with public visibility.
     *
     * @param string $path            
     * @param array $options            
     * @return string|false
     */
    public function storePublicly($path, $options = [])
    {
        $options = $this->parseOptions($options);
        
        $options['visibility'] = 'public';
        
        return $this->storeAs($path, $this->hashName(), $options);
    }

    /**
     * Store the uploaded file on a filesystem disk with public visibility.
     *
     * @param string $path            
     * @param string $name            
     * @param array $options            
     * @return string|false
     */
    public function storePubliclyAs($path, $name, $options = [])
    {
        $options = $this->parseOptions($options);
        
        $options['visibility'] = 'public';
        
        return $this->storeAs($path, $name, $options);
    }

    /**
     * Store the uploaded file on a filesystem disk.
     *
     * @param string $path            
     * @param string $name            
     * @param array $options            
     * @return string|false
     */
    public function storeAs($path, $name, $options = [])
    {
        $options = $this->parseOptions($options);
        
        $disk = Arr::pull($options, 'disk');
        
        return Kant::$app->store->disk($disk)->putFileAs($path, $this, $name, $options);
    }

    /**
     * Create a new file instance from a base instance.
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file            
     * @param bool $test            
     * @return static
     */
    public static function createFromBase(UploadedFile $file, $test = false)
    {
        return $file instanceof static ? $file : new static($file->getPathname(), $file->getClientOriginalName(), $file->getClientMimeType(), $file->getClientSize(), $file->getError(), $test);
    }

    /**
     * Parse and format the given options.
     *
     * @param array|string $options            
     * @return array
     */
    protected function parseOptions($options)
    {
        if (is_string($options)) {
            $options = [
                'disk' => $options
            ];
        }
        
        return $options;
    }
}
