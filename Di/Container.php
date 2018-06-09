<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Di;

use Kant\Kant;
use Kant\Foundation\Component;
use Kant\Helper\ArrayHelper;
use Kant\Exception\InvalidConfigException;
use Kant\Exception\NotFoundHttpException;
use Kant\Exception\NotInstantiableException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionParameter;
use ReflectionException;

/**
 * Container implements a [dependency injection](http://en.wikipedia.org/wiki/Dependency_injection) container.
 *
 * A dependency injection (DI) container is an object that knows how to instantiate and configure objects and
 * all their dependent objects. For more information about DI, please refer to
 * [Martin Fowler's article](http://martinfowler.com/articles/injection.html).
 *
 * Container supports constructor injection as well as property injection.
 *
 * To use Container, you first need to set up the class dependencies by calling [[set()]].
 * You then call [[get()]] to create a new class object. Container will automatically instantiate
 * dependent objects, inject them into the object being created, configure and finally return the newly created object.
 *
 * By default, [[\Kant::$container]] refers to a Container instance which is used by [[\Kant::createObject()]]
 * to create new object instances. You may use this method to replace the `new` operator
 * when creating a new object, which gives you the benefit of automatic dependency resolution and default
 * property configuration.
 */
class Container extends Component
{

    /**
     *
     * @var array singleton objects indexed by their types
     */
    private $_singletons = [];

    /**
     *
     * @var array object definitions indexed by their types
     */
    private $_definitions = [];

    /**
     *
     * @var array constructor parameters indexed by object types
     */
    private $_params = [];

    /**
     *
     * @var array cached ReflectionClass objects indexed by class/interface names
     */
    private $_reflections = [];

    /**
     *
     * @var array cached dependencies indexed by class/interface names. Each class name
     *      is associated with a list of constructor parameter types or default values.
     */
    private $_dependencies = [];

    /**
     * Returns an instance of the requested class.
     *
     * You may provide constructor parameters (`$params`) and object configurations (`$config`)
     * that will be used during the creation of the instance.
     *
     * If the class implements [[\Kant\Foundation\Configurable]], the `$config` parameter will be passed as the last
     * parameter to the class constructor; Otherwise, the configuration will be applied *after* the object is
     * instantiated.
     *
     * Note that if the class is declared to be singleton by calling [[setSingleton()]],
     * the same instance of the class will be returned each time this method is called.
     * In this case, the constructor parameters and object configurations will be used
     * only if the class is instantiated the first time.
     *
     * @param string $class
     *            the class name or an alias name (e.g. `foo`) that was previously registered via [[set()]]
     *            or [[setSingleton()]].
     * @param array $params
     *            a list of constructor parameter values. The parameters should be provided in the order
     *            they appear in the constructor declaration. If you want to skip some parameters, you should index the remaining
     *            ones with the integers that represent their positions in the constructor parameter list.
     * @param array $config
     *            a list of name-value pairs that will be used to initialize the object properties.
     * @return object an instance of the requested class.
     * @throws InvalidConfigException if the class cannot be recognized or correspond to an invalid definition
     * @throws NotInstantiableException If resolved to an abstract class or an interface (since 2.0.9)
     */
    public function get($class, $params = [], $config = [])
    {
        if (isset($this->_singletons[$class])) {
            // singleton
            return $this->_singletons[$class];
        } elseif (! isset($this->_definitions[$class])) {
            return $this->build($class, $params, $config);
        }
        
        $definition = $this->_definitions[$class];
        
        if (is_callable($definition, true)) {
            $params = $this->resolveDependencies($this->mergeParams($class, $params));
            $object = call_user_func($definition, $this, $params, $config);
        } elseif (is_array($definition)) {
            $concrete = $definition['class'];
            unset($definition['class']);
            
            $config = array_merge($definition, $config);
            $params = $this->mergeParams($class, $params);
            
            if ($concrete === $class) {
                $object = $this->build($class, $params, $config);
            } else {
                $object = $this->get($concrete, $params, $config);
            }
        } elseif (is_object($definition)) {
            return $this->_singletons[$class] = $definition;
        } else {
            throw new InvalidConfigException('Unexpected object definition type: ' . gettype($definition));
        }
        
        if (array_key_exists($class, $this->_singletons)) {
            // singleton
            $this->_singletons[$class] = $object;
        }
        
        return $object;
    }

