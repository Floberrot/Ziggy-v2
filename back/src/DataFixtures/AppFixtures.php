<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Calendar\Domain\Model\Calendar;
use App\Calendar\Domain\Model\CalendarId;
use App\Calendar\Domain\Model\ChipId;
use App\Calendar\Domain\Repository\CalendarRepository;
use App\Cat\Domain\Model\Cat;
use App\Cat\Domain\Model\CatId;
use App\Cat\Domain\Model\CatName;
use App\Cat\Domain\Repository\CatRepository;
use App\ChipType\Domain\Model\ChipColor;
use App\ChipType\Domain\Model\ChipType;
use App\ChipType\Domain\Model\ChipTypeId;
use App\ChipType\Domain\Repository\ChipTypeRepository;
use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\Role;
use App\Identity\Domain\Model\User;
use App\Identity\Domain\Model\UserId;
use App\Identity\Domain\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\InMemoryUser;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly CatRepository $catRepository,
        private readonly ChipTypeRepository $chipTypeRepository,
        private readonly CalendarRepository $calendarRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // ------------------------------------------------------------------ users
        $alice = $this->createUser('flours@ziggy.dev', 'password', Role::OWNER, 'Flours');
        $bob   = $this->createUser('wolf@ziggy.dev', 'password', Role::PET_SITTER, 'Wolf');

        // getUserIdentifier() returns email — that's what controllers store as ownerId
        $aliceEmail = $alice->email()->value();

        // ------------------------------------------------------------------ chip types (owned by Alice)
        $repas      = $this->createChipType('Repas', '#22c55e', $aliceEmail);
        $veto       = $this->createChipType('Vétérinaire', '#ef4444', $aliceEmail);
        $medicament = $this->createChipType('Médicament', '#f97316', $aliceEmail);
        $toilettage = $this->createChipType('Toilettage', '#a855f7', $aliceEmail);
        $vaccin     = $this->createChipType('Vaccin', '#3b82f6', $aliceEmail);
        $calin      = $this->createChipType('Câlin', '#ec4899', $aliceEmail);

        // ------------------------------------------------------------------ cats (owned by Alice)
        $ziggy = Cat::add(
            id: CatId::generate(),
            name: new CatName('Ziggy'),
            ownerId: $aliceEmail,
            weight: 4.2,
            breed: 'Tabby',
            colors: ['#f97316', '#1c1917'],
        );
        $this->catRepository->save($ziggy);

        $luna = Cat::add(
            id: CatId::generate(),
            name: new CatName('Luna'),
            ownerId: $aliceEmail,
            weight: 3.8,
            breed: 'Persan',
            colors: ['#f5f5f4'],
        );
        $this->catRepository->save($luna);

        // Mochi has no calendar yet — showcases the empty state in the UI
        $mochi = Cat::add(
            id: CatId::generate(),
            name: new CatName('Mochi'),
            ownerId: $aliceEmail,
            weight: 4.5,
            breed: 'Scottish Fold',
            colors: ['#a8a29e', '#f5f5f4'],
        );
        $this->catRepository->save($mochi);

        $aliceId = $alice->id()->value();
        $bobId   = $bob->id()->value();

        // ------------------------------------------------------------------ Ziggy's calendar (3 weeks of activity)
        $ziggyCalendar = Calendar::create(
            id: CalendarId::generate(),
            catId: $ziggy->id()->value(),
        );

        // Week −3
        $ziggyCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('-21 days 08:00'), $aliceId);
        $ziggyCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('-21 days 18:30'), $aliceId);
        $ziggyCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('-20 days 08:00'), $aliceId);
        $ziggyCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('-20 days 18:30'), $aliceId);
        $ziggyCalendar->placeChip(ChipId::generate(), $veto->id()->value(), new \DateTimeImmutable('-19 days 10:00'), $aliceId, 'Bilan annuel, RAS. Poids stable.');
        $ziggyCalendar->placeChip(ChipId::generate(), $vaccin->id()->value(), new \DateTimeImmutable('-19 days 10:15'), $aliceId, 'Rappel typhus + leucose');
        $ziggyCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('-18 days 08:00'), $bobId);
        $ziggyCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('-18 days 18:30'), $bobId);
        $ziggyCalendar->placeChip(ChipId::generate(), $calin->id()->value(), new \DateTimeImmutable('-18 days 20:00'), $bobId);

        // Week −2
        $ziggyCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('-14 days 08:00'), $aliceId);
        $ziggyCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('-14 days 18:30'), $aliceId);
        $ziggyCalendar->placeChip(ChipId::generate(), $toilettage->id()->value(), new \DateTimeImmutable('-13 days 14:00'), $aliceId, 'Brossage complet, griffes taillées');
        $ziggyCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('-13 days 18:30'), $aliceId);
        $ziggyCalendar->placeChip(ChipId::generate(), $medicament->id()->value(), new \DateTimeImmutable('-12 days 08:00'), $aliceId, 'Vermifuge — Milbemax 1 comprimé');
        $ziggyCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('-12 days 18:30'), $bobId);
        $ziggyCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('-11 days 08:00'), $bobId);
        $ziggyCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('-11 days 18:30'), $bobId);
        $ziggyCalendar->placeChip(ChipId::generate(), $calin->id()->value(), new \DateTimeImmutable('-11 days 19:00'), $bobId);
        $ziggyCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('-10 days 08:00'), $aliceId);
        $ziggyCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('-10 days 18:30'), $aliceId);

        // Week −1
        $ziggyCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('-7 days 08:00'), $aliceId);
        $ziggyCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('-7 days 18:30'), $aliceId);
        $ziggyCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('-6 days 08:00'), $bobId);
        $ziggyCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('-6 days 18:30'), $bobId);
        $ziggyCalendar->placeChip(ChipId::generate(), $veto->id()->value(), new \DateTimeImmutable('-5 days 09:30'), $aliceId, 'Légère conjonctivite œil droit. Collyre Tobradex 5j.');
        $ziggyCalendar->placeChip(ChipId::generate(), $medicament->id()->value(), new \DateTimeImmutable('-5 days 09:35'), $aliceId, 'Collyre Tobradex — 1 goutte matin et soir');
        $ziggyCalendar->placeChip(ChipId::generate(), $medicament->id()->value(), new \DateTimeImmutable('-4 days 08:05'), $aliceId, 'Collyre J2 matin');
        $ziggyCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('-4 days 08:00'), $aliceId);
        $ziggyCalendar->placeChip(ChipId::generate(), $medicament->id()->value(), new \DateTimeImmutable('-4 days 19:00'), $aliceId, 'Collyre J2 soir');
        $ziggyCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('-3 days 08:00'), $aliceId);
        $ziggyCalendar->placeChip(ChipId::generate(), $medicament->id()->value(), new \DateTimeImmutable('-3 days 08:05'), $aliceId, 'Collyre J3 matin');
        $ziggyCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('-3 days 18:30'), $aliceId);
        $ziggyCalendar->placeChip(ChipId::generate(), $medicament->id()->value(), new \DateTimeImmutable('-3 days 19:00'), $aliceId, 'Collyre J3 soir');
        $ziggyCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('-2 days 08:00'), $aliceId);
        $ziggyCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('-2 days 18:30'), $aliceId);
        $ziggyCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('yesterday 08:00'), $aliceId);
        $ziggyCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('yesterday 18:30'), $aliceId);
        $ziggyCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('today 08:00'), $aliceId);
        $ziggyCalendar->placeChip(ChipId::generate(), $calin->id()->value(), new \DateTimeImmutable('today 09:15'), $aliceId);

        $this->calendarRepository->save($ziggyCalendar);

        // ------------------------------------------------------------------ Luna's calendar (2 weeks of activity)
        $lunaCalendar = Calendar::create(
            id: CalendarId::generate(),
            catId: $luna->id()->value(),
        );

        $lunaCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('-14 days 08:00'), $aliceId);
        $lunaCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('-14 days 18:30'), $aliceId);
        $lunaCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('-13 days 08:00'), $aliceId);
        $lunaCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('-13 days 18:30'), $aliceId);
        $lunaCalendar->placeChip(ChipId::generate(), $vaccin->id()->value(), new \DateTimeImmutable('-12 days 10:00'), $aliceId, 'Primo-vaccination coryza');
        $lunaCalendar->placeChip(ChipId::generate(), $toilettage->id()->value(), new \DateTimeImmutable('-12 days 15:00'), $aliceId, 'Démêlage et brossage fourrure longue');
        $lunaCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('-11 days 08:00'), $aliceId);
        $lunaCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('-11 days 18:30'), $aliceId);
        $lunaCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('-7 days 08:00'), $bobId);
        $lunaCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('-7 days 18:30'), $bobId);
        $lunaCalendar->placeChip(ChipId::generate(), $calin->id()->value(), new \DateTimeImmutable('-7 days 20:00'), $bobId);
        $lunaCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('-6 days 08:00'), $bobId);
        $lunaCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('-6 days 18:30'), $bobId);
        $lunaCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('-3 days 08:00'), $aliceId);
        $lunaCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('-3 days 18:30'), $aliceId);
        $lunaCalendar->placeChip(ChipId::generate(), $toilettage->id()->value(), new \DateTimeImmutable('-2 days 14:00'), $aliceId, 'Nœuds derrière les oreilles, démêlant appliqué');
        $lunaCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('yesterday 08:00'), $aliceId);
        $lunaCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('yesterday 18:30'), $aliceId);
        $lunaCalendar->placeChip(ChipId::generate(), $repas->id()->value(), new \DateTimeImmutable('today 08:00'), $aliceId);

        $this->calendarRepository->save($lunaCalendar);
    }

    private function createUser(string $email, string $plainPassword, Role $role, string $username): User
    {
        $hashedPassword = $this->passwordHasher->hashPassword(
            new InMemoryUser($email, ''),
            $plainPassword,
        );

        $user = User::register(
            id: UserId::generate(),
            email: new Email($email),
            hashedPassword: $hashedPassword,
            role: $role,
            username: $username,
        );

        $this->userRepository->save($user);

        return $user;
    }

    private function createChipType(string $name, string $color, string $ownerId): ChipType
    {
        $chipType = ChipType::create(
            id: ChipTypeId::generate(),
            name: $name,
            color: new ChipColor($color),
            ownerId: $ownerId,
        );

        $this->chipTypeRepository->save($chipType);

        return $chipType;
    }
}
