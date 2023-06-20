<?php

declare(strict_types=1);

namespace muqsit\arenagenerator\layout\chunk;

use pocketmine\block\tile\Tile;
use pocketmine\block\tile\TileFactory;
use pocketmine\world\format\Chunk;
use pocketmine\world\format\SubChunk;
use pocketmine\world\World;

final class PreloadedChunk{

    public static function fromChunk(Chunk $chunk) : self{
		$chunk = clone $chunk;
		return new self(
			$chunk->getSubChunks(),
			$chunk->getTiles(),
			$chunk->getHeightMapArray(),
			$chunk->isLightPopulated()
		);
	}

    /** @var SubChunk[] */
	private array $sub_chunks;

    /** @var Tile[] */
	private array $tiles;

    /** @var int[] */
    private array $height_map;

    private ?bool $light_populated;

    /**
	 * @param SubChunk[] $sub_chunks
	 * @param Tile[] $tiles
	 * @param int[] $height_map
	 * @param bool|null $light_populated
	 */
	public function __construct(array $sub_chunks, array $tiles, array $height_map, ?bool $light_populated){
		$this->sub_chunks = $sub_chunks;
		$this->tiles = $tiles;
		$this->height_map = $height_map;
		$this->light_populated = $light_populated;
	}

	public function copyTo(int $chunk_x, int $chunk_z, World $world) : Chunk{
		$chunk = new Chunk(
			array_map(static function(SubChunk $sub_chunk) : SubChunk{ return clone $sub_chunk; }, $this->sub_chunks),
            true
		);
        $chunk->setHeightMapArray($this->height_map);
		$chunk->setLightPopulated($this->light_populated);
		$chunk->clearTerrainDirtyFlags();

		$world->setChunk($chunk_x, $chunk_z, $chunk);

		$movers = ChunkEntityMovers::instance();

		$tile_factory = TileFactory::getInstance();
		foreach($this->tiles as $data){
			$data = clone $data;
			$movers->block_entity->move($chunk_x, $chunk_z, $data->getCleanedNBT());
			$tile = $tile_factory->createFromData($world, $data->getCleanedNBT());
			if($tile !== null){
				$world->addTile($tile);
			}
		}

		return $chunk;
	}
}