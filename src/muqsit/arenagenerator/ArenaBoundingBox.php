<?php

declare(strict_types=1);

namespace muqsit\arenagenerator;

/**
 * ArenaBoundingBox is a rectangular bounding box representing the bounds
 * of an area in terms of chunk coordinates.
 */
final class ArenaBoundingBox{

	public static function width(self $box) : int{
		return 1 + ($box->max_x - $box->min_x);
	}

	public static function length(self $box) : int{
		return 1 + ($box->max_z - $box->min_z);
	}

	public static function relative(self $box) : self{
		return new self(0, 0, $box->max_x - $box->min_x, $box->max_z - $box->min_z);
	}

	private int $min_x;
	private int $min_z;
	private int $max_x;
	private int $max_z;

	public function __construct(int $min_x, int $min_z, int $max_x, int $max_z){
		$this->min_x = $min_x;
		$this->min_z = $min_z;
		$this->max_x = $max_x;
		$this->max_z = $max_z;
	}

	public function getMinX() : int{
		return $this->min_x;
	}

	public function getMinZ() : int{
		return $this->min_z;
	}

	public function getMaxX() : int{
		return $this->max_x;
	}

	public function getMaxZ() : int{
		return $this->max_z;
	}
}