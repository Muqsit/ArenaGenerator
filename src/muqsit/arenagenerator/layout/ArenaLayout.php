<?php

declare(strict_types=1);

namespace muqsit\arenagenerator\layout;

use muqsit\arenagenerator\ArenaBoundingBox;
use pocketmine\world\World;

/**
 * ArenaLayout sets world terrain of an arena.
 */
interface ArenaLayout{

	public function getRelativeBoundingBox() : ArenaBoundingBox;

	public function writeTo(World $world, ArenaBoundingBox $absolute_bounding_box) : void;
}