<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use App\Domain\User\UserRepository;
use App\Services\UserService;
use App\Domain\User\User;
use App\Domain\Message\Message;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use Slim\Factory\AppFactory;

return function (App $app) {
    
    $container = require __DIR__ . "/../bootstrap.php";	
    $em = $container->get(EntityManager::class);
    $userService = $container->get(UserService::class);
    $key = 'HelloWorld';

    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) use ($container) {
        return $response;
    });

    $app->post('/login', function(Request $request, Response $response) use ($userService, $key) {
        $data = $request->getParsedBody();
        $html = var_export($data, true);
        $user = $em->getRepository(User::class)->findOneBy(['email' => $data["email"]]);

        if ($user && password_verify($data['password'], $user->getPassword())){
            $issuedAt = new DateTimeImmutable();
            $payload = [
                'iat'  => $issuedAt->getTimestamp(),
                'nbf'  => $issuedAt->getTimestamp(),
                'exp'  => $issuedAt->modify('+1seconds')->getTimestamp(),
                'data' => [
                    'email' => $user->getEmail()    
                ],  
            ];  
            $jwt = JWT::encode($payload, $key, 'HS256');
            $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => "Login Successful",
                'jwt' => $jwt,
            ]));
            return $response;
        }
        $response->getBody()->write(json_encode([
                'success' => false,
                'message' => "Username or Password false",
        ]));
        return $response;
    });

    //USER ROUTES

    $app->get('/user', function(Request $request, Response $response) use ($em) {
        $users = $em->getRepository(User::class)->findAll();
        $response->getBody()->write(json_encode($users));
        return $response->withHeader('Content-Type', 'application/json');
    });
    $app->get('/user/{id}', function(Request $request, Response $response, $args) use ($em) {
        $user = $em->getRepository(User::class)->find($args['id']);
        $response->getBody()->write(json_encode($user));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->post('/user', function(Request $request, Response $response) use ($em, $key) {
        $data = $request->getParsedBody();
        $html = var_export($data, true);
        if (isset($data["email"]) && isset($data["password"])){
            $user = new User($data["email"], $data["password"]);
            $em->persist($user);
            $em->flush();
        }
        return $response;
    });

    
    $app->put('/user/{id}', function(Request $request, Response $response, $args) use ($em) {
        $data = $request->getParsedBody();
        $html = var_export($data, true);
        $user = $em->getRepository(User::class)->find($args['id']);
        if ($data["email"]){
            $user->setEmail($data["email"]);
        }
        if ($data["password"]){
            $user->setPassword($data["password"]);
        }
        $em->persist($user);
        $em->flush();
        return $response;
    });

    $app->delete('/user/{id}', function(Request $request, Response $response, $args) use ($em) {
        $user = $em->getRepository(User::class)->find($args['id']);
        $em->remove($user);
        $em->flush();
        return $response;
    });

    //MESSAGE ROUTES

    $app->get('/message', function(Request $request, Response $response) use ($em) {
        $message = $em->getRepository(Message::class)->findAll();
        $response->getBody()->write(json_encode($message));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/message/{id}', function(Request $request, Response $response, $args) use ($em) {
        $message = $em->getRepository(Message::class)->find($args['id']);
        $response->getBody()->write(json_encode($message));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->post('/message', function(Request $request, Response $response) use ($em) {
        $data = $request->getParsedBody();
        $html = var_export($data, true);
        if ($data["content"] && $data["receiver"] && $data["sender"]){
            $receiver = $em->getRepository(User::class)->find($data["receiver"]);
            $sender = $em->getRepository(User::class)->find($data["sender"]);
            $message = new Message($data["content"], $sender, $receiver);
            $em->persist($message);
            $em->flush();
        }
        return $response;
    });

    $app->put('/message/{id}', function(Request $request, Response $response) use ($em) {
        $data = $request->getParsedBody();
        $html = var_export($data, true);
        $message = $em->getRepository(Message::class)->find($args['id']);
        if ($data["content"]){
            $message->setContent($data["content"]);
        }
        $em->persist($message);
        $em->flush();
        return $response;
    });

    $app->delete('/message/{id}', function(Request $request, Response $response, $args) use ($em) {
        $message = $em->getRepository(Message::class)->find($args['id']);
        $em->remove($message);
        $em->flush();
        return $response;
    });

};
