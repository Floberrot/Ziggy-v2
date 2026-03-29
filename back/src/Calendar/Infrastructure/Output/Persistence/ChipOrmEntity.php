<?php

declare(strict_types=1);

namespace App\Calendar\Infrastructure\Output\Persistence;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'chips')]
class ChipOrmEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string', length: 36)]
    private string $chipTypeId;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $date;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $note = null;

    #[ORM\Column(type: 'string', length: 36)]
    private string $authorId;

    #[ORM\ManyToOne(targetEntity: CalendarOrmEntity::class, inversedBy: 'chips')]
    #[ORM\JoinColumn(nullable: false)]
    private CalendarOrmEntity $calendar;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getChipTypeId(): string
    {
        return $this->chipTypeId;
    }

    public function setChipTypeId(string $chipTypeId): void
    {
        $this->chipTypeId = $chipTypeId;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): void
    {
        $this->date = $date;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): void
    {
        $this->note = $note;
    }

    public function getAuthorId(): string
    {
        return $this->authorId;
    }

    public function setAuthorId(string $authorId): void
    {
        $this->authorId = $authorId;
    }

    public function getCalendar(): CalendarOrmEntity
    {
        return $this->calendar;
    }

    public function setCalendar(CalendarOrmEntity $calendar): void
    {
        $this->calendar = $calendar;
    }
}
