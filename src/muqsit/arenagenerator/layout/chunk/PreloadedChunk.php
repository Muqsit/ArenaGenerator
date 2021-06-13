<?php

declare(strict_types=1);

namespace muqsit\arenagenerator\layout\chunk;

use pocketmine\block\tile\TileFactory;
use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\format\BiomeArray;
use pocketmine\world\format\Chunk;
use pocketmine\world\format\HeightArray;
use pocketmine\world\format\SubChunk;
use pocketmine\world\World;

final class PreloadedChunk{

	public static function fromChunk(Chunk $chunk) : self{
		$chunk = clone $chunk;
		return new self(
			$chunk->getSubChunks()->toArray(),
			$chunk->getNBTentities(),
			$chunk->getNBTtiles(),
			$chunk->getBiomeIdArray(),
			$chunk->getHeightMapArray(),
			$chunk->isLightPopulated()
		);
	}

	/** @var SubChunk[] */
	private array $sub_chunks;

	/** @var CompoundTag[] */
	private array $entities;

	/** @var CompoundTag[] */
	private array $tiles;

	private string $biome_ids;

	/** @var int[] */
	private array $height_map;

	private ?bool $light_populated;

	/**
	 * @param SubChunk[] $sub_chunks
	 * @param CompoundTag[] $entities
	 * @param CompoundTag[] $tiles
	 * @param string $biome_ids
	 * @param int[] $height_map
	 * @param bool|null $light_populated
	 */
	public function __construct(array $sub_chunks, array $entities, array $tiles, string $biome_ids, array $height_map, ?bool $light_populated){
		$this->sub_chunks = $sub_chunks;
		$this->entities = $entities;
		$this->tiles = $tiles;
		$this->biome_ids = $biome_ids;
		$this->height_map = $height_map;
		$this->light_populated = $light_populated;
	}

	public function copyAt(int $chunk_x, int $chunk_z, World $world) : Chunk{
		$chunk = new Chunk(
			array_map(static function(SubChunk $sub_chunk) : SubChunk{ return clone $sub_chunk; }, $this->sub_chunks),
			[],
			[],
			new BiomeArray($this->biome_ids),
			new HeightArray($this->height_map)
		);
		$chunk->setPopulated(true);
		$chunk->setLightPopulated($this->light_populated);
		$chunk->clearDirtyFlags();

		$world->setChunk($chunk_x, $chunk_z, $chunk);

		$movers = ChunkEntityMovers::instance();

		$entity_factory = EntityFactory::getInstance();
		foreach($this->entities as $data){
			$data = clone $data;
			$movers->entity->move($chunk_x, $chunk_z, $data);
			// $world->addEntity($entity_factory->createFromData($world, $data)); TODO: Find out why this sometimes results in "Entity 645 has already been added to this world"
		}

		$tile_factory = TileFactory::getInstance();
		foreach($this->tiles as $data){
			$data = clone $data;
			$movers->block_entity->move($chunk_x, $chunk_z, $data);
			$tile = $tile_factory->createFromData($world, $data);
			if($tile !== null){
				$world->addTile($tile);
			}
		}

		return $chunk;
	}
}