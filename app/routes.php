<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use App\Domain\User\UserRepository;
use App\Services\AuthService;
use App\Domain\User\User;
use App\Domain\Message\Message;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\PhpRenderer;

return function (App $app) {
    
    $container = require __DIR__ . "/../bootstrap.php";	
    $em = $container->get(EntityManager::class);
    $authService = $container->get(AuthService::class);

    $app->add(function (Request $request, RequestHandler $handler) use ($authService) {
        if (!str_starts_with($request->getRequestTarget(), '/api/')){
            $response = $handler->handle($request);
            return $response;
        }
        if (in_array($request->getRequestTarget(), ['/api/login', '/api/register'])){
            $response = $handler->handle($request);
            return $response->withHeader('Content-Type', 'application/json');
        }
        $response = new \Slim\Psr7\Response();
        if ($request->getHeader("Authorization")) {
            try{
                $token = explode(" ", $request->getHeader("Authorization")[0])[1];
                $decoded = (array) $authService->checkToken($token);
                $response = $handler->handle($request);
                return $response->withHeader('Content-Type', 'application/json');
            }
            catch (Exception $e) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => $e->getMessage(),
                ]));
                return $response->withHeader('Content-Type', 'application/json');
            }
            $response = $handler->handle($request);
            return $response->withHeader('Content-Type', 'application/json');
        }
        $response->getBody()->write(json_encode([
                'success' => false,
                'message' => "Token not found",
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) use ($container) {
        return $response;
    });

    $app->post('/api/login', function(Request $request, Response $response) use ($authService) {
        $data = $request->getParsedBody();
        if (!isset($data["email"]) || !isset($data["password"])) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => "Username or Password false",
            ]));
            return $view->render($response, 'register.html', [
                'error' => 'Username or Password false'
            ]);
        }
        $jwt = $authService->login($data['email'], $data['password']);
        if ($jwt) {
            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => "Login Successful",
                'jwt' => $jwt,
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        }
        $response->getBody()->write(json_encode([
                'success' => false,
                'message' => "Username or Password false",
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    //USER ROUTES

    $app->get('/api/user', function(Request $request, Response $response) use ($em) {
        $users = $em->getRepository(User::class)->findAll();
        $response->getBody()->write(json_encode($users));
        return $response->withHeader('Content-Type', 'application/json');
    });
    $app->get('/api/user/{id}', function(Request $request, Response $response, $args) use ($em) {
        $user = $em->getRepository(User::class)->find($args['id']);
        $response->getBody()->write(json_encode($user));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->post('/api/register', function(Request $request, Response $response) use ($em, $authService) {
        $data = $request->getParsedBody();
        if (!isset($data["email"]) || !isset($data["password"])) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => "Username or Password false",
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        }
        $jwt = $authService->register($data["email"], $data["password"]);
        if ($jwt === "taken"){
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => "Email taken",
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        }
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => "Login Successful",
            'jwt' => $jwt,
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    
    $app->put('/api/user/{id}', function(Request $request, Response $response, $args) use ($em) {
        $data = $request->getParsedBody();
        $user = $em->getRepository(User::class)->find($args['id']);
        if (!$user){
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => "User not found",
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        }
        if ($data["email"]){
            $user->setEmail($data["email"]);
        }
        if ($data["password"]){
            $user->setPassword($data["password"]);
        }
        $em->persist($user);
        $em->flush();
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => "User updated",
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->delete('/api/user/{id}', function(Request $request, Response $response, $args) use ($em) {
        $user = $em->getRepository(User::class)->find($args['id']);
        if (!$user){
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => "User not found",
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        }
        $em->remove($user);
        $em->flush();
        return $response->withHeader('Content-Type', 'application/json');
    });

    //MESSAGE ROUTES

    $app->get('/api/message', function(Request $request, Response $response) use ($em) {
        $message = $em->getRepository(Message::class)->findAll();
        $response->getBody()->write(json_encode($message));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/api/message/{id}', function(Request $request, Response $response, $args) use ($em) {
        $message = $em->getRepository(Message::class)->find($args['id']);
        $response->getBody()->write(json_encode($message));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->post('/api/message', function(Request $request, Response $response) use ($em) {
        $data = $request->getParsedBody();
        if (!isset($data["content"]) || !isset($data["sender"])){
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => "Message sent wrong",
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        }
        if (isset($data["content"]) && isset($data["receiver"]) && isset($data["sender"])){
            $receiver = $em->getRepository(User::class)->find($data["receiver"]);
            $sender = $em->getRepository(User::class)->find($data["sender"]);
            if (!$sender || !$receiver){
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => "Message sent wrong",
                ]));
                return $response->withHeader('Content-Type', 'application/json');
            }
            $message = new Message($data["content"], $sender, $receiver);
            $em->persist($message);
            $em->flush();
            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => $message,
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        }
        else if (isset($data["content"]) && !isset($data["receiver"]) && isset($data["sender"])){
            $sender = $em->getRepository(User::class)->find($data["sender"]);
            if (!$sender){
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => "Message sent wrong",
                ]));
                return $response->withHeader('Content-Type', 'application/json');
            }
            $message = new Message($data["content"], $sender);
            $em->persist($message);
            $em->flush();
            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => $message,
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        }

    });

    $app->put('/api/message/{id}', function(Request $request, Response $response) use ($em) {
        $data = $request->getParsedBody();
        $html = var_export($data, true);
        $message = $em->getRepository(Message::class)->find($args['id']);
        if (!$message){
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => "Message sent wrong",
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        }
        if ($data["content"]){
            $message->setContent($data["content"]);
        }
        $em->persist($message);
        $em->flush();
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => "Message updated",
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->delete('/api/message/{id}', function(Request $request, Response $response, $args) use ($em) {
        $message = $em->getRepository(Message::class)->find($args['id']);
        if (!$message){
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => "Message sent wrong",
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        }
        $em->remove($message);
        $em->flush();
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => "Message deleted",
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });

};
