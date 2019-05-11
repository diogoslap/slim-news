<?php
namespace App\v1\Controllers;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use App\Models\Entity\News;
use Firebase\JWT\JWT;
/**
 * Controller v1 de livros
 */
class NewsController {
    
    private $container;
    
    public function __construct($container) {
        $this->container = $container;
    }
    
   
    public function listNews($request, $response, $args) {
        $entityManager = $this->container->get('em');
        $newsRepository = $entityManager->getRepository('App\Models\Entity\News');
        $news = $newsRepository->findAll();
        
        foreach($news as $k => $new){
            $news[$k]->author = ["name"=>$new->getAuthor()->getUsername(),"id"=>$new->getAuthor()->getId()];
        }
        $logger = $this->container->get('logger');
        $logger->info("List News.",$request->getHeaders());
        $return = $response->withJson($news, 200)
            ->withHeader('Content-type', 'application/json');
        return $return;        
    }
    
   
    public function createNews(Request $request, Response $response, array $args) {
        $params = (object) $request->getParams();
        $jwt = $request->getAttribute('jwt');                
        $entityManager = $this->container->get('em');   
        $repo = $this->container->get('em')->getRepository('App\Models\Entity\User');
        $user = $repo->find($jwt['id']);         
        $news = (new News())->setTitle($params->title)
            ->setAuthor($user)
            ->setStatus($params->status)
            ->setDescription($params->description);

        $entityManager->persist($news);
        $entityManager->flush();

        $news->author = ["name"=>$news->getAuthor()->getUsername(),"id"=>$news->getAuthor()->getId()];

        $logger = $this->container->get('logger');
        $logger->info('News Created!', $news->getValues());

        $return = $response->withJson($news, 201)
            ->withHeader('Content-type', 'application/json');
        return $return;       
    }
    
    public function viewNews($request, $response, $args) {
        $id = (int) $args['id'];
        $entityManager = $this->container->get('em');
        $newsRepository = $entityManager->getRepository('App\Models\Entity\News');
        $news = $newsRepository->find($id); 
        
        
        if (!$news) {
            $logger = $this->container->get('logger');
            $logger->warning("News {$id} Not Found");
            throw new \Exception("News not Found", 404);
        }    
        $news->author = ["name" => $news->getAuthor()->getUsername(),"id" => $news->getAuthor()->getId()];      

        $return = $response->withJson($news, 200)
            ->withHeader('Content-type', 'application/json');
        return $return;   
    }
  
    public function updateNews($request, $response, $args) {
        $id = (int) $args['id'];
        $params = (object) $request->getParams();
        $jwt = $request->getAttribute('jwt');
        $entityManager = $this->container->get('em');
        $newsRepository = $entityManager->getRepository('App\Models\Entity\News');
        $news = $newsRepository->find($id);   
        
        if (!$news) {
            $logger = $this->container->get('logger');
            $logger->warning("News {$id} Not Found");
            throw new \Exception("News not Found", 404);
        }  

        $repo = $entityManager->getRepository('App\Models\Entity\User');
        $user = $repo->find($jwt['id']);     

        if(!$user || $user->getId() != $news->getAuthor()->getId()){
            $logger = $this->container->get('logger');
            $logger->warning("The user is different from the author on News {$id}.");
            throw new \Exception("Can't update News", 401);
        }
        
        $news->setTitle($params->title)
            ->setAuthor($user)
            ->setStatus($params->status)
            ->setUpdated()
            ->setDescription($params->description);
        /**
         * Persiste a entidade no banco de dados
         */
        $entityManager->persist($news);
        $entityManager->flush();       
        
        $news->author = ["name" => $news->getAuthor()->getUsername(),"id" => $news->getAuthor()->getId()];  
        
        $return = $response->withJson($news, 200)
            ->withHeader('Content-type', 'application/json');
        return $return;       
    }
   
    public function deleteNews($request, $response, $args) {
        $id = (int) $args['id'];
        $jwt = $request->getAttribute('jwt');
        $entityManager = $this->container->get('em');
        $newsRepository = $entityManager->getRepository('App\Models\Entity\News');
        $news = $newsRepository->find($id);   
        
        if (!$news) {
            $logger = $this->container->get('logger');
            $logger->warning("News {$id} Not Found");
            throw new \Exception("News not Found", 404);
        }  

        $repo = $entityManager->getRepository('App\Models\Entity\User');
        $user = $repo->find($jwt['id']);     

        if(!$user || $user->getId() != $news->getAuthor()->getId()){
            $logger = $this->container->get('logger');
            $logger->warning("The user is different from the author on News {$id}.");
            throw new \Exception("Can't delete News", 401);
        }
        
        $entityManager->remove($news);
        $entityManager->flush(); 
        $return = $response->withJson(['msg' => "New {$id} deleted"], 204)
            ->withHeader('Content-type', 'application/json');
        return $return;    
    }
    
}