<?php

namespace Storm\MethodOverload;

use InvalidArgumentException;
use ReflectionObject;
use Closure;

class MethodOverloader
{
    private array $types = ['string', 'int', 'float', 'numeric', 'bool', 'array', 'resource', 'callable', 'object', 'mixed'];
    private array $callableDefs = [];

    private ?Closure $onFailureClosure = null;

    public static function create(): MethodOverloader
    {
        return new self();
    }

    public function register(callable $callable, mixed ...$types): MethodOverloader
    {
        foreach ($types as $type) {
            if (!in_array(strtolower($type), $this->types) and !class_exists($type)) {
                throw new InvalidArgumentException("Type/Class $type does not exists");
            }
        }

        $this->callableDefs[] = [
            'callable' => $callable,
            'types' => $types
        ];

        return $this;
    }

    public function find(array $invokeArgs): callable
    {
        foreach ($this->callableDefs as $definition) {
            $callable = $definition['callable'];
            $callableTypes = $definition['types'];
            if ($this->fulfillInvokeDefinition($invokeArgs, $callableTypes, true)){
                return $callable;
            }
        }
        foreach ($this->callableDefs as $definition) {
            $callable = $definition['callable'];
            $callableTypes = $definition['types'];
            if ($this->fulfillInvokeDefinition($invokeArgs, $callableTypes)) {
                return $callable;
            }
        }

        if ($this->onFailureClosure) {
            return $this->onFailureClosure;
        }
        throw new InvalidArgumentException();
    }

    public function invoke(array $invokeArgs): mixed
    {
        $callable = $this->find($invokeArgs);
        if (is_string($callable)) {
            $reflector = new ReflectionObject($this->object);
            $method = $reflector->getMethod($callable);
            $method->setAccessible(true);
            return $method->invokeArgs($this->object, $invokeArgs);
        }
        else {
            return call_user_func_array($callable, $invokeArgs);
        }
    }

    public function invokeArray(array $invokeArgs): mixed
    {
        $callable = $this->find($invokeArgs);
        return call_user_func_array($callable, $invokeArgs);
    }

    public function onFailure(callable $callable): MethodOverloader
    {
        $this->onFailureClosure = $callable;
        return $this;
    }

    private function fulfillInvokeDefinition($invokeArgs, $callableTypes, $strictTypes = false): bool
    {
        if (count($invokeArgs) === count($callableTypes)) {
            foreach($invokeArgs as $i => $arg) {
                $type = $callableTypes[$i];
                if (!$this->is_type($type, $arg, $strictTypes)) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    private function is_type(string $type, mixed $value, bool $strictTypes = false): bool
    {
        if ($value === null) {
            return true;
        }
        if ($strictTypes and is_object($value) and !is_callable($value)) {
            return $type == get_class($value);
        }

        return match($type) {
            'string' => is_string($value),
            'int' => is_int($value),
            'float' => is_float($value),
            'numeric' => is_numeric($value),
            'bool' => is_bool($value),
            'array' => is_array($value),
            'resource' => is_resource($value),
            'callable' => is_callable($value),
            'object' => is_object($value) and !is_callable($value) and !$strictTypes,
            'mixed' => true
        };
    }
}