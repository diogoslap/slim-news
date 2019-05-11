<?php

namespace App\Auth;

class Authenticator {
    private $container;
    

    public function __construct($container)
    {
        $this->container = $container;
    }
   

    public function __invoke(array $arguments) {
      $username = $arguments['user'];   
      $repo = $this->container->get('em')->getRepository('App\Models\Entity\User');
      $user = $repo->findOneBy(['username'=>$username]);       
      if (!$user || !password_verify($arguments['password'],$user->getPassword())) {
        return false;
      }

      return $user;     
    }
}