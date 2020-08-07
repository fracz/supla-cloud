<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace SuplaBundle\Entity;

use Assert\Assertion;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="SuplaBundle\Repository\ChannelValueRepository")
 * @ORM\Table(name="supla_channel_value")
 */
class ChannelValue {
    use BelongsToUser;

    /**
     * @ORM\Id
     * @ORM\Column(name="channel_id", type="integer")
     * @ORM\JoinColumn(name="channel_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $channel;

    /**
     * @ORM\Column(name="update_time", type="utcdatetime", nullable=true)
     */
    private $updateTime;

    /**
     * @ORM\Column(name="valid_to", type="utcdatetime", nullable=true)
     */
    private $validTo;

    /**
     * @ORM\Column(name="guid", type="binary", length=8, nullable=false, unique=false)
     */
    private $value;
}