    /**
     * Registers a class definition with this container.
     *
     * For example,
     *
     * ```php
     * // register a class name as is. This can be skipped.
     * $container->set('Kant\Database\Connection');
     *
     * // register an interface
     * // When a class depends on the interface, the corresponding class
     * // will be instantiated as the dependent object
     * $container->set('Kant\Mail\MailInterface', 'Kant\Swiftmailer\Mailer');
     *
     * // register an alias name. You can use $container->get('foo')
     * // to create an instance of Connection
     * $container->set('foo', 'Kant\Database\Connection');
     *
     * // register a class with configuration. The configuration
     * // will be applied when the class is instantiated by get()
     * $container->set('Kant\Database\Connection', [
     * 'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
     * 'username' => 'root',
     * 'password' => '',
     * 'charset' => 'utf8',
     * ]);
     *
     * // register an alias name with class configuration
     * // In this case, a "class" element is required to specify the class
     * $container->set('db', [
     * 'class' => 'Kant\Database\Connection',
     * 'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
     * 'username' => 'root',
     * 'password' => '',
     * 'charset' => 'utf8',
     * ]);
     *
     * // register a PHP callable
     * // The callable will be executed when $container->get('db') is called
     * $container->set('db', function ($container, $params, $config) {
     * return new \Kant\Database\Connection($config);
     * });
     * ```
     *
     * If a class definition with the same name already exists, it will be overwritten with the new one.
     * You may use [[has()]] to check if a class definition already exists.
     *
     * @param string $class
     *            class name, interface name or alias name
     * @param mixed $definition
     *            the definition associated with `$class`. It can be one of the following:
     *            
     *            - a PHP callable: The callable will be executed when [[get()]] is invoked. The signature of the callable
     *            should be `function ($container, $params, $config)`, where `$params` stands for the list of constructor
     *            parameters, `$config` the object configuration, and `$container` the container object. The return value
     *            of the callable will be returned by [[get()]] as the object instance requested.
     *            - a configuration array: the array contains name-value pairs that will be used to initialize the property
     *            values of the newly created object when [[get()]] is called. The `class` element stands for the
     *            the class of the object to be created. If `class` is not specified, `$class` will be used as the class name.
     *            - a string: a class name, an interface name or an alias name.
     * @param array $params
     *            the list of constructor parameters. The parameters will be passed to the class
     *            constructor when [[get()]] is called.
     * @return $this the container itself
     */
    public function set($class, $definition = [], array $params = [])
    {
        $this->_definitions[$class] = $this->normalizeDefinition($class, $definition);
        $this->_params[$class] = $params;
        unset($this->_singletons[$class]);
        return $this;
    }

    /**
     * Registers a class definition with this container and marks the class as a singleton class.
     *
     * This method is similar to [[set()]] except that classes registered via this method will only have one
     * instance. Each time [[get()]] is called, the same instance of the specified class will be returned.
     *
     * @param string $class
     *            class name, interface name or alias name
     * @param mixed $definition
     *            the definition associated with `$class`. See [[set()]] for more details.
     * @param array $params
     *            the list of constructor parameters. The parameters will be passed to the class
     *            constructor when [[get()]] is called.
     * @return $this the container itself
     * @see set()
     */
    public function setSingleton($class, $definition = [], array $params = [])
    {
        $this->_definitions[$class] = $this->normalizeDefinition($class, $definition);
        $this->_params[$class] = $params;
        $this->_singletons[$class] = null;
        return $this;
    }

    /**
     * Returns a value indicating whether the container has the definition of the specified name.
     * 
     * @param string $class
     *            class name, interface name or alias name
     * @return boolean whether the container has the definition of the specified name..
     * @see set()
     */
    public function has($class)
    {
        return isset($this->_definitions[$class]);
    }

    /**
     * Returns a value indicating whether the given name corresponds to a registered singleton.
     * 
     * @param string $class
     *            class name, interface name or alias name
     * @param boolean $checkInstance
     *            whether to check if the singleton has been instantiated.
     * @return boolean whether the given name corresponds to a registered singleton. If `$checkInstance` is true,
     *         the method should return a value indicating whether the singleton has been instantiated.
     */
    public function hasSingleton($class, $checkInstance = false)
    {
        return $checkInstance ? isset($this->_singletons[$class]) : array_key_exists($class, $this->_singletons);
    }

    /**
     * Removes the definition for the specified name.
     * 
     * @param string $class
     *            class name, interface name or alias name
     */
    public function clear($class)
    {
        unset($this->_definitions[$class], $this->_singletons[$class]);
    }

