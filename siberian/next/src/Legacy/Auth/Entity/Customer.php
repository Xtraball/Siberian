<?php

namespace App\Legacy\Auth\Entity;

use App\Legacy\Auth\Repository\CustomerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CustomerRepository::class)]
#[ORM\Table(name: 'customer')]
class Customer
{
    /**
     * PMA structure
     *
     * 1	customer_id Primaire	int(11)		UNSIGNED	Non	Aucun(e)		AUTO_INCREMENT	Modifier Modifier	Supprimer Supprimer
    2	app_id	int(11)			Non	Aucun(e)			Modifier Modifier	Supprimer Supprimer
    3	civility	varchar(5)	utf8mb3_unicode_ci		Oui	NULL			Modifier Modifier	Supprimer Supprimer
    4	firstname	varchar(100)	utf8mb3_unicode_ci		Non	Aucun(e)			Modifier Modifier	Supprimer Supprimer
    5	lastname	varchar(100)	utf8mb3_unicode_ci		Non	Aucun(e)			Modifier Modifier	Supprimer Supprimer
    6	nickname	varchar(16)	utf8mb3_unicode_ci		Non	Aucun(e)			Modifier Modifier	Supprimer Supprimer
    7	phone	varchar(255)	utf8mb3_unicode_ci		Non	Aucun(e)			Modifier Modifier	Supprimer Supprimer
    8	mobile	varchar(255)	utf8mb3_unicode_ci		Non	Aucun(e)			Modifier Modifier	Supprimer Supprimer
    9	birthdate	bigint(20)			Non	Aucun(e)			Modifier Modifier	Supprimer Supprimer
    10	email	varchar(255)	utf8mb3_unicode_ci		Non	Aucun(e)			Modifier Modifier	Supprimer Supprimer
    11	password	varchar(100)	utf8mb3_unicode_ci		Non	Aucun(e)			Modifier Modifier	Supprimer Supprimer
    12	language	varchar(10)	utf8mb3_unicode_ci		Non	Aucun(e)			Modifier Modifier	Supprimer Supprimer
    13	image	varchar(255)	utf8mb3_unicode_ci		Oui	NULL			Modifier Modifier	Supprimer Supprimer
    14	is_custom_image	tinyint(1)			Non	0			Modifier Modifier	Supprimer Supprimer
    15	show_in_social_gaming	tinyint(1)			Non	1			Modifier Modifier	Supprimer Supprimer
    16	can_access_locked_features	tinyint(1)		UNSIGNED	Non	0			Modifier Modifier	Supprimer Supprimer
    17	is_active	tinyint(1)			Non	1			Modifier Modifier	Supprimer Supprimer
    18	is_verified	tinyint(1)			Non	0			Modifier Modifier	Supprimer Supprimer
    19	identity_number	varchar(64)	utf8mb3_unicode_ci		Oui	NULL			Modifier Modifier	Supprimer Supprimer
    20	date_of_birth	varchar(16)	utf8mb3_unicode_ci		Oui	NULL			Modifier Modifier	Supprimer Supprimer
    21	is_deleted	tinyint(1)			Non	0			Modifier Modifier	Supprimer Supprimer
    22	gdpr_token	varchar(255)	utf8mb3_unicode_ci		Oui	NULL			Modifier Modifier	Supprimer Supprimer
    23	remember_token	varchar(100)	utf8mb3_unicode_ci		Oui	NULL			Modifier Modifier	Supprimer Supprimer
    24	privacy_policy	tinyint(1)		UNSIGNED	Non	1			Modifier Modifier	Supprimer Supprimer
    25	communication_agreement	tinyint(1)		UNSIGNED	Non	0			Modifier Modifier	Supprimer Supprimer
    26	session_uuid	varchar(255)	utf8mb3_unicode_ci		Oui	NULL			Modifier Modifier	Supprimer Supprimer
    27	created_at	datetime			Non	Aucun(e)			Modifier Modifier	Supprimer Supprimer
    28	updated_at	datetime			Non	Aucun(e)			Modifier Modifier	Supprimer Supprimer

     *
     *
     */

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'customer_id')]
    private ?int $id = null;

    #[ORM\Column(name: 'app_id', length: 255)]
    private ?string $appId = null;

    #[ORM\Column(name: 'civility', length: 255)]
    private ?string $civility = null;

    #[ORM\Column(name: 'firstname', length: 255)]
    private ?string $firstname = null;

    #[ORM\Column(name: 'lastname', length: 255)]
    private ?string $lastname = null;

    #[ORM\Column(name: 'nickname', length: 255)]
    private ?string $nickname = null;

    #[ORM\Column(name: 'phone', length: 255)]
    private ?string $phone = null;

    #[ORM\Column(name: 'mobile', length: 255)]
    private ?string $mobile = null;

    #[ORM\Column(name: 'birthdate', type: 'bigint')]
    private ?int $birthdate = null;

    #[ORM\Column(name: 'email', length: 255)]
    private ?string $email = null;

    #[ORM\Column(name: 'password', length: 255)]
    private ?string $password = null;

    #[ORM\Column(name: 'language', length: 255)]
    private ?string $language = null;

    #[ORM\Column(name: 'image', length: 255)]
    private ?string $image = null;

    #[ORM\Column(name: 'is_custom_image', type: 'boolean', options: ['default' => false])]
    private bool $isCustomImage = false;

    #[ORM\Column(name: 'show_in_social_gaming', type: 'boolean', options: ['default' => false])]
    private bool $showInSocialGaming = false;

    #[ORM\Column(name: 'can_access_locked_features', type: 'boolean', options: ['default' => false])]
    private bool $canAccessLockedFeatures = false;

    #[ORM\Column(name: 'is_active', type: 'boolean', options: ['default' => false])]
    private bool $isActive = false;

    #[ORM\Column(name: 'is_verified', type: 'boolean', options: ['default' => false])]
    private bool $isVerified = false;

    #[ORM\Column(name: 'identity_number', length: 255)]
    private ?string $identityNumber = null;

    #[ORM\Column(name: 'date_of_birth', length: 255)]
    private ?string $dateOfBirth = null;

    #[ORM\Column(name: 'is_deleted', type: 'boolean', options: ['default' => false])]
    private bool $isDeleted = false;

    #[ORM\Column(name: 'gdpr_token', length: 255)]
    private ?string $gdprToken = null;

    #[ORM\Column(name: 'remember_token', length: 255)]
    private ?string $rememberToken = null;

    #[ORM\Column(name: 'privacy_policy', type: 'boolean', options: ['default' => false])]
    private bool $privacyPolicy = false;

    #[ORM\Column(name: 'communication_agreement', type: 'boolean', options: ['default' => false])]
    private bool $communicationAgreement = false;

    #[ORM\Column(name: 'session_uuid', length: 255)]
    private ?string $sessionUuid = null;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private ?\DateTime $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: 'datetime')]
    private ?\DateTime $updatedAt = null;

    # get email
    public function getEmail(): ?string
    {
        return $this->email;
    }
}