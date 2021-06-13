<?php

declare(strict_types=1);

namespace muqsit\arenagenerator\layout\chunk;

final class ChunkEntityMovers{

	public static function instance() : self{
		static $instance = null;
		return $instance ??= new self(new BlockEntityMoverRegistry(), new EntityMoverRegistry());
	}

	public BlockEntityMoverRegistry $block_entity;
	public EntityMoverRegistry $entity;

	public function __construct(BlockEntityMoverRegistry $block_entity, EntityMoverRegistry $entity){
		$this->block_entity = $block_entity;
		$this->entity = $entity;
	}
}