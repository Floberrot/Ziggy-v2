<?php

declare(strict_types=1);

namespace App\Calendar\Infrastructure\Output\Persistence;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'calendars')]
class CalendarOrmEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string', length: 36, unique: true)]
    private string $catId;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    /** @var Collection<int, ChipOrmEntity> */
    #[ORM\OneToMany(targetEntity: ChipOrmEntity::class, mappedBy: 'calendar', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $chips;

    public function __construct()
    {
        $this->chips = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getCatId(): string
    {
        return $this->catId;
    }

    public function setCatId(string $catId): void
    {
        $this->catId = $catId;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /** @return Collection<int, ChipOrmEntity> */
    public function getChips(): Collection
    {
        return $this->chips;
    }

    public function addChip(ChipOrmEntity $chip): void
    {
        if (!$this->chips->contains($chip)) {
            $this->chips->add($chip);
            $chip->setCalendar($this);
        }
    }
}
