<?php
/**
 * Created by PhpStorm.
 * User: crab
 * Date: 2015/4/12
 * Time: 15:23
 */
namespace Courser\Http;

use Courser\Http\StdObject;
use Courser\Http\Header;

/*
 * Http request extend swoole_http_request
 * the main properties and method are base on swoole
 * see https://wiki.swoole.com/wiki/page/328.html
 * */
class Request
{

    public $params = [];
    /*
     * @var array
     * */
    public $paramNames = [];

    /*
     * @var array
     * */
    public $methods = [];

    /*
     * @var array
     * */
    public $body = [];

    /*
     * @var array
     * */
    public $header = [];

    /*
     * @var array
     * */
    public $server = [];

    /*
     * @var string
     * */
    public $method = 'get';

    /*
     * @var object
     * */
    public $req;

    /*
     * @var array
     * */
    public $cookie = [];

    /*
    * @var array
    * */
    public $files = [];

    /*
     * @var array
     * */
    private $callable = [];

    /*
     * set request context
     * @param object $req  \Swoole\Http\Request
     * @return void
     * */
    public function setRequest(\Swoole\Http\Request $req)
    {
        $this->req = $req;
        $this->cookie = isset($req->cookie) ? $req->cookie : [];
        $this->server = $req->server;
        $this->files = isset($req->files) ? $req->files : [];
        $this->method = $req->server['request_method'];
        $reflection = new \ReflectionClass($req);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $key => $method) {
            $this->callable[] = $method->getName();
        }
    }

    /*
     * get request context method
     * */
    public function getMethod()
    {
        return $this->method;
    }


    /*
     * get all params
     *
     * @return array
     * */
    public function getParams()
    {
        return $this->params;
    }

    /*
     * add param name
     * @param string $name
     * @return void
     * */
    public function addParamName($name)
    {
        $this->paramNames[] = $name;
    }

    /*
     * set param
     * @param string $key
     * @param string $val
     * @return void
     * */
    public function setParam($key, $val)
    {
        if (in_array($key, $this->paramNames))
            $this->params[$key] = $val;
    }


    /*
     * get request header by field name
     *
     * @param string $name
     * */
    public function header($name)
    {
        return $this->req->header($name) ?: null;
    }

    /*
     * get cookie by key
     * @param string $key
     * @return mixed
     * */
    public function cookie($key)
    {
        if (isset($this->cookie[$key])) return $this->cookie[$key];

        return null;
    }

    /*
     * get request body by param name
     *
     * @param string $key param name
     * @return string || null
     * */
    public function body($key)
    {
        if ($this->header('content-type') === 'application/x-www-form-urlencoded') {
            return $this->req->post($key);
        } else {
            if ($this->body === null) {
                if (function_exists('mb_parse_str'))
                    mb_parse_str(file_get_contents('php://input'), $this->body);
                else
                    parse_str(file_get_contents('php://input'), $this->body);
            } else {
                return isset($this->body[$key]) ? $this->body[$key] : null;
            }
        }

        return null;
    }

    /*
     * get url query param by name
     * @param string $key
     * @return mixed
     * */
    public function query($key)
    {
        return $this->req->get($key) ?: null;
    }

    /*
     * check request js json request or not
     * */
    public function isJson()
    {
        return true;
    }


    public function __invoke($request)
    {
        return $this;
    }


    public function __get($name)
    {
        if (isset($this->req->$name)) return $this->req->$name;

        return null;
    }

    public function __set($name, $value)
    {

        return $this->req->$name = $value;
    }

    public function __call($func, $params)
    {
        if (isset($this->callable[$func])) {
            return call_user_func_array([$this->req, $func], $params);
        }

        return false;
    }
}