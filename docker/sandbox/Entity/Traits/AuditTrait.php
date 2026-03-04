<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\SerializedName;

/**
 * Audit Trait - Adds created/updated timestamps to entities.
 *
 * This trait provides automatic timestamp management for entities.
 * It tracks when an entity is first created (createdAt) and when
 * it is last updated (updatedAt). These timestamps are useful for
 * auditing, debugging, and data analysis purposes.
 *
 * The trait uses Doctrine lifecycle callbacks to automatically
 * set these values without requiring manual intervention.
 */
trait AuditTrait
{
    /**
     * Timestamp when the entity was first created.
     * This field is automatically set when the entity is first persisted.
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[SerializedName("CreatedAt")]
    public \DateTimeInterface $createdAt;

    /**
     * Timestamp when the entity was last updated.
     * This field is automatically updated on each entity update.
     * It is nullable because it hasn't been set on initial creation.
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[SerializedName("LastUpdateAt")]
    public ?\DateTimeInterface $updatedAt = null;

    /**
     * Doctrine PrePersist lifecycle callback.
     * Called before the entity is first persisted to the database.
     * Sets the initial creation timestamp.
     */
    #[ORM\PrePersist]
    public function initAudit(): void
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * Doctrine PreUpdate lifecycle callback.
     * Called before the entity is updated in the database.
     * Updates the last modified timestamp.
     */
    #[ORM\PreUpdate]
    public function updateAudit(): void
    {
        $this->updatedAt = new \DateTime();
    }
}
