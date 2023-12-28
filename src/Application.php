<?php

namespace Octophp\Encore;

use Laminas\Diactoros\ResponseFactory;

use Psr\Container\ContainerInterface;
use League\Route\Router;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Octophp\Encore\Route\Strategy\JsonStrategy;
use Psr\Log\LoggerInterface;

class Application
{
    const VERSION = '0.2.0';
    
    public $router;
    protected $response;
    protected $request;
    private ContainerInterface $container;
    private LoggerInterface $logger;
    private float $started_at;

    public function __construct(ContainerInterface $container, float $started_at)
    {
        $this->container = $container;
        $f = new Psr17Factory();
        $this->request =  (new ServerRequestCreator($f, $f, $f, $f))->fromGlobals();
        $this->logger = $this->container->get('Psr\Log\LoggerInterface');
        $this->started_at = $started_at;
    }

    public function get_version()
    {
        return SELF::VERSION;
    }

    public function router()
    {
        $responseFactory = new ResponseFactory();
        $strategy = (new JsonStrategy($responseFactory))->setContainer($this->container);
        $this->router = (new Router)->setStrategy($strategy);
    }
 
    public function run()
    {
        $this->response = $this->router->dispatch($this->request);
        (new \Laminas\HttpHandlerRunner\Emitter\SapiEmitter)->emit($this->response);
        $this->logger->info(sprintf("Execution time: %.3f sec", microtime(true) - $this->started_at));
        $this->logger->info(sprintf("Memory usage: %d bytes", memory_get_usage(true)));
    }

}
