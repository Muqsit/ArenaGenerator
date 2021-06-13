<?php

declare(strict_types=1);

namespace muqsit\arenagenerator\layout\chunk;

use pocketmine\block\tile\Chest;
use pocketmine\block\tile\Tile;
use pocketmine\block\tile\TileFactory;
use pocketmine\nbt\tag\CompoundTag;

final class BlockEntityMoverRegistry{

	/**
	 * @var BlockEntityMover[]
	 *
	 * @phpstan-var array<string, array<BlockEntityMover>>
	 */
	private array $specific = [];

	/** @var BlockEntityMover[] */
	private array $global = [];

	public function __construct(){
		$this->registerGlobal(GenericBlockEntityMover::instance());
		$this->register(TileFactory::getInstance()->getSaveId(Chest::class), new PairedChestBlockEntityMover());
	}

	public function register(string $identifier, BlockEntityMover $movement) : void{
		$this->specific[$identifier][spl_object_id($movement)] = $movement;
	}

	public function registerGlobal(BlockEntityMover $movement) : void{
		$this->global[spl_object_id($movement)] = $movement;
	}

	public function move(int $chunk_x, int $chunk_z, CompoundTag $nbt) : void{
		if(isset($this->specific[$id = $nbt->getString(Tile::TAG_ID)])){
			foreach($this->specific[$id] as $mover){
				$mover->move($chunk_x, $chunk_z, $nbt);
			}
		}

		foreach($this->global as $mover){
			$mover->move($chunk_x, $chunk_z, $nbt);
		}
	}
}