    /**
     * Normalizes the class definition.
     * 
     * @param string $class
     *            class name
     * @param string|array|callable $definition
     *            the class definition
     * @return array the normalized class definition
     * @throws InvalidConfigException if the definition is invalid.
     */
    protected function normalizeDefinition($class, $definition)
    {
        if (empty($definition)) {
            return [
                'class' => $class
            ];
        } elseif (is_string($definition)) {
            return [
                'class' => $definition
            ];
        } elseif (is_callable($definition, true) || is_object($definition)) {
            return $definition;
        } elseif (is_array($definition)) {
            if (! isset($definition['class'])) {
                if (strpos($class, '\\') !== false) {
                    $definition['class'] = $class;
                } else {
                    throw new InvalidConfigException("A class definition requires a \"class\" member.");
                }
            }
            return $definition;
        } else {
            throw new InvalidConfigException("Unsupported definition type for \"$class\": " . gettype($definition));
        }
    }

    /**
     * Returns the list of the object definitions or the loaded shared objects.
     * 
     * @return array the list of the object definitions or the loaded shared objects (type or ID => definition or instance).
     */
    public function getDefinitions()
    {
        return $this->_definitions;
    }

    /**
     * Creates an instance of the specified class.
     * This method will resolve dependencies of the specified class, instantiate them, and inject
     * them into the new instance of the specified class.
     * 
     * @param string $class
     *            the class name
     * @param array $params
     *            constructor parameters
     * @param array $config
     *            configurations to be applied to the new instance
     * @return object the newly created instance of the specified class
     * @throws NotInstantiableException If resolved to an abstract class or an interface (since 2.0.9)
     */
    protected function build($class, $params, $config)
    {
        /* @var $reflection ReflectionClass */
        list ($reflection, $dependencies) = $this->getDependencies($class);
        
        foreach ($params as $index => $param) {
            $dependencies[$index] = $param;
        }
        
        $dependencies = $this->resolveDependencies($dependencies, $reflection);
        if (! $reflection->isInstantiable()) {
            throw new NotInstantiableException($reflection->name);
        }
        if (empty($config)) {
            return $reflection->newInstanceArgs($dependencies);
        }
        if (! empty($dependencies) && $reflection->implementsInterface('Kant\Foundation\Configurable')) {
            // set $config as the last parameter (existing one will be overwritten)
            $dependencies[count($dependencies) - 1] = $config;
            return $reflection->newInstanceArgs($dependencies);
        } else {
            $object = $reflection->newInstanceArgs($dependencies);
            foreach ($config as $name => $value) {
                $object->$name = $value;
            }
            return $object;
        }
    }

    /**
     * Merges the user-specified constructor parameters with the ones registered via [[set()]].
     * 
     * @param string $class
     *            class name, interface name or alias name
     * @param array $params
     *            the constructor parameters
     * @return array the merged parameters
     */
    protected function mergeParams($class, $params)
    {
        if (empty($this->_params[$class])) {
            return $params;
        } elseif (empty($params)) {
            return $this->_params[$class];
        } else {
            $ps = $this->_params[$class];
            foreach ($params as $index => $value) {
                $ps[$index] = $value;
            }
            return $ps;
        }
    }

    /**
     * Returns the dependencies of the specified class.
     * 
     * @param string $class
     *            class name, interface name or alias name
     * @return array the dependencies of the specified class.
     */
    protected function getDependencies($class)
    {
        if (isset($this->_reflections[$class])) {
            return [
                $this->_reflections[$class],
                $this->_dependencies[$class]
            ];
        }
        
        $dependencies = [];
        if (class_exists($class) == false) {
            throw new NotFoundHttpException('Class not exists: ' . $class);
        }
        $reflection = new ReflectionClass($class);
        
        $constructor = $reflection->getConstructor();
        if ($constructor !== null) {
            foreach ($constructor->getParameters() as $param) {
                if ($param->isDefaultValueAvailable()) {
                    $dependencies[] = $param->getDefaultValue();
                } else {
                    $c = $param->getClass();
                    $dependencies[] = Instance::of($c === null ? null : $c->getName());
                }
            }
        }
        
        $this->_reflections[$class] = $reflection;
        $this->_dependencies[$class] = $dependencies;
        
        return [
            $reflection,
            $dependencies
        ];
    }

