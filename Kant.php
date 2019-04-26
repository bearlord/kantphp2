<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant;

use Kant\Log\Logger;
use Kant\Exception\InvalidConfigException;
use Kant\Exception\InvalidParamException;

class Kant
{

    /**
     *
     * @var \Kant\Web\Application the application instancethe application instance
     */
    public static $app;

    /**
     *
     * @var array registered path aliases
     * @see getAlias()
     * @see setAlias()
     */
    public static $aliases = [
        '@kant' => __DIR__
    ];

    /**
     *
     * @var \Kant\Di\Container the dependency injection (DI) container used by [[createObject()]].
     *      You may use [[Container::set()]] to set up the needed dependencies of classes and
     *      their initial property values.
     * @see createObject()
     * @see Container
     */
    public static $container;

    private static $_logger;

    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function getVersion()
    {
        return '2.2.0';
    }

    /**
     * Translates a path alias into an actual path.
     *
     * The translation is done according to the following procedure:
     *
     * 1. If the given alias does not start with '@', it is returned back without change;
     * 2. Otherwise, look for the longest registered alias that matches the beginning part
     * of the given alias. If it exists, replace the matching part of the given alias with
     * the corresponding registered path.
     * 3. Throw an exception or return false, depending on the `$throwException` parameter.
     *
     * For example, by default '@kant' is registered as the alias to the Kant framework directory,
     * say '/path/to/kant'. The alias '@kant/web' would then be translated into '/path/to/kant/web'.
     *
     * If you have registered two aliases '@foo' and '@foo/bar'. Then translating '@foo/bar/config'
     * would replace the part '@foo/bar' (instead of '@foo') with the corresponding registered path.
     * This is because the longest alias takes precedence.
     *
     * However, if the alias to be translated is '@foo/barbar/config', then '@foo' will be replaced
     * instead of '@foo/bar', because '/' serves as the boundary character.
     *
     * Note, this method does not check if the returned path exists or not.
     *
     * @param string $alias
     *            the alias to be translated.
     * @param boolean $throwException
     *            whether to throw an exception if the given alias is invalid.
     *            If this is false and an invalid alias is given, false will be returned by this method.
     * @return string|boolean the path corresponding to the alias, false if the root alias is not previously registered.
     * @throws InvalidParamException if the alias is invalid while $throwException is true.
     * @see setAlias()
     */
    public static function getAlias($alias, $throwException = true)
    {
        if (strncmp($alias, '@', 1)) {
            // not an alias
            return $alias;
        }
        
        $pos = strpos($alias, '/');
        $root = $pos === false ? $alias : substr($alias, 0, $pos);
        if (isset(static::$aliases[$root])) {
            if (is_string(static::$aliases[$root])) {
                return $pos === false ? static::$aliases[$root] : static::$aliases[$root] . substr($alias, $pos);
            } else {
                foreach (static::$aliases[$root] as $name => $path) {
                    if (strpos($alias . '/', $name . '/') === 0) {
                        return $path . substr($alias, strlen($name));
                    }
                }
            }
        }
        
        if ($throwException) {
            throw new InvalidParamException("Invalid path alias: $alias");
        } else {
            return false;
        }
    }

