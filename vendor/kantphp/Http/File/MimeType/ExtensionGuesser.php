<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @original-author Laravel/Symfony
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Http\File\MimeType;

/**
 * A singleton mime type to file extension guesser.
 *
 * A default guesser is provided.
 * You can register custom guessers by calling the register()
 * method on the singleton instance:
 *
 * $guesser = ExtensionGuesser::getInstance();
 * $guesser->register(new MyCustomExtensionGuesser());
 *
 * The last registered guesser is preferred over previously registered ones.
 */
class ExtensionGuesser implements ExtensionGuesserInterface
{

    /**
     * The singleton instance.
     *
     * @var ExtensionGuesser
     */
    private static $instance = null;

    /**
     * All registered ExtensionGuesserInterface instances.
     *
     * @var array
     */
    protected $guessers = array();

    /**
     * Returns the singleton instance.
     *
     * @return ExtensionGuesser
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }

    /**
     * Registers all natively provided extension guessers.
     */
    private function __construct()
    {
        $this->register(new MimeTypeExtensionGuesser());
    }

    /**
     * Registers a new extension guesser.
     *
     * When guessing, this guesser is preferred over previously registered ones.
     *
     * @param ExtensionGuesserInterface $guesser            
     */
    public function register(ExtensionGuesserInterface $guesser)
    {
        array_unshift($this->guessers, $guesser);
    }

    /**
     * Tries to guess the extension.
     *
     * The mime type is passed to each registered mime type guesser in reverse order
     * of their registration (last registered is queried first). Once a guesser
     * returns a value that is not NULL, this method terminates and returns the
     * value.
     *
     * @param string $mimeType
     *            The mime type
     *            
     * @return string The guessed extension or NULL, if none could be guessed
     */
    public function guess($mimeType)
    {
        foreach ($this->guessers as $guesser) {
            if (null !== $extension = $guesser->guess($mimeType)) {
                return $extension;
            }
        }
    }
}
