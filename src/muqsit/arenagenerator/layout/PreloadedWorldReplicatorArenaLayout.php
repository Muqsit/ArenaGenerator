<?php

declare(strict_types=1);

namespace muqsit\arenagenerator\layout;

use muqsit\arenagenerator\ArenaBoundingBox;
use muqsit\arenagenerator\layout\chunk\PreloadedChunk;
use pocketmine\world\World;

/**
 * ArenaLayout implementation that stores chunks in-memory to reduce overhead
 * of reading chunks from world everytime the layout is re-used. As this layout
 * stores chunks in memory, it is more memory intensive than
 * RealtimeWorldReplicatorArenaLayout. However, the runtime cost is a lot lesser
 * as the process of reading chunks from disk and decoding them is eliminated.
 */
final class PreloadedWorldReplicatorArenaLayout implements ArenaLayout{

	/**
	 * Reads chunks from a given world once and stores them for reuse.
	 *
	 * @param World $world
	 * @param ArenaBoundingBox $absolute_bounding_box
	 * @return self
	 */
	public static function fromWorld(World $world, ArenaBoundingBox $absolute_bounding_box) : self{
		$chunks = [];

		$offset_min_x = $absolute_bounding_box->getMinX();
		$offset_min_z = $absolute_bounding_box->getMinZ();

		$relative = ArenaBoundingBox::relative($absolute_bounding_box);
		$min_x = $relative->getMinX();
		$min_z = $relative->getMinZ();
		$max_x = $relative->getMaxX();
		$max_z = $relative->getMaxZ();

		for($chunk_x = $min_x; $chunk_x <= $max_x; ++$chunk_x){
			for($chunk_z = $min_z; $chunk_z <= $max_z; ++$chunk_z){
				$chunk = $world->loadChunk($offset_min_x + $chunk_x, $offset_min_z + $chunk_z);
				if($chunk === null){
					continue;
				}

				$chunks[World::chunkHash($chunk_x, $chunk_z)] = PreloadedChunk::fromChunk($chunk);
			}
		}

		return new self($chunks, $relative);
	}

	/** @var PreloadedChunk[] */
	private array $chunks;

	private ArenaBoundingBox $relative_bounding_box;

	/**
	 * @param PreloadedChunk[] $chunks
	 * @param ArenaBoundingBox $relative_bounding_box
	 */
	public function __construct(array $chunks, ArenaBoundingBox $relative_bounding_box){
		$this->chunks = $chunks;
		$this->relative_bounding_box = $relative_bounding_box;
	}

	public function getRelativeBoundingBox() : ArenaBoundingBox{
		return $this->relative_bounding_box;
	}

	public function writeTo(World $world, ArenaBoundingBox $absolute_bounding_box) : void{
		$offset_min_x = $absolute_bounding_box->getMinX();
		$offset_min_z = $absolute_bounding_box->getMinZ();

		$relative = ArenaBoundingBox::relative($absolute_bounding_box);
		$min_x = $relative->getMinX();
		$min_z = $relative->getMinZ();
		$max_x = $relative->getMaxX();
		$max_z = $relative->getMaxZ();

		for($chunk_x = $min_x; $chunk_x <= $max_x; ++$chunk_x){
			for($chunk_z = $min_z; $chunk_z <= $max_z; ++$chunk_z){
				if(isset($this->chunks[$hash = World::chunkHash($chunk_x, $chunk_z)])){
					$this->chunks[$hash]->copyTo($offset_min_x + $chunk_x, $offset_min_z + $chunk_z, $world);
				}
			}
		}
	}
}