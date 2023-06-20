<?php

declare(strict_types=1);

namespace muqsit\arenagenerator;

use Closure;
use muqsit\arenagenerator\layout\ArenaLayout;
use pocketmine\Server;
use pocketmine\utils\Filesystem;
use pocketmine\world\World;
use pocketmine\world\WorldCreationOptions;
use RuntimeException;

final class ArenaGenerator {

    private Closure $arena_destroy_listener;

    private ?World $current = null;
    private array $arena_by_worlds = [];

    public function __construct(
        protected string $name,
        protected ArenaAllocator $allocator,
        protected ArenaLayout $layout,
        protected WorldCreationOptions $world_creation_options) {

        $this->init();
    }

    private function init(): void {
        $this->arena_destroy_listener = function (Arena $arena): void {
            $world = $arena->getWorld();
            $arenaId = spl_object_id($arena);
            unset($this->arena_by_worlds[$world->getId()][$arenaId]);

            if ($world !== $this->current && count($this->arena_by_worlds[$world->getId()]) === 0) {
                unset($this->arena_by_worlds[$world->getId()]);
                $this->destroyWorld($world);
            }
        };
    }

    public function generate(): Arena {
        try {
            $box = $this->allocator->allocate($this->layout);
            assert($this->current !== null);
        } catch (\Exception $e) {
            $this->allocator->reallocate();
            $box = $this->allocator->allocate($this->layout);

            $world_folder_name = "{$this->name}_{$this->allocator->allocations}";
            $generated = Server::getInstance()->getWorldManager()->generateWorld($world_folder_name, $this->world_creation_options, false);

            if ($generated === null) {
                throw new RuntimeException("Failed to generate world '{$world_folder_name}'");
            }

            $world = Server::getInstance()->getWorldManager()->getWorldByName($world_folder_name);

            $this->current = $world;
        }

        $this->layout->writeTo($this->current, $box);
        $arena = new Arena($this->current, $box);
        $this->arena_by_worlds[$this->current->getId()][spl_object_id($arena)] = $arena;
        $arena->registerDestroyListener($this->arena_destroy_listener);
        return $arena;
    }

    public function destroy(): void {
        foreach ($this->arena_by_worlds as $worldArenas) {
            foreach ($worldArenas as $arena) {
                $arena->destroy();
            }
        }

        if ($this->current !== null) {
            $this->destroyWorld($this->current);
            $this->current = null;
        }
    }

    private function destroyWorld(World $world): void {
        $path = $world->getProvider()->getPath();
        Server::getInstance()->getWorldManager()->unloadWorld($world);
        Filesystem::recursiveUnlink($path);
    }
}