<?php

declare(strict_types=1);

namespace App\Domain\User;

use Doctrine\ORM\EntityManager;
use App\Domain\User\User;
use Slim\Container;


class UserRepository
{
    public function __construct(){
        $container = require_once __DIR__ . '/../../../bootstrap.php';
        $this->em = $container->get(EntityManager::class);
    }

    /**
     * @return User[]
     */
    public function findAll() : array {
        return $this->em->getRepository(User::class)->findAll();
    }

    /**
     * @param int $id
     * @return User
     * @throws UserNotFoundException
     */
    public function findUserOfId(int $id): User {

    }
}
