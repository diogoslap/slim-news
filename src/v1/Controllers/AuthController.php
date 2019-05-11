<?php
namespace App\v1\Controllers;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Firebase\JWT\JWT;
use App\Models\Entity\User;

class AuthController {
   
   protected $container;
      
   public function __construct($container) {
       $this->container = $container;
   }
   
  
   public function __invoke(Request $request, Response $response, $args) {
    
    $params = (object) $request->getParams();
    $headers = $request->getHeaders();
    $auth_user = $headers['PHP_AUTH_USER'];
    $repo = $this->container->get('em')->getRepository('App\Models\Entity\User');
    $user = $repo->findOneBy(['username'=>$auth_user]);
    $key = $this->container->get("secretkey");
    $token = array(
        "iat" => time(),
        "user" => $user->getUsername(),
        "id" =>$user->getId()
    );
    
    $jwt = JWT::encode($token, $key);
    return $response->withJson(["token" => $jwt,"user"=>["username"=>$user->getUsername(),"id"=>$user->getId()]], 200)
        ->withHeader('Content-type', 'application/json');   
   }

   public function createUser(Request $request, Response $response, $args) {
    $params = (object) $request->getParams();
    $entityManager = $this->container->get('em');   
    $repo = $entityManager->getRepository('App\Models\Entity\User');
    $user = $repo->findOneBy(['username'=>$params->username]);

    if($user){
        throw new \Exception ("Username already taken.", 400);
    }           
    $user = new User();
    $user->setUsername($params->username);
    $user->setPassword($params->password);
    
    $logger = $this->container->get('logger');
    $logger->info('User Created!', [$user->getUsername()]);
   
    $entityManager->persist($user);
    $entityManager->flush();

    $key = $this->container->get("secretkey");
    $token = array(
        "iat" => time(),
        "user" => $user->getUsername(),
        "id" => $user->getId()
    );
    $jwt = JWT::encode($token, $key);
    return $response->withJson(["token" => $jwt,'user'=>["username"=>$user->getUsername(),"id"=>$user->getId()]], 201)
        ->withHeader('Content-type', 'application/json');    
}
}