<?php

declare(strict_types=1);

namespace muqsit\arenagenerator\layout\chunk;

use pocketmine\nbt\tag\CompoundTag;

interface EntityMover{

	public function move(int $chunk_x, int $chunk_z, CompoundTag $nbt) : void;
}