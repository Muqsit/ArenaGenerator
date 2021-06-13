<?php

declare(strict_types=1);

namespace muqsit\arenagenerator\layout;

use muqsit\arenagenerator\ArenaBoundingBox;
use muqsit\arenagenerator\layout\chunk\PreloadedChunk;
use pocketmine\world\World;

/**
 * ArenaLayout implementation that copies chunks from a given world at the
 * time a new arena is generated. This reduces preparation cost and has a
 * smaller memory footprint than PreloadedWorldReplicatorArenaLayout, however
 * the runtime cost is much higher as chunks are copied at every request and
 * may be loaded from disk if the chunk in source world isn't loaded.
 */
final class RealtimeWorldReplicatorArenaLayout implements ArenaLayout{

	public static function fromWorld(World $source, ArenaBoundingBox $absolute_bounding_box) : self{
		return new self($source, $absolute_bounding_box);
	}

	private World $source;
	private ArenaBoundingBox $absolute_bounding_box;

	public function __construct(World $source, ArenaBoundingBox $absolute_bounding_box){
		$this->source = $source;
		$this->absolute_bounding_box = $absolute_bounding_box;
	}

	public function getRelativeBoundingBox() : ArenaBoundingBox{
		return ArenaBoundingBox::relative($this->absolute_bounding_box);
	}

	public function writeTo(World $world, ArenaBoundingBox $absolute_bounding_box) : void{
		$offset_src_min_x = $this->absolute_bounding_box->getMinX();
		$offset_src_min_z = $this->absolute_bounding_box->getMinZ();

		$offset_dst_min_x = $absolute_bounding_box->getMinX();
		$offset_dst_min_z = $absolute_bounding_box->getMinZ();

		$relative = ArenaBoundingBox::relative($absolute_bounding_box);
		$min_x = $relative->getMinX();
		$min_z = $relative->getMinZ();
		$max_x = $relative->getMaxX();
		$max_z = $relative->getMaxZ();

		for($chunk_x = $min_x; $chunk_x <= $max_x; ++$chunk_x){
			$x = $offset_src_min_x + $chunk_x;
			for($chunk_z = $min_z; $chunk_z <= $max_z; ++$chunk_z){
				$z = $offset_src_min_z + $chunk_z;
				$chunk = $this->source->loadChunk($x, $z);
				if($chunk !== null){
					PreloadedChunk::fromChunk($chunk)->copyAt($offset_dst_min_x + $chunk_x, $offset_dst_min_z + $chunk_z, $world);
				}
			}
		}
	}
}