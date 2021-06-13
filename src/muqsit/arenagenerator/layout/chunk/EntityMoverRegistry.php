<?php

declare(strict_types=1);

namespace muqsit\arenagenerator\layout\chunk;

use pocketmine\nbt\tag\CompoundTag;

final class EntityMoverRegistry{

	/** @var EntityMover[] */
	private array $global = [];

	public function __construct(){
		$this->registerGlobal(GenericEntityMover::instance());
	}

	public function registerGlobal(EntityMover $movement) : void{
		$this->global[spl_object_id($movement)] = $movement;
	}

	public function move(int $chunk_x, int $chunk_z, CompoundTag $nbt) : void{
		foreach($this->global as $mover){
			$mover->move($chunk_x, $chunk_z, $nbt);
		}
	}
}