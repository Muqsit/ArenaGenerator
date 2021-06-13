<?php

declare(strict_types=1);

namespace muqsit\arenagenerator;

use Closure;
use InvalidStateException;
use muqsit\arenagenerator\layout\ArenaLayout;
use pocketmine\Server;
use pocketmine\utils\Filesystem;
use pocketmine\world\World;
use pocketmine\world\WorldCreationOptions;
use RuntimeException;

final class ArenaGenerator{

	private static function destroyWorld(World $world) : void{
		$path = $world->getProvider()->getPath();
		Server::getInstance()->getWorldManager()->unloadWorld($world);
		// TODO: Unlink $path
		// Filesystem::recursiveUnlink($path); Is this safe?
	}

	private string $name;
	private ArenaLayout $layout;
	private ArenaAllocator $allocator;
	private WorldCreationOptions $world_creation_options;

	/** @phpstan-var Closure(Arena) : void */
	private Closure $arena_destroy_listener;

	private ?World $current = null;

	/**
	 * @var Arena[][]
	 *
	 * @phpstan-var array<int, array<Arena>>
	 */
	private array $arena_by_worlds = [];

	public function __construct(string $name, ArenaAllocator $allocator, ArenaLayout $layout, WorldCreationOptions $world_creation_options){
		$this->name = $name;
		$this->layout = $layout;
		$this->world_creation_options = $world_creation_options;
		$this->allocator = $allocator;
		$this->init();
	}

	private function init() : void{
		$this->arena_destroy_listener = function(Arena $arena) : void{
			$world = $arena->getWorld();
			unset($this->arena_by_worlds[$world_id = $world->getId()][spl_object_id($arena)]);
			if($world !== $this->current && count($this->arena_by_worlds[$world_id]) === 0){
				unset($this->arena_by_worlds[$world_id]);
				self::destroyWorld($world);
			}
		};
	}

	public function generate() : Arena{
		try{
			$box = $this->allocator->allocate($this->layout);
			assert($this->current !== null);
		}catch(InvalidStateException $e){
			$this->allocator->reallocate();
			$box = $this->allocator->allocate($this->layout);

			$world_manager = Server::getInstance()->getWorldManager();

			$world_folder_name = "{$this->name}_{$this->allocator->allocations}";
			if(!$world_manager->generateWorld($world_folder_name, $this->world_creation_options, false)){
				throw new RuntimeException("Failed to generate world '{$world_folder_name}'");
			}

			$world = $world_manager->getWorldByName($world_folder_name);
			if($world === null){
				throw new RuntimeException("Failed to generate world '{$world_folder_name}'");
			}

			$this->current = $world;
		}

		$this->layout->writeTo($this->current, $box);
		$arena = new Arena($this->current, $box);
		$this->arena_by_worlds[$this->current->getId()][spl_object_id($arena)] = $arena;
		$arena->registerDestroyListener($this->arena_destroy_listener);
		return $arena;
	}

	public function destroy() : void{
		foreach($this->arena_by_worlds as $world_id => $arenas){
			foreach($arenas as $arena){
				$arena->destroy();
			}
		}
		if($this->current !== null){
			self::destroyWorld($this->current);
			$this->current = null;
		}
	}
}