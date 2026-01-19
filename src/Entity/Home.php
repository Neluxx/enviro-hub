<?php

declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'homes')]
class Home
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string')]
    private string $title;

    #[ORM\Column(type: 'string')]
    private string $identifier;

    #[ORM\Column(type: 'datetime')]
    private DateTime $createdAt;

    #[ORM\Column(type: 'datetime')]
    private DateTime $modifiedAt;

    public function __construct(string $title, string $identifier)
    {
        $this->title = $title;
        $this->identifier = $identifier;
        $this->createdAt = new DateTime();
        $this->modifiedAt = new DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
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
