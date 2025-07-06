<?php

namespace App\Legacy\Auth\Entity;

use App\Legacy\Auth\Repository\AdminRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdminRepository::class)]
#[ORM\Table(name: 'admin')]
class Admin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'admin_id')]
    private ?int $id = null;

    #[ORM\Column(name: 'parent_id', length: 255)]
    private ?string $parentId = null;

    #[ORM\Column(name: 'role_id', length: 255)]
    private ?string $roleId = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 100)]
    private ?string $password = null;

    #[ORM\Column(name: 'is_allowed_to_add_pages', type: 'boolean', options: ['default' => false])]
    private bool $isAllowedToAddPages = false;

    #[ORM\Column(name: 'is_allowed_to_manage_tour', type: 'boolean', options: ['default' => false])]
    private bool $isAllowedToManageTour = false;

    #[ORM\Column(name: 'publication_access_type', length: 255)]
    private ?string $publicationAccessType = null;

    #[ORM\Column(name: 'generate_apk', length: 255)]
    private ?string $generateApk = null;

    #[ORM\Column(length: 255)]
    private ?string $company = null;

    #[ORM\Column(length: 255)]
    private ?string $website = null;

    #[ORM\Column(length: 100)]
    private ?string $firstname = null;

    #[ORM\Column(length: 100)]
    private ?string $lastname = null;

    #[ORM\Column(type: 'text')]
    private ?string $address = null;

    #[ORM\Column(length: 255)]
    private ?string $address2 = null;

    #[ORM\Column(length: 255)]
    private ?string $city = null;

    #[ORM\Column(name: 'zip_code', length: 255)]
    private ?string $zipCode = null;

    #[ORM\Column(name: 'region_code', length: 255)]
    private ?string $regionCode = null;

    #[ORM\Column(length: 255)]
    private ?string $region = null;

    #[ORM\Column(name: 'country_code', length: 10)]
    private ?string $countryCode = null;

    #[ORM\Column(length: 100)]
    private ?string $country = null;

    #[ORM\Column(type: 'text')]
    private ?string $preferences = null;

    #[ORM\Column(length: 20)]
    private ?string $phone = null;

    #[ORM\Column(name: 'vat_number', length: 20)]
    private ?string $vatNumber = null;

    #[ORM\Column(name: 'accept_tos', type: 'boolean', options: ['default' => false])]
    private bool $acceptTos = false;

    #[ORM\Column(name: 'last_action', type: 'datetime')]
    private ?\DateTime $lastAction = null;

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