<?php

declare(strict_types=1);

namespace muqsit\arenagenerator;

use InvalidStateException;
use muqsit\arenagenerator\layout\ArenaLayout;

/**
 * ArenaAllocator allocates chunks of space for an arena, finding a suitable
 * area where the arena could be placed based on max horizontal coordinates
 * and spacing from other arenas.
 */
final class ArenaAllocator{

	public static function optimized() : self{
		return new self(2048, 8);
	}

	public int $allocations = 0;
	private int $next_x;
	private int $next_z;
	private int $max_length;
	private int $spacing;

	public function __construct(int $max_length, int $spacing){
		$this->max_length = $max_length;
		$this->spacing = $spacing;
		$this->next_x = $this->max_length;
		$this->next_z = $this->max_length;
	}

	public function allocate(ArenaLayout $layout) : ArenaBoundingBox{
		$bounding_box = $layout->getRelativeBoundingBox();
		$width = ArenaBoundingBox::width($bounding_box);
		$length = ArenaBoundingBox::length($bounding_box);

		$this->next_x += $width;
		$this->next_x += $this->spacing;
		if($this->next_x > $this->max_length){
			$this->next_x = 0;
			$this->next_z += $length;
			$this->next_z += $this->spacing;
			if($this->next_z > $this->max_length){
				throw new InvalidStateException("Arena space is full");
			}
		}

		return new ArenaBoundingBox($this->next_x, $this->next_z, $this->next_x + $width, $this->next_z + $length);
	}

	public function reallocate() : void{
		$this->next_x = 0;
		$this->next_z = 0;
		++$this->allocations;
	}
}