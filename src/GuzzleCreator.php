<?php

declare(strict_types=1);

namespace Dog\Alarm;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Hyperf\Guzzle\HandlerStackFactory;
use Psr\Container\ContainerInterface;
use Swoole\Coroutine;

class GuzzleCreator
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * 创建guzzle客户端.
     *
     * @return Client
     */
    public function create(array $config = [])
    {
        $stack = $this->createHandler($config);
        $this->pushMiddlewares($stack, $config);

        $guzzleConfig = empty($config['options']) ? [] : $config['options'];
        $guzzleConfig['handler'] = $stack;

        return new Client($guzzleConfig);
    }

    /**
     * 创建guzzle handler.
     *
     * @return HandlerStack
     */
    protected function createHandler(array $config = [])
    {
        $handler = null;
        if ($this->inCoroutine() && class_exists(HandlerStackFactory::class)) {
            $factory = new HandlerStackFactory();
            return $factory->create(empty($config['pool']) ? [] : $config['pool']);
        }

        return HandlerStack::create($handler);
    }

    /**
     * Push guzzle客户端中间件.
     *
     * @param array $config
     */
    protected function pushMiddlewares(HandlerStack $stack, $config = [])
    {
        foreach (empty($config['middlewares']) ? [] : $config['middlewares'] as $name => $middleware) {
            if (is_callable($middleware)) {
                $middleware = call_user_func($middleware, $this->container);
            }
            $stack->push($middleware, $name);
        }
    }

    /**
     * 是否在协程中.
     *
     * @return bool
     */
    protected function inCoroutine()
    {
        if (! class_exists(Coroutine::class) || ! method_exists(Coroutine::class, 'getCid')) {
            return false;
        }
        return Coroutine::getCid() > -1;
    }
}
