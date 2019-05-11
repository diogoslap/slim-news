<?php
namespace App\v1\Controllers;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use App\Models\Entity\Event;

class EventController {
    
    private $container;
    
    public function __construct($container) {
        $this->container = $container;
    }    
 
    public function listEvents($request, $response, $args) {
        $entityManager = $this->container->get('em');
        $eventRepository = $entityManager->getRepository('App\Models\Entity\Event');
        $events = $eventRepository->findAll();
        foreach($events as $k => $event){            
            $events[$k]->author = ["name"=>$event->getAuthor()->getUsername(),"id"=>$event->getAuthor()->getId()];            
        }
        $logger = $this->container->get('logger');
        $logger->info("List Events.",$request->getHeaders());
        $return = $response->withJson($events, 200)
            ->withHeader('Content-type', 'application/json');
        return $return;        
    }
       
  
    public function createEvent(Request $request, Response $response, array $args) {
        $params = (object) $request->getParams();        
        $jwt = $request->getAttribute('jwt');                
        $entityManager = $this->container->get('em');   
        $repo = $this->container->get('em')->getRepository('App\Models\Entity\User');
        $user = $repo->find($jwt['id']);  
        if(!$user){
            $logger = $this->container->get('logger');
            $logger->warning("Creating Event - User not Exists");
            throw new \Exception("User not Exists", 401);
        }
        $event = (new Event())->setTitle($params->title)
            ->setAuthor($user)
            ->setPublishDate($params->publish_date)
            ->setDescription($params->description);

        $entityManager->persist($event);
        $entityManager->flush();

        $event->author = ["name"=>$event->getAuthor()->getUsername(),"id"=>$event->getAuthor()->getId()];

        $logger = $this->container->get('logger');
        $logger->info('Event Created!', $event->getValues());

        $return = $response->withJson($event, 201)
            ->withHeader('Content-type', 'application/json');
        return $return;       
    }
   
    public function viewEvent($request, $response, $args) {
        $id = (int) $args['id'];
        $entityManager = $this->container->get('em');
        $eventRepository = $entityManager->getRepository('App\Models\Entity\Event');
        $event = $eventRepository->find($id); 
        
        if (!$event) {
            $logger = $this->container->get('logger');
            $logger->warning("Event {$id} Not Found");
            throw new \Exception("Event not Found", 404);
        }   
        
        $event->author = ["name" => $event->getAuthor()->getUsername(),"id" => $event->getAuthor()->getId()];      

        $return = $response->withJson($event, 200)
            ->withHeader('Content-type', 'application/json');
        return $return;   
    }
   
    public function updateEvent($request, $response, $args) {
        $id = (int) $args['id'];
        $params = (object) $request->getParams();
        $jwt = $request->getAttribute('jwt');        
        $entityManager = $this->container->get('em');
        $eventRepository = $entityManager->getRepository('App\Models\Entity\Event');
        $event = $eventRepository->find($id);   
        
        if (!$event) {
            $logger = $this->container->get('logger');
            $logger->warning("Events {$id} Not Found");
            throw new \Exception("Events not Found", 404);
        }  

        $repo = $entityManager->getRepository('App\Models\Entity\User');
        $user = $repo->find($jwt['id']);     

        if(!$user || $user->getId() != $event->getAuthor()->getId()){
            $logger = $this->container->get('logger');
            $logger->warning("The user is different from the author on Event {$id}.");
            throw new \Exception("Can't update Event", 401);
        }
        
        $event->setTitle($params->title)
        ->setAuthor($user)
        ->setPublishDate($params->publish_date)
        ->setDescription($params->description);

        $entityManager->persist($event);
        $entityManager->flush();       
        
        $event->author = ["name" => $event->getAuthor()->getUsername(),"id" => $event->getAuthor()->getId()];  
        
        $return = $response->withJson($event, 200)
            ->withHeader('Content-type', 'application/json');
        return $return;       
    }

    public function deleteEvent($request, $response, $args) {
        $id = (int) $args['id'];
        $jwt = $request->getAttribute('jwt');        
        $entityManager = $this->container->get('em');
        $eventRepository = $entityManager->getRepository('App\Models\Entity\Event');
        $event = $eventRepository->find($id);   

        if (!$event) {
            $logger = $this->container->get('logger');
            $logger->warning("Event {$id} Not Found");
            throw new \Exception("Event not Found", 404);
        }  

        $repo = $entityManager->getRepository('App\Models\Entity\User');
        $user = $repo->find($jwt['id']);     

        if(!$user || $user->getId() != $event->getAuthor()->getId()){
            $logger = $this->container->get('logger');
            $logger->warning("The user is different from the author on Event {$id}.");
            throw new \Exception("Can't delete Event", 401);
        }

        $entityManager->remove($event);
        $entityManager->flush(); 
        $return = $response->withJson(['msg' => "Deleting event {$id}"], 204)
            ->withHeader('Content-type', 'application/json');
        return $return;    
    }
    
}