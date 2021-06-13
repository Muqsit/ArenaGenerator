<?php

declare(strict_types=1);

namespace muqsit\arenagenerator\layout\chunk;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;

final class GenericEntityMover implements EntityMover{

	public static function instance() : self{
		static $instance = null;
		return $instance ??= new self();
	}

	private function __construct(){
	}

	public function move(int $chunk_x, int $chunk_z, CompoundTag $nbt) : void{
		$pos = $nbt->getListTag("Pos");
		assert($pos !== null);

		$x = $pos->get(0)->getValue();
		$fx = (int) floor($x);
		$pos->set(0, new DoubleTag(($chunk_x << 4) + ($fx & 0x0f) + ($x - $fx)));

		$z = $pos->get(2)->getValue();
		$fz = (int) floor($z);
		$pos->set(2, new DoubleTag(($chunk_z << 4) + ($fz & 0x0f) + ($z - $fz)));
	}
}