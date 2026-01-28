<?php

declare(strict_types=1);

namespace App\Node\Entity;

use App\Api\SensorData\Entity\SensorData;
use App\Node\Repository\NodeRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NodeRepository::class)]
#[ORM\Table(name: 'nodes')]
class Node
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 36)]
    private string $uuid;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'integer')]
    private int $homeId;

    #[ORM\OneToMany(targetEntity: SensorData::class, mappedBy: 'node')]
    private Collection $sensorData;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $modifiedAt;

    public function __construct(string $uuid, string $title, int $homeId)
    {
        $this->uuid = $uuid;
        $this->title = $title;
        $this->homeId = $homeId;
        $this->sensorData = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
        $this->modifiedAt = new DateTimeImmutable();
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

    /**
     * @return Collection<int, SensorData>
     */
    public function getSensorData(): Collection
    {
        return $this->sensorData;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getModifiedAt(): DateTimeImmutable
    {
        return $this->modifiedAt;
    }
}
