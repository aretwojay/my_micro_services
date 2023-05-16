<?php

// src/Domain/User.php

namespace App\Domain\User;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

#[Entity, Table(name: 'user')]
class User
{
    #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
    public int $id;

    #[Column(type: 'string', unique: true, nullable: false)]
    public string $email;

    #[Column(type: 'string', nullable: false)]
    public string $password;

    #[Column(name: 'registered_at', type: 'datetimetz_immutable', nullable: false)]
    public DateTimeImmutable $registeredAt;

    public function __construct(string $email, string $password)
    {
        $this->email = $email;
        $this->password = password_hash($password, PASSWORD_DEFAULT);
        $this->registeredAt = new DateTimeImmutable('now');
    }

    public function setEmail($email): void
    {
        $this->email = $email;
    }

    public function setPassword($password): void
    {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }


    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    
    public function getPassword(): string
    {
        return $this->password;
    }


    public function getRegisteredAt(): DateTimeImmutable
    {
        return $this->registeredAt;
    }
}