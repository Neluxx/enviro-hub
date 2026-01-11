<?php

declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'nodes')]
class Node
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 36)]
    private string $uuid;

    #[ORM\Column(type: 'string')]
    private string $title;

    #[ORM\Column(type: 'integer')]
    private int $homeId;

    #[ORM\Column(type: 'datetime')]
    private DateTime $createdAt;

    #[ORM\Column(type: 'datetime')]
    private DateTime $modifiedAt;

    public function __construct(string $uuid, string $title, int $homeId)
    {
        $this->uuid = $uuid;
        $this->title = $title;
        $this->homeId = $homeId;
        $this->createdAt = new DateTime();
        $this->modifiedAt = new DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getHomeId(): int
    {
        return $this->homeId;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getModifiedAt(): DateTime
    {
        return $this->modifiedAt;
    }
}
