<?php

// src/Domain/Message.php

namespace App\Domain\Message;

use App\Domain\User\User;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity, Table(name: 'message')]
class Message
{
    #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
    public int $id;

    #[Column(type: 'string', nullable: false)]
    public string $content;

    #[JoinColumn(name: 'sender_id', referencedColumnName: 'id', nullable: false), ManyToOne(targetEntity: User::class)]
    public User $sender;

    #[JoinColumn(name: 'receiver_id', referencedColumnName: 'id', nullable: true), ManyToOne(targetEntity: User::class)]
    public User $receiver;

    #[Column(name: 'send_at', type: 'datetimetz_immutable', nullable: false)]
    public DateTimeImmutable $sendAt;

    public function __construct(string $content, User $sender, User $receiver = null)
    {
        $this->content = $content;
        $this->sender = $sender;
        if (isset($receiver)){
            $this->receiver = $receiver;
        }
        $this->sendAt = new DateTimeImmutable('now');
    }
    
    
    public function setContent($content): void
    {
        $this->content = $content;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getSender(): User
    {
        return $this->sender;
    }

    public function getReceiver(): User
    {
        return $this->receiver;
    }

    public function getSendAt(): DateTimeImmutable
    {
        return $this->sendAt;
    }
}