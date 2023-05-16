<?php

declare(strict_types=1);

namespace App\Domain\Message;

use Doctrine\ORM\EntityManager;
use App\Domain\User\User;
use App\Domain\Message\Message;
use Slim\Container;


class MessageRepository
{
    public function __construct(EntityManager $em = null){
        $container = require_once '/home/ruben/PHP/my_micro_services/bootstrap.php';
        $this->em = $container->get(EntityManager::class);
    }

    /**
     * @return Message[]
     */
    public function findAll() : array {
        return $this->em->getRepository(Message::class)->findAll();
    }

    /**
     * @param int $id
     * @return Message
     * @throws MessageNotFoundException
     */
    public function findMessageOfId(int $id): Message {

    }
}
