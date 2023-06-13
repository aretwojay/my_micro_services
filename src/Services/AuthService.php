<?php

namespace App\Services;

use App\Domain\User\User;
use Doctrine\ORM\EntityManager;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

final class AuthService
{
    private EntityManager $em;
    private $key;

    public function __construct(EntityManager $em)
    {
        $this->key = "HelloWorld";
        $this->em = $em;
    }

    public function checkToken($token){
        return JWT::decode($token, new Key($this->key, 'HS256'));
    }

    public function login(string $email, string $password): string
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
        if (!$user){
            return false;
        }
        $issuedAt = new \DateTimeImmutable();
        $payload = [
            'iat'  => $issuedAt->getTimestamp(),
            'nbf'  => $issuedAt->getTimestamp(),
            'exp'  => $issuedAt->modify('+60minutes')->getTimestamp(),
            'data' => [
                'email' => $user->getEmail()    
            ],  
        ];  
        $jwt = JWT::encode($payload, $this->key, 'HS256');
        if ($user && password_verify($password, $user->getPassword())){
            return $jwt;
        }
        return false;
    }
    public function register(string $email, string $password): string
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($user){
            return "taken";
        }
        $user = new User($email, $password);
        $this->em->persist($user);
        $this->em->flush();   
        return $this->login($email, $password);
    }
}