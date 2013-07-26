<?php

namespace YetAnother\DI;

class Container implements \ArrayAccess
{
    protected $objects = array();

    public function get($className)
    {
        if (is_array($className)) return array_map(array($this, 'get'), $className);

        if (!isset($this->objects[$className]) || $this->objects[$className] instanceof \Closure) {
            $this->objects[$className] = $this->create($className);
        }

        return $this->objects[$className];
    }

    public function set($className, $object)
    {
        $this->objects[$className] = $object;
    }

    public function has($className)
    {
        return isset($this->objects[$className]);
    }

    public function push($object)
    {
        if (is_array($object)) return array_map(array($this, 'push'), $object);

        if (!is_object($object)) throw new \InvalidArgumentException("Scalar value can't be pushing into container");
        $className = get_class($object);
        if (!isset($this->objects[$className])) {
            $this->objects[$className] = $object;
        }
    }

    public function create($className)
    {
        if (isset($this->objects[$className]) && $this->objects[$className] instanceof \Closure) {
            return $this->createFromClosure($this->objects[$className]);
        } else {
            return $this->createObject($className);
        }
    }

    public function remove($className)
    {
        if (isset($this->objects[$className])) {
            unset($this->objects[$className]);
        }
    }

    protected function createObject($className)
    {
        if (!class_exists($className)) throw new \InvalidArgumentException("Class $className not exist");
        $reflectionClass = new \ReflectionClass($className);
        if ($reflectionConstructor = $reflectionClass->getConstructor()) {
            if ($reflectionParams = $reflectionConstructor->getParameters()) {
                $deps = $this->getDependencies($reflectionParams);
                return $reflectionClass->newInstanceArgs($deps);
            }
        }
        return new $className();
    }

    protected function createFromClosure($closure)
    {
        $reflectionFunction = new \ReflectionFunction($closure);
        if ($reflectionParams = $reflectionFunction->getParameters()) {
            $deps = $this->getDependencies($reflectionParams);
            return $reflectionFunction->invokeArgs($deps);
        }
        return $closure();
    }

    protected function getDependencies($reflectionParams)
    {
        return array_map(array($this, 'getDependency'), $reflectionParams);
    }

    protected function getDependency(\ReflectionParameter $param)
    {
        return $param->getClass() ?
            $this->get($param->getClass()->getName()) :
            null;
    }

    /********** ArrayAccess functions **********/

    public function offsetGet($className)
    {
        return $this->get($className);
    }

    public function offsetSet($className, $object)
    {
        if (is_null($className)) {
            $this->push($object);
        } else {
            $this->set($className, $object);
        }
    }

    public function offsetExists($className)
    {
        return $this->has($className);
    }

    public function offsetUnset($className)
    {
        $this->remove($className);
    }
}