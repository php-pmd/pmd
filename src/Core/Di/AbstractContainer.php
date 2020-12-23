<?php

namespace PhpPmd\Pmd\Core\Di;

abstract class AbstractContainer implements ContainerInterface
{
    protected $__resolvedEntries = [];
    /**
     * @var array
     */
    protected $__definitions = [];

    public function __construct($definitions = [])
    {
        foreach ($definitions as $id => $definition) {
            $this->injection($id, $definition);
        }
    }

    public function get($id)
    {

        if (!$this->has($id)) {
            throw new NotFoundException("No entry or class found for {$id}");
        }

        $instance = $this->make($id);

        return $instance;
    }

    public function has($id)
    {
        return isset($this->__definitions[$id]);
    }

    public function injection($id, $concrete)
    {
        if (is_array($concrete) && !isset($concrete['class'])) {
            throw new ContainerException('Array must contain a class definition.');
        }

        $this->__definitions[$id] = $concrete;
    }

    public function make($name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException(sprintf(
                'The name parameter must be of type string, %s given',
                is_object($name) ? get_class($name) : gettype($name)
            ));
        }

        if (isset($this->__resolvedEntries[$name])) {
            return $this->__resolvedEntries[$name];
        }

        if (!$this->has($name)) {
            throw new NotFoundException("No entry or class found for {$name}");
        }

        $definition = $this->__definitions[$name];
        $params = [];
        if (is_array($definition) && isset($definition['class'])) {
            $params = $definition;
            $definition = $definition['class'];
            unset($params['class']);
        }

        $object = $this->reflector($definition, $params);

        return $this->__resolvedEntries[$name] = $object;
    }

    public function reflector($concrete, array $params = [])
    {
        if ($concrete instanceof \Closure) {
            return $concrete($params);
        } elseif (is_string($concrete)) {
            $reflection = new \ReflectionClass($concrete);
            $dependencies = $this->getDependencies($reflection);
            foreach ($params as $index => $value) {
                $dependencies[$index] = $value;
            }
            return $reflection->newInstanceArgs($dependencies);
        } elseif (is_object($concrete)) {
            return $concrete;
        }
    }

    private function getDependencies($reflection)
    {
        $dependencies = [];
        $constructor = $reflection->getConstructor();
        if ($constructor !== null) {
            $parameters = $constructor->getParameters();
            $dependencies = $this->getParametersByDependencies($parameters);
        }

        return $dependencies;
    }

    private function getParametersByDependencies(array $dependencies)
    {
        $parameters = [];
        foreach ($dependencies as $param) {
            if ($param->getClass()) {
                $paramName = $param->getClass()->name;
                $paramObject = $this->reflector($paramName);
                $parameters[] = $paramObject;
            } elseif ($param->isArray()) {
                if ($param->isDefaultValueAvailable()) {
                    $parameters[] = $param->getDefaultValue();
                } else {
                    $parameters[] = [];
                }
            } elseif ($param->isCallable()) {
                if ($param->isDefaultValueAvailable()) {
                    $parameters[] = $param->getDefaultValue();
                } else {
                    $parameters[] = function ($arg) {
                    };
                }
            } else {
                if ($param->isDefaultValueAvailable()) {
                    $parameters[] = $param->getDefaultValue();
                } else {
                    if ($param->allowsNull()) {
                        $parameters[] = null;
                    } else {
                        $parameters[] = false;
                    }
                }
            }
        }
        return $parameters;
    }
}