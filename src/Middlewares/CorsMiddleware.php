<?php declare(strict_types=1);

namespace Octophp\Encore\Middlewares;

use Neomerx\Cors\Analyzer;
use Neomerx\Cors\Contracts\AnalysisResultInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\JsonResponse;

class CorsMiddleware implements MiddlewareInterface
{

    protected $settings;
    protected $logger;

    public function __construct($settings, $logger)
    {
        $this->settings = $settings;
        $this->logger = $logger;
    }

    /**
     * Handle an incoming request.
     *
     * @param RequestInterface $request
     * @param Closure          $next
     *
     * @return mixed
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler)  : ResponseInterface
    {
            //$cors = Analyzer::instance($this->settings)->analyze($request);
        
            $analyzer = Analyzer::instance($this->settings);
            $analyzer->setLogger($this->logger);
            $cors     = $analyzer->analyze($request);

 
         switch ($cors->getRequestType()) {
            case AnalysisResultInterface::ERR_NO_HOST_HEADER:
            case AnalysisResultInterface::ERR_ORIGIN_NOT_ALLOWED:
            case AnalysisResultInterface::ERR_METHOD_NOT_SUPPORTED:
            case AnalysisResultInterface::ERR_HEADERS_NOT_SUPPORTED:
                // return 4XX HTTP error
                return new JsonResponse(404);

            case AnalysisResultInterface::TYPE_PRE_FLIGHT_REQUEST:

                $corsHeaders = $cors->getResponseHeaders();
                $response = $handler->handle($request);
                foreach ($cors->getResponseHeaders() as $name => $value) {
                    $response = $response->withHeader($name, $value);
                }
                return $response->withStatus(200);


            case AnalysisResultInterface::TYPE_REQUEST_OUT_OF_CORS_SCOPE:
                // call next middleware handler
                $response = $handler->handle($request);
                return $response;
            
            default:
                // actual CORS request
                $corsHeaders = $cors->getResponseHeaders(); 
                $this->logger->warning('add CORS headers to Response $respons');
                $response = $handler->handle($request);
                foreach ($cors->getResponseHeaders() as $name => $value) {
                    $response = $response->withHeader($name, $value);
                }
                return $response;
        }
    }
}