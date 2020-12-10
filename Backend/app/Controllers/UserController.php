<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Firebase\JWT\JWT;
use Doctrine\ORM\EntityManager;
use User;

class UserController
{
    private $entityManager;

    public function __construct(EntityManager $em)
    {
        $this->entityManager = $em;
    }

    function testAllFields($body){

        $test = true;
    
        $nameRegex = "/[^0-9]{2,30}/";
        $fornameRegex = "/[^0-9]{2,30}/";
        $addressRegex = "/.{6,}/";
        $zipRegex = "/[0-9]{5}/";
        $cityRegex = "/[^0-9]{1,60}/";
        $loginRegex = "/.{5,}/";
        $passwordRegex = "/.{8,}/";
        $mailRegex = "/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/";
    
        $test = preg_match($nameRegex,$body['nom']);
        $test = preg_match($fornameRegex,$body['prenom']);
        $test = preg_match($addressRegex,$body['adresse']);
        $test = preg_match($zipRegex,$body['codePostal']);
        $test = preg_match($cityRegex,$body['ville']);
        $test = preg_match($cityRegex,$body['pays']);
        $test = preg_match($loginRegex,$body['username']);
        $test = preg_match($passwordRegex,$body['motDePasse']);
        $test = preg_match($mailRegex,$body['mail']);
    
        return $test;
      }

    function addUser(Request $request,Response $response,$args) {
        $body = $request->getParsedBody();
        $userRepository= $this->entityManager->getRepository('User');
        $nb = $userRepository->count(['username' => $body['username']]);
        $nb += $userRepository->count(['mail' => $body['mail']]);

        if($nb == 0 && $this->testAllFields($body)){
            $user = new User;
            $user->setNom($body['nom']); 
            $user->setPrenom($body['prenom']);
            $user->setCivilite($body['civilite']);
            $user->setAdresse($body['adresse']);
            $user->setCodePostal($body['codePostal']);
            $user->setVille($body['ville']);
            $user->setPays($body['pays']);
            $user->setTel($body['tel']);
            $user->setUsername($body['username']);
            $user->setMotDePasse($body['motDePasse']);
            $user->setMail($body['mail']);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $response->getBody()->write(json_encode($body));
            return $response->withStatus(200);
        }
        return $response->withStatus(400);
        

        
      }
      
      

      function connexion(Request $request,Response $response,$args) {
        $userRepository= $this->entityManager->getRepository('User');
        
        $secret = "makey1234567";

        $body = $request->getParsedBody();  

        $username = $body['username']; 
        $password = $body['motDePasse'];

        $user = $userRepository->findOneBy(array('username' => $username));

        $issuedAt= time();
        $expirationTime = $issuedAt + 60; 
        // jwtvalid for 60 seconds from the issued time
        $payload = array(
          'userid' => $username,
          'iat' => $issuedAt,
          'exp' => $expirationTime);
      
          if($user != null && $user->getMotDePasse() == $password){
            $token_jwt= JWT::encode($payload, $secret, "HS256");
            $response = $response->withHeader("token", $token_jwt);
            $data = array('success' => true);
            $response->getBody()->write(json_encode($data));
            return $response->withStatus(200);
          }
          else{            
            $data = array('success' => false);
            $response->getBody()->write(json_encode($data));
            return $response->withStatus(400);
          }
          
        }  
}