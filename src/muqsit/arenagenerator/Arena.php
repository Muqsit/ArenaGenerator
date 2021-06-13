<?php

declare(strict_types=1);

namespace muqsit\arenagenerator;

use Closure;
use pocketmine\world\World;

final class Arena{

	private World $world;
	private ArenaBoundingBox $absolute_bounding_box;

	/**
	 * @var Closure[]
	 *
	 * @phpstan-var array<Closure(self) : void>
	 */
	private array $destroy_listeners = [];

	public function __construct(World $world, ArenaBoundingBox $absolute_bounding_box){
		$this->world = $world;
		$this->absolute_bounding_box = $absolute_bounding_box;
	}

	public function getWorld() : World{
		return $this->world;
	}

	public function getAbsoluteBoundingBox() : ArenaBoundingBox{
		return $this->absolute_bounding_box;
	}

	/**
	 * @param Closure $listener
	 *
	 * @phpstan-param Closure(self) : void $listener
	 */
	public function registerDestroyListener(Closure $listener) : void{
		$this->destroy_listeners[spl_object_id($listener)] = $listener;
	}

	public function destroy() : void{
		foreach($this->destroy_listeners as $listener){
			$listener($this);
		}
		$this->destroy_listeners = [];
	}
}