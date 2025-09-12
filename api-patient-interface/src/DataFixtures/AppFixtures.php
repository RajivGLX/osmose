<?php

namespace App\DataFixtures;

use App\Entity\Administrator;
use App\Entity\Center;
use App\Entity\Patient;
use App\Entity\Region;
use App\Entity\Role;
use App\Repository\CenterRepository;
use App\Repository\RegionRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use App\Entity\User;
use App\Entity\Booking;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class AppFixtures extends Fixture
{
    private $entityManager;
    private $encoder;
    private $kernel;
    private $regionRepository;
    private $centerRepository;
    private $userPasswordHasher;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $encoder,
        KernelInterface $kernel,
        RegionRepository $regionRepository,
        CenterRepository $centerRepository,
        UserPasswordHasherInterface $userPasswordHasher
    )
    {
        $this->entityManager = $entityManager;
        $this->encoder = $encoder;
        $this->kernel = $kernel;
        $this->regionRepository = $regionRepository;
        $this->centerRepository = $centerRepository;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager)
    {
        $data = $this->parseDialysisData();
        $faker = Factory::create('fr_FR');
        $this->loadUser($manager);

        // Création des régions
        foreach ($data as $zone => $zoneCenter){
            if ($zone != 0){
                $region = new Region();
                $region->setName($zone);
                $manager->persist($region);
                $manager->flush();
            }
        }

        //Création des centres
        foreach ($data as $zone => $zoneCenter){
            foreach ($zoneCenter as $dataCenter){
                $center = new Center();
                $region = $this->regionRepository->findOneByName($zone);

                $center->setBand($dataCenter["group"]);
                $center->setAddress($dataCenter["adresse"]);
                $center->setZipcode(floatval($dataCenter["departement"]));
                $center->setCity($dataCenter["ville"]);
                $center->setName($dataCenter["name"]);
                $center->setPhone($dataCenter["phone"]);
                $center->setUrl($dataCenter["url"]);
                $center->setPlaceAvailable(1);
                $center->setLatitudeLongitude($dataCenter["latitudeLongitude"]);
                $center->setRegion($region);
                $center->setActive(true);
                $center->setDeleted(false);
                $manager->persist($center);
                $manager->flush();

                $allCenters[] = $center;
            }

        }


        // Création des Admin
        for ($i = 1; $i <= 5; $i++) {
            $user = new User();
            $admin = new Administrator();

            $hash = $this->encoder->hashPassword($user, 'password');

            $user->setFirstName($faker->firstname());
            $user->setLastname($faker->lastname());
            $user->setEmail($faker->email);
            $user->setPassword($hash);
            $user->setRoles(['ROLE_ADMIN']);
            $user->setEmail($faker->email);
            $user->setValid(true);
            $user->setDeleted(false);
            $user->setAdmin(true);
            $manager->persist($user);

            $center    = $allCenters[mt_rand(0, count($allCenters) - 1)];
            $admin->addCenter($center);
            $admin->setService('secretariat');
            $admin->setUser($user);
            $manager->persist($admin);

        }

        // Création des Patient
        for ($i = 1; $i <= 15; $i++) {
            $user = new User();
            $patient = new Patient();

            $hash = $this->encoder->hashPassword($user, 'password');

            $user->setFirstName($faker->firstname());
            $user->setLastname($faker->lastname());
            $user->setEmail($faker->email);
            $user->setPassword($hash);
            $user->setRoles(['ROLE_PATIENT']);
            $user->setEmail($faker->email);
            $user->setValid(true);
            $user->setDeleted(false);
            $user->setAdmin(false);
            $manager->persist($user);

            $patient->setPhone('0685785256');
            $patient->setUser($user);
            $manager->persist($patient);

            $patients[] = $patient;
        }

        // Ont charge tous les id des centres pour pouvoir les dispatcher
//        $centers = $this->centerRepository->findAll();
//        $centerIds = [];
//        foreach ($centers as $center) {
//            $centerIds[] = $center->getId();
//        }

        // Nous gérons les annonces
//        for ($i = 1; $i <= 30; $i++) {
//
//            // Gestion des réservations
//            for ($j = 1; $j <= mt_rand(0, 10); $j++) {
//                $booking = new Booking();
//
//                $randomCenterId = $centerIds[array_rand($centerIds)];
//
//                // Récupérer le centre correspondant à l'ID sélectionné aléatoirement
//                $center = $this->centerRepository->find($randomCenterId);
//
//                $createdAt = DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months'));
//                $startDate = DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-3 months'));
//                // Gestion de la date de fin
//                $duration  = mt_rand(3, 10);
//                $endDate   = (clone $startDate)->modify("+$duration days");
//
//                $booker    = $patients[mt_rand(0, count($patients) - 1)];
//
//                $booking->setPatient($booker);
//                $booking->setCenter($center);
//                $booking->setStartDate($startDate);
//                $booking->setEndDate($endDate);
//                $booking->setCreateAt($createdAt);
//
//                $manager->persist($booking);
//
//            }
//        }

        $manager->flush();
    }

    public function loadUser($manager): void
    {
        $roles = [
            'ROLE_ADMIN_OSMOSE' => 'Admin Osmose',
            'ROLE_SUPER_ADMIN' => 'Super Admin',
            'ROLE_ADMIN' => 'Admin',
            'ROLE_PATIENT' => 'Patient',
        ];

        foreach ($roles as $key => $value) {
            if (!$manager->getRepository(Role::class)->findByRoleName([$key])) {
                $role = new Role();
                $role->setRoleName($key);
                $role->setLibelle($value);
                $manager->persist($role);
                $manager->flush();
            }
        }

        $user = new User();
        if (!$manager->find(User::class, 1)) {
            $user->setFirstname('admin');
            $user->setLastname('admin');
            $user->setRoles(['ROLE_ADMIN_OSMOSE']);
            $user->setPassword($this->userPasswordHasher->hashPassword($user, 'admin'));
            $user->setEmail('admin@admin.com');
            $user->setValid(true);
            $user->setDeleted(false);
            $user->setAdmin(true);
            $manager->persist($user);

            $manager->flush();
        }
    }

    public function parseDialysisData()
    {
        $projectDir = $this->kernel->getProjectDir();
        $filePath = $projectDir . '/templates/for_fixtures/index.html.twig';
        $html_data = file_get_contents($filePath);

        $regions = [];

        $crawler = new Crawler($html_data);

        // Parcourir chaque élément div avec la classe "qcpd-single-list-pd"
        $crawler->filter('div.qcpd-single-list-pd')->each(function (Crawler $regionDiv) use (&$regions) {
            // Récupérer le titre de la région (h2)
            $region_name = $regionDiv->filter('h2')->text();
            // Initialiser un tableau pour stocker les centres de dialyse de la région
            $centers = [];

            // Parcourir chaque élément li dans la région
            $regionDiv->filter('ul li')->each(function (Crawler $li) use (&$centers) {
                // Extraire les informations du centre de dialyse
                $center_info = [
                    'name' => $li->attr('data-title'),
                    'phone' => $li->attr('data-phone'),
                    'address' => $li->attr('data-address'),
                    'url' => $li->attr('data-url'),
                    'latitudeLongitude' => $li->attr('data-latlon'),
                ];

                // Enlever tous les espaces et les caractères qui ne sont pas des entiers du numéro de téléphone
                $center_info['phone'] = preg_replace('/[^0-9]/', '', $center_info['phone']);
                // Met la chaine en minuscule
                $center_info['name'] = strtolower($center_info['name']);
                // Extraire le groupe du centre de dialyse depuis le premier <span> de la balise <div class="sld_simple_description">
                $center_info['group'] = trim($li->filter('div.sld_simple_description > span')->eq(0)->text());
                // Ajouter les informations au tableau des centres
                $centers[] = $center_info;

            });

            // Extraire les villes, les départements et les adresses pour chaque centre
            foreach ($centers as &$center) {
                // Extraction de la ville (en supprimant "CEDEX" s'il est présent)
                $villeMatches = [];
                preg_match('/([A-Z\s]+)(?: CEDEX)?\s*$/', $center['address'], $villeMatches);
                $center['ville'] = trim($villeMatches[1] ?? '');
                $departementMatches = [];
                preg_match('/\b\d{5}\b/', $center['address'], $departementMatches);
                $center['departement'] = $departementMatches[0] ?? '';
                $adresse = trim(str_replace([$center['ville'], $center['departement']], '', $center['address']));
                $center['adresse'] = $adresse;
            }

            // Ajouter les centres de dialyse au tableau des régions
            $regions[$region_name] = $centers;
        });

        // Retourner les données
        return $regions;
    }

}

