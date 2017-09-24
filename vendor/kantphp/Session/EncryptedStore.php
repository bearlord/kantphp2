<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @original-author Laravel/Symfony
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Session;

use SessionHandlerInterface;
use Kant\Contracts\Encryption\DecryptException;
use Kant\Contracts\Encryption\Encrypter as EncrypterContract;

class EncryptedStore extends Store
{

    /**
     * The encrypter instance.
     *
     * @var \Kant\Contracts\Encryption\Encrypter
     */
    protected $encrypter;

    /**
     * Create a new session instance.
     *
     * @param string $name            
     * @param \SessionHandlerInterface $handler            
     * @param \Kant\Contracts\Encryption\Encrypter $encrypter            
     * @param string|null $id            
     * @return void
     */
    public function __construct($name, SessionHandlerInterface $handler, EncrypterContract $encrypter, $id = null)
    {
        $this->encrypter = $encrypter;
        
        parent::__construct($name, $handler, $id);
    }

    /**
     * Prepare the raw string data from the session for unserialization.
     *
     * @param string $data            
     * @return string
     */
    protected function prepareForUnserialize($data)
    {
        try {
            return $this->encrypter->decrypt($data);
        } catch (DecryptException $e) {
            return json_encode([]);
        }
    }

    /**
     * Prepare the serialized session data for storage.
     *
     * @param string $data            
     * @return string
     */
    protected function prepareForStorage($data)
    {
        return $this->encrypter->encrypt($data);
    }

    /**
     * Get the encrypter instance.
     *
     * @return \Kant\Contracts\Encryption\Encrypter
     */
    public function getEncrypter()
    {
        return $this->encrypter;
    }
}
