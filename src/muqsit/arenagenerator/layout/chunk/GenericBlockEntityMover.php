<?php

declare(strict_types=1);

namespace muqsit\arenagenerator\layout\chunk;

use pocketmine\block\tile\Tile;
use pocketmine\nbt\tag\CompoundTag;

final class GenericBlockEntityMover implements BlockEntityMover{

	public static function instance() : self{
		static $instance = null;
		return $instance ??= new self();
	}

	private function __construct(){
	}

	public function move(int $chunk_x, int $chunk_z, CompoundTag $nbt) : void{
		$nbt->setInt(Tile::TAG_X, ($chunk_x << 4) + ($nbt->getInt(Tile::TAG_X) & 0x0f));
		$nbt->setInt(Tile::TAG_Z, ($chunk_z << 4) + ($nbt->getInt(Tile::TAG_Z) & 0x0f));
	}
}