<?php
namespace Kant\Session;

use SessionHandlerInterface;
use Kant\Filesystem\Filesystem;

// use Symfony\Component\Finder\Finder;
class FileSessionHandler implements SessionHandlerInterface
{

    /**
     * The filesystem instance.
     *
     * @var \Kant\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The path where sessions should be stored.
     *
     * @var string
     */
    protected $path;

    /**
     * The number of minutes the session should be valid.
     *
     * @var int
     */
    protected $lifetime;

    /**
     * Create a new file driven handler instance.
     *
     * @param \Kant\Filesystem\Filesystem $files            
     * @param string $path            
     * @param int $minutes            
     * @return void
     */
    public function __construct(Filesystem $files, $path, $lifetime)
    {
        $this->path = $path;
        $this->files = $files;
        $this->lifetime = $lifetime;
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public function open($savePath, $sessionName)
    {
        return true;
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public function close()
    {
        return true;
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public function read($sessionId)
    {
        if ($this->files->exists($path = $this->path . $sessionId)) {
            if (filemtime($path) >= time() - $this->lifetime) {
                return $this->files->get($path);
            }
        }
        
        return '';
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public function write($sessionId, $data)
    {
        $this->files->put($this->path . $sessionId, $data, true);
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public function destroy($sessionId)
    {
        $this->files->delete($this->path . $sessionId);
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public function gc($lifetime)
    {
        $this->files->cleanDirectoryByFilemtime($this->path, $this->lifetime);
    }
}
