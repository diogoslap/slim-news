<?php
require './vendor/autoload.php';
require './db-config.php';
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Psr7Middlewares\Middleware\TrailingSlash;
use App\Auth\Authenticator;

$configs = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];
/**
 * Container Resources do Slim.
 * Aqui dentro dele vamos carregar todas as dependências
 * da nossa aplicação que vão ser consumidas durante a execução
 * da nossa API
 */
$container = new \Slim\Container($configs);

$container['errorHandler'] = function ($c) {
    return function ($request, $response, $exception) use ($c) {
        $statusCode = $exception->getCode() ? $exception->getCode() : 500;        
        return $c['response']->withStatus($statusCode)
            ->withHeader('Content-Type', 'Application/json')
            ->withJson(["message" => $exception->getMessage()], $statusCode);
    };
};

$container['notAllowedHandler'] = function ($c) {
    return function ($request, $response, $methods) use ($c) {
        return $c['response']
            ->withStatus(405)
            ->withHeader('Allow', implode(', ', $methods))
            ->withHeader('Content-Type', 'Application/json')
            ->withHeader("Access-Control-Allow-Methods", implode(",", $methods))
            ->withJson(["message" => "Method not Allowed; Method must be one of: " . implode(', ', $methods)], 405);
    };
};

$container['notFoundHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        return $container['response']
            ->withStatus(404)
            ->withHeader('Content-Type', 'Application/json')
            ->withJson(['message' => 'Page not found']);
    };
};

$container['logger'] = function($container) {
    $logger = new Monolog\Logger('news-microservice');
    $logfile = __DIR__ . '/log/news-microservice.log';
    $stream = new Monolog\Handler\StreamHandler($logfile, Monolog\Logger::DEBUG);
    $fingersCrossed = new Monolog\Handler\FingersCrossedHandler(
        $stream, Monolog\Logger::INFO);
    $logger->pushHandler($fingersCrossed);
    
    return $logger;
};

$isDevMode = true;

$config = Setup::createAnnotationMetadataConfiguration(array(__DIR__."/src/Models/Entity"), $isDevMode);



$entityManager = EntityManager::create($conn, $config);

$container['em'] = $entityManager;

$container['secretkey'] = "yoursecretkeyjwthere";

$app = new \Slim\App($container);

$app->add(new TrailingSlash(false));

$app->add(new \Tuupola\Middleware\CorsMiddleware([
    "origin" => ["*"],
    "methods" => ["GET", "POST", "PATCH", "DELETE", "OPTIONS"],    
    "headers.allow" => ["Content-Type", "Authorization", "Accept", "X-Token"],
    "headers.expose" => [],
    "credentials" => false,
    "cache" => 0,        
]));


$app->add(new \Tuupola\Middleware\HttpBasicAuthentication([ 
    
    "path" => ["/auth","/v1/auth/login"],
    "ignore" => ["/v1/auth/register"],
    "authenticator" =>  new Authenticator($container),
    "error" => function ($response, $arguments) {
        $data = [];
        $data["status"] = "error";
        $data["message"] = $arguments["message"];
        return $response->write(json_encode($data, JSON_UNESCAPED_SLASHES));
    }
    
]));

$app->add(new \Tuupola\Middleware\JwtAuthentication([
    "regexp" => "/(.*)/", 
    "header" => "X-Token", 
    "attribute" => "jwt",
    "logger" => $container->get('logger'),    
    "secure" => false,
    "rules" => [
        new Tuupola\Middleware\JwtAuthentication\RequestPathRule([
            "path" =>"/",
            "ignore" => ["/v1/auth/login","/v1/auth/register"]
        ]),
        new Tuupola\Middleware\JwtAuthentication\RequestMethodRule([            
            "ignore" => ["OPTIONS"]
        ]),
        new  App\Auth\JwtRequestMethodPathRule([
            "path" => "/",
            "passthrough" => ['GET'=>"/v1/news",'OPTIONS'=>"/v1/news"]
        ])        
    ],
    "realm" => "Protected", 
    "secret" => $container['secretkey'] 
]));