    /**
     * Resolves dependencies by replacing them with the actual object instances.
     * 
     * @param array $dependencies
     *            the dependencies
     * @param ReflectionClass $reflection
     *            the class reflection associated with the dependencies
     * @return array the resolved dependencies
     * @throws InvalidConfigException if a dependency cannot be resolved or if a dependency cannot be fulfilled.
     */
    protected function resolveDependencies($dependencies, $reflection = null)
    {
        foreach ($dependencies as $index => $dependency) {
            if ($dependency instanceof Instance) {
                if ($dependency->id !== null) {
                    $dependencies[$index] = $this->get($dependency->id);
                } elseif ($reflection !== null) {
                    $name = $reflection->getConstructor()->getParameters()[$index]->getName();
                    $class = $reflection->getName();
                    throw new InvalidConfigException("Missing required parameter \"$name\" when instantiating \"$class\".");
                }
            }
        }
        return $dependencies;
    }

    /**
     * Invoke a callback with resolving dependencies in parameters.
     *
     * This methods allows invoking a callback and let type hinted parameter names to be
     * resolved as objects of the Container. It additionally allow calling function using named parameters.
     *
     * For example, the following callback may be invoked using the Container to resolve the formatter dependency:
     *
     * ```php
     * $formatString = function($string, \Kant\I18n\Formatter $formatter) {
     * // ...
     * }
     * Kant::$container->invoke($formatString, ['string' => 'Hello World!']);
     * ```
     *
     * This will pass the string `'Hello World!'` as the first param, and a formatter instance created
     * by the DI container as the second param to the callable.
     *
     * @param callable $callback
     *            callable to be invoked.
     * @param array $params
     *            The array of parameters for the function.
     *            This can be either a list of parameters, or an associative array representing named function parameters.
     * @return mixed the callback return value.
     * @throws InvalidConfigException if a dependency cannot be resolved or if a dependency cannot be fulfilled.
     * @throws NotInstantiableException If resolved to an abstract class or an interface (since 2.0.9)
     * @since 2.0.7
     */
    public function invoke(callable $callback, $params = [])
    {
        if (is_callable($callback)) {
            return call_user_func_array($callback, $this->resolveCallableDependencies($callback, $params));
        } else {
            return call_user_func_array($callback, $params);
        }
    }

    /**
     * Resolve dependencies for a function.
     *
     * This method can be used to implement similar functionality as provided by [[invoke()]] in other
     * components.
     *
     * @param callable $callback
     *            callable to be invoked.
     * @param array $params
     *            The array of parameters for the function, can be either numeric or associative.
     * @return array The resolved dependencies.
     * @throws InvalidConfigException if a dependency cannot be resolved or if a dependency cannot be fulfilled.
     * @throws NotInstantiableException If resolved to an abstract class or an interface (since 2.0.9)
     * @since 2.0.7
     */
    public function resolveCallableDependencies(callable $callback, $params = [])
    {
        if (is_array($callback)) {
            $reflection = new \ReflectionMethod($callback[0], $callback[1]);
        } else {
            $reflection = new \ReflectionFunction($callback);
        }
        
        $args = [];
        
        $associative = ArrayHelper::isAssociative($params);
        
        foreach ($reflection->getParameters() as $param) {
            $name = $param->getName();
            if (($class = $param->getClass()) !== null) {
                $className = $class->getName();
                if ($associative && isset($params[$name]) && $params[$name] instanceof $className) {
                    $args[] = $params[$name];
                    unset($params[$name]);
                } elseif (! $associative && isset($params[0]) && $params[0] instanceof $className) {
                    $args[] = array_shift($params);
                } elseif (isset(Kant::$app) && Kant::$app->has($name) && ($obj = Kant::$app->get($name)) instanceof $className) {
                    $args[] = $obj;
                } else {
                    // If the argument is optional we catch not instantiable exceptions
                    try {
                        $args[] = $this->get($className);
                    } catch (NotInstantiableException $e) {
                        if ($param->isDefaultValueAvailable()) {
                            $args[] = $param->getDefaultValue();
                        } else {
                            throw $e;
                        }
                    }
                }
            } elseif ($associative && isset($params[$name])) {
                $args[] = $params[$name];
                unset($params[$name]);
            } elseif (! $associative && count($params)) {
                $args[] = array_shift($params);
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } elseif (! $param->isOptional()) {
                $funcName = $reflection->getName();
                throw new InvalidConfigException("Missing required parameter \"$name\" when calling \"$funcName\".");
            }
        }
        
        foreach ($params as $value) {
            $args[] = $value;
        }
        return $args;
    }

    /**
     * Call the given Closure / class@method and inject its dependencies.
     *
     * @param callable|string $callback            
     * @param array $parameters            
     * @param string|null $defaultMethod            
     * @return mixed
     */
    public function call($callback, array $parameters = [], $defaultMethod = null)
    {
        if ($this->isCallableWithAtSign($callback) || $defaultMethod) {
            return $this->callClass($callback, $parameters, $defaultMethod);
        }
        $dependencies = $this->getMethodDependencies($callback, $parameters);
        return call_user_func_array($callback, $dependencies);
    }

    /**
     * Determine if the given string is in Class@method syntax.
     *
     * @param mixed $callback            
     * @return bool
     */
    protected function isCallableWithAtSign($callback)
    {
        return is_string($callback) && strpos($callback, '@') !== false;
    }

    /**
     * Get all dependencies for a given method.
     *
     * @param callable|string $callback            
     * @param array $parameters            
     * @return array
     */
    protected function getMethodDependencies($callback, array $parameters = [])
    {
        $dependencies = [];
        
        foreach ($this->getCallReflector($callback)->getParameters() as $parameter) {
            $this->addDependencyForCallParameter($parameter, $parameters, $dependencies);
        }
        return array_merge($parameters, $dependencies);
        // return array_merge($dependencies, $parameters);
    }

    /**
     * Get the proper reflection instance for the given callback.
     *
     * @param callable|string $callback            
     * @return \ReflectionFunctionAbstract
     */
    protected function getCallReflector($callback)
    {
        if (is_string($callback) && strpos($callback, '::') !== false) {
            $callback = explode('::', $callback);
        }
        if (is_array($callback)) {
            list ($class, $name) = $callback;
            if (! preg_match('/^[A-Za-z](\w)*$/', $name)) {
                throw new ReflectionException('Method not exists: ' . get_class($class) . "::" . $name);
            }
            if (method_exists($class, $name)) {
                $method = new ReflectionMethod($class, $name);
                
                if (! $method->isPublic()) {
                    throw new ReflectionException('Method not exists: ' . get_class($class) . "::" . $name);
                }
                return $method;
            } else {
                throw new InvalidConfigException('Method not exists: ' . get_class($class) . "::" . $name);
            }
        }
        return new ReflectionFunction($callback);
    }

    /**
     * Get the dependency for the given call parameter.
     *
     * @param \ReflectionParameter $parameter            
     * @param array $parameters            
     * @param array $dependencies            
     * @return mixed
     */
    protected function addDependencyForCallParameter(ReflectionParameter $parameter, array &$parameters, &$dependencies)
    {
        if (array_key_exists($parameter->name, $parameters)) {
            $dependencies[] = $parameters[$parameter->name];
            
            unset($parameters[$parameter->name]);
        } elseif ($parameter->getClass()) {
            $dependencies[] = $this->make($parameter->getClass()->name);
        } elseif ($parameter->isDefaultValueAvailable()) {
            $dependencies[] = $parameter->getDefaultValue();
        }
    }

    /**
     * Call a string reference to a class using Class@method syntax.
     *
     * @param string $target            
     * @param array $parameters            
     * @param string|null $defaultMethod            
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function callClass($target, array $parameters = [], $defaultMethod = null)
    {
        $segments = explode('@', $target);
        
        // If the listener has an @ sign, we will assume it is being used to delimit
        // the class name from the handle method name. This allows for handlers
        // to run multiple handler methods in a single class for convenience.
        $method = count($segments) == 2 ? $segments[1] : $defaultMethod;
        
        if (is_null($method)) {
            throw new InvalidArgumentException('Method not provided.');
        }
        
        return $this->call([
            $this->make($segments[0]),
            $method
        ], $parameters);
        
        // return $this->call([Kant::$container->get($segments[0]), $method], $parameters);
    }

    /**
     * Resolve the given type from the container.
     */
    public function make($class)
    {
        return $this->get($class);
    }

    /**
     * Determine if the given options exclude a particular method.
     *
     * @param string $method            
     * @param array $options            
     * @return bool
     */
    protected static function methodExcludedByOptions($method, array $options)
    {
        return (isset($options['only']) && ! in_array($method, (array) $options['only'])) || (! empty($options['except']) && in_array($method, (array) $options['except']));
    }
}