    /**
     * Registers a path alias.
     *
     * A path alias is a short name representing a long path (a file path, a URL, etc.)
     * For example, we use '@kant' as the alias of the path to the Kant framework directory.
     *
     * A path alias must start with the character '@' so that it can be easily differentiated
     * from non-alias paths.
     *
     * Note that this method does not check if the given path exists or not. All it does is
     * to associate the alias with the path.
     *
     * Any trailing '/' and '\' characters in the given path will be trimmed.
     *
     * @param string $alias
     *            the alias name (e.g. "@kant"). It must start with a '@' character.
     *            It may contain the forward slash '/' which serves as boundary character when performing
     *            alias translation by [[getAlias()]].
     * @param string $path
     *            the path corresponding to the alias. If this is null, the alias will
     *            be removed. Trailing '/' and '\' characters will be trimmed. This can be
     *            
     *            - a directory or a file path (e.g. `/tmp`, `/tmp/main.txt`)
     *            - a URL (e.g. `http://www.kantphp.com`)
     *            - a path alias (e.g. `@kant/base`). In this case, the path alias will be converted into the
     *            actual path first by calling [[getAlias()]].
     *            
     * @throws InvalidParamException if $path is an invalid alias.
     * @see getAlias()
     */
    public static function setAlias($alias, $path)
    {
        if (strncmp($alias, '@', 1)) {
            $alias = '@' . $alias;
        }
        $pos = strpos($alias, '/');
        $root = $pos === false ? $alias : substr($alias, 0, $pos);
        if ($path !== null) {
            $path = strncmp($path, '@', 1) ? rtrim($path, '\\/') : static::getAlias($path);
            if (!isset(static::$aliases[$root])) {
                if ($pos === false) {
                    static::$aliases[$root] = $path;
                } else {
                    static::$aliases[$root] = [
                        $alias => $path
                    ];
                }
            } elseif (is_string(static::$aliases[$root])) {
                if ($pos === false) {
                    static::$aliases[$root] = $path;
                } else {
                    static::$aliases[$root] = [
                        $alias => $path,
                        $root => static::$aliases[$root]
                    ];
                }
            } else {
                static::$aliases[$root][$alias] = $path;
                krsort(static::$aliases[$root]);
            }
        } elseif (isset(static::$aliases[$root])) {
            if (is_array(static::$aliases[$root])) {
                unset(static::$aliases[$root][$alias]);
            } elseif ($pos === false) {
                unset(static::$aliases[$root]);
            }
        }
    }

    /**
     * Creates a new object using the given configuration.
     *
     * You may view this method as an enhanced version of the `new` operator.
     * The method supports creating an object based on a class name, a configuration array or
     * an anonymous function.
     *
     * Below are some usage examples:
     *
     * Using [[\Kant\Di\Container|dependency injection container]], this method can also identify
     * dependent objects, instantiate them and inject them into the newly created object.
     *
     * @param string|array|callable $type
     *            the object type. This can be specified in one of the following forms:
     *            
     *            - a string: representing the class name of the object to be created
     *            - a configuration array: the array must contain a `class` element which is treated as the object class,
     *            and the rest of the name-value pairs will be used to initialize the corresponding object properties
     *            - a PHP callable: either an anonymous function or an array representing a class method (`[$class or $object, $method]`).
     *            The callable should return a new instance of the object being created.
     *            
     * @param array $params
     *            the constructor parameters
     * @return object the created object
     * @throws InvalidConfigException if the configuration is invalid.
     * @see \Kant\Di\Container
     */
    public static function createObject($type, $params = [])
    {
        if (is_string($type)) {
            return static::$container->get($type, $params);
        } elseif (is_array($type) && isset($type['class'])) {
            $class = $type['class'];
            unset($type['class']);
            return static::$container->get($class, $params, $type);
        } elseif (is_callable($type, true)) {
            return static::$container->invoke($type, $params);
        } elseif (is_array($type)) {
            throw new InvalidConfigException('Object configuration must be an array containing a "class" element.');
        } else {
            throw new InvalidConfigException('Unsupported configuration type: ' . gettype($type));
        }
    }

    /**
     * Configures an object with the initial property values.
     * 
     * @param object $object
     *            the object to be configured
     * @param array $properties
     *            the property initial values given in terms of name-value pairs.
     * @return object the object itself
     */
    public static function configure($object, $properties)
    {
        foreach ($properties as $name => $value) {
            $object->$name = $value;
        }
        
        return $object;
    }

    /**
     * Returns the public member variables of an object.
     * This method is provided such that we can get the public member variables of an object.
     * It is different from "get_object_vars()" because the latter will return private
     * and protected variables if it is called within the object itself.
     * 
     * @param object $object
     *            the object to be handled
     * @return array the public member variables of the object
     */
    public static function getObjectVars($object)
    {
        return get_object_vars($object);
    }

    /**
     * Translates a message to the specified language.
     *
     * This is a shortcut method of [[\Kant\I18n\I18N::translate()]].
     *
     * The translation will be conducted according to the message category and the target language will be used.
     *
     * You can add parameters to a translation message that will be substituted with the corresponding value after
     * translation. The format for this is to use curly brackets around the parameter name as you can see in the following example:
     *
     * ```php
     * $username = 'Alexander';
     * echo Kant\Kant:t('app', 'Hello, {username}!', ['username' => $username]);
     * ```
     *
     * Further formatting of message parameters is supported using the [PHP intl extensions](http://www.php.net/manual/en/intro.intl.php)
     * message formatter. See [[\Kant\I18n\I18N::translate()]] for more details.
     *
     * @param string $category
     *            the message category.
     * @param string $message
     *            the message to be translated.
     * @param array $params
     *            the parameters that will be used to replace the corresponding placeholders in the message.
     * @param string $language
     *            the language code (e.g. `en-US`, `en`). If this is null, the current
     *            [[\Kant\Application::language|application language]] will be used.
     * @return string the translated message.
     */
    public static function t($category, $message, $params = [], $language = null)
    {
        if (static::$app !== null) {
            return static::$app->getI18n()->translate($category, $message, $params, $language ?  : static::$app->getLanguage());
        } else {
            $p = [];
            foreach ((array) $params as $name => $value) {
                $p['{' . $name . '}'] = $value;
            }
            
            return ($p === []) ? $message : strtr($message, $p);
        }
    }

    /**
     *
     * @return Logger message logger
     */
    public static function getLogger()
    {
        if (self::$_logger !== null) {
            return self::$_logger;
        } else {
            return self::$_logger = static::createObject('Kant\Log\Logger');
        }
    }

    /**
     * Sets the logger object.
     * 
     * @param Logger $logger
     *            the logger object.
     */
    public static function setLogger($logger)
    {
        self::$_logger = $logger;
    }

    /**
     * Logs a trace message.
     * Trace messages are logged mainly for development purpose to see
     * the execution work flow of some code.
     * 
     * @param string $message
     *            the message to be logged.
     * @param string $category
     *            the category of the message.
     */
    public static function trace($message, $category = 'application')
    {
        if (Kant::$app->config->get('debug')) {
            static::getLogger()->log($message, Logger::LEVEL_TRACE, $category);
        }
    }

    /**
     * Logs an error message.
     * An error message is typically logged when an unrecoverable error occurs
     * during the execution of an application.
     * 
     * @param string $message
     *            the message to be logged.
     * @param string $category
     *            the category of the message.
     */
    public static function error($message, $category = 'application')
    {
        static::getLogger()->log($message, Logger::LEVEL_ERROR, $category);
    }

    /**
     * Logs a warning message.
     * A warning message is typically logged when an error occurs while the execution
     * can still continue.
     * 
     * @param string $message
     *            the message to be logged.
     * @param string $category
     *            the category of the message.
     */
    public static function warning($message, $category = 'application')
    {
        static::getLogger()->log($message, Logger::LEVEL_WARNING, $category);
    }

    /**
     * Logs an informative message.
     * An informative message is typically logged by an application to keep record of
     * something important (e.g. an administrator logs in).
     * 
     * @param string $message
     *            the message to be logged.
     * @param string $category
     *            the category of the message.
     */
    public static function info($message, $category = 'application')
    {
        static::getLogger()->log($message, Logger::LEVEL_INFO, $category);
    }

    /**
     * Marks the beginning of a code block for profiling.
     * This has to be matched with a call to [[endProfile]] with the same category name.
     * The begin- and end- calls must also be properly nested. For example,
     *
     * ```php
     * \Kant::beginProfile('block1');
     * // some code to be profiled
     * \Kant::beginProfile('block2');
     * // some other code to be profiled
     * \Kant::endProfile('block2');
     * \Kant::endProfile('block1');
     * ```
     * 
     * @param string $token
     *            token for the code block
     * @param string $category
     *            the category of this log message
     * @see endProfile()
     */
    public static function beginProfile($token, $category = 'application')
    {
        static::getLogger()->log($token, Logger::LEVEL_PROFILE_BEGIN, $category);
    }

    /**
     * Marks the end of a code block for profiling.
     * This has to be matched with a previous call to [[beginProfile]] with the same category name.
     * 
     * @param string $token
     *            token for the code block
     * @param string $category
     *            the category of this log message
     * @see beginProfile()
     */
    public static function endProfile($token, $category = 'application')
    {
        static::getLogger()->log($token, Logger::LEVEL_PROFILE_END, $category);
    }
}
