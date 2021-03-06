<?php
/**
 * @license MIT
 * @copyright Copyright (c) 2018
 * @author: bugbear
 * @date: 2018/2/2
 * @time: 下午2:56
 */

namespace Courser;

class Route
{
    protected $method = 'get';

    protected $path = '/';

    protected $callable = [];

    protected $group = '/';

    protected $scope = 1;

    protected $paramNames = [];

    protected $middleware;

    protected $pattern = '';

    protected $route = '';

    protected $length = 0;

    /**
     * Route constructor.
     *
     * @param string $method
     * @param string $route
     * @param array $callable
     * @param int $scope
     * @param string $group
     */
    public function __construct(string $method, string $route, array $callable, int $scope = 1, string $group = '/')
    {
        $route = $route === '/' ? $route : rtrim($route, '/');
        $this->method = $method;
        $this->route = $route;
        $this->group = $group;
        $this->scope = $scope;
        list($regex, $params) = $this->parseRoute($route);
        $this->pattern = '#^' . $regex . '$#';
        $this->paramNames = $params;
        $this->length = count(explode('/', $route));
        $this->callable = $callable;
    }

    public function getRoute(): string
    {
        return $this->route;
    }


    /**
     * @param $route string
     * @return array
     */
    public static function parseRoute(string $route)
    {
        $params = [];
        $regex = preg_replace_callback(
            '#:([\w]+)|{([\w]+)}|(\*)#',
            function ($match) use (&$params) {
                $name = array_pop($match);
                $type = $match[0][0];
                if ($type === '*') {
                    return '(.*)';
                }
                $type = $type === ':' ? '\d' : '\w';
                $params[] = $name;
                return "(?P<$name>[$type]+)";
            },
            $route
        );

        return [$regex, $params];
    }

    public function add(callable $callable)
    {
        $this->callable[] = $callable;
    }

    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setParamName(string $name, string $value)
    {
        $this->paramNames[$name] = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * @return int
     */
    public function getScope(): int
    {
        return $this->scope;
    }

    /**
     * @param string $method
     * @param string $path
     * @return null|array
     */
    public function find(string $method, string $path)
    {
        if ($this->method !== $method) {
            return null;
        }

        preg_match($this->pattern, $path, $match);
        if (empty($match)) {
            return null;
        }

        return $match;
    }

    /**
     * @return string
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }


    /**
     * @return int
     */
    public function len(): int
    {
        return $this->length;
    }

    public function getParamNames(): array
    {
        return $this->paramNames;
    }

    public function getCallable()
    {
        return $this->callable;
    }
}
