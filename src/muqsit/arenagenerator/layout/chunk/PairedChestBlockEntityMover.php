<?php

declare(strict_types=1);

namespace muqsit\arenagenerator\layout\chunk;

use pocketmine\block\tile\Chest;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;

final class PairedChestBlockEntityMover implements BlockEntityMover{

	public function __construct(){
	}

	public function move(int $chunk_x, int $chunk_z, CompoundTag $nbt) : void{
		$pair_x_tag = $nbt->getTag(Chest::TAG_PAIRX);
		$pair_z_tag = $nbt->getTag(Chest::TAG_PAIRZ);
		if(!($pair_x_tag instanceof IntTag) || !($pair_z_tag instanceof IntTag)){
			return;
		}

		$x = $nbt->getInt(Chest::TAG_PAIRX);
		$z = $nbt->getInt(Chest::TAG_PAIRZ);
		$nbt->setInt(Chest::TAG_PAIRX, ($chunk_x << 4) + ($x & 0x0f) + ($x - $pair_x_tag->getValue()));
		$nbt->setInt(Chest::TAG_PAIRZ, ($chunk_z << 4) + ($z & 0x0f) + ($z - $pair_z_tag->getValue()));
	}
}