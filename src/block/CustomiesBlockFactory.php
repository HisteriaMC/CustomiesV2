<?php
declare(strict_types=1);

namespace customiesdevs\customies\block;

use Closure;
use customiesdevs\customies\block\BlockComponents;
use customiesdevs\customies\block\permutations\BlockPermutation;
use customiesdevs\customies\block\permutations\BlockPermutations;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\CustomiesItemFactory;
use customiesdevs\customies\task\AsyncRegisterBlocksTask;
use customiesdevs\customies\util\NBT;
use InvalidArgumentException;
use minicore\blocks\decoration\CustomFlower;
use minicore\blocks\register\InterfaceBlock;
use pocketmine\block\Block;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\data\bedrock\block\convert\BlockStateReader;
use pocketmine\data\bedrock\block\convert\BlockStateWriter;
use pocketmine\inventory\CreativeCategory;
use pocketmine\inventory\CreativeGroup;
use pocketmine\inventory\CreativeInventory;
use pocketmine\lang\Translatable;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\protocol\types\BlockPaletteEntry;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\format\io\GlobalBlockStateHandlers;
use RuntimeException;
use function array_map;
use function array_reverse;
use function hash;
use function strcmp;
use function usort;

final class CustomiesBlockFactory {
	use SingletonTrait;

	/**
	 * @var Closure[]
	 * @phpstan-var array<string, array{(Closure(int): Block), (Closure(BlockStateWriter): Block), (Closure(Block): BlockStateReader)}>
	 */
	private array $blockFuncs = [];
	/** @var BlockPaletteEntry[] */
	private array $blockPaletteEntries = [];
	/** @var array<string, Block> Map of block identifiers to block instances */
	private array $customBlocks = [];
	/** @var array<string, CreativeGroup> Map of group names to creative groups */
	private array $groups = [];
    //The following is from Histeria
	/** @var array<string, BlockComponent[]> External components registered per identifier */
	private array $externalComponents = [];

	/**
	 * Allows attaching block components to an identifier even if the block class itself
	 * doesn't implement BlockComponents. Components will be merged during registration.
	 * @param string $identifier
	 * @param BlockComponent[] $components
	 */
	public function registerExternalComponents(string $identifier, array $components): void {
		$this->externalComponents[$identifier] = $components;
	}
    //End from Histeria

	/**
	 * Adds a worker initialize hook to the async pool to sync the BlockFactory for every thread worker that is created.
	 * It is especially important for the workers that deal with chunk encoding, as using the wrong runtime ID mappings
	 * can result in massive issues with almost every block showing as the wrong thing and causing lag to clients.
	 */
	public function addWorkerInitHook(): void {
		$server = Server::getInstance();
		$blocks = $this->blockFuncs;
		$server->getAsyncPool()->addWorkerStartHook(static function (int $worker) use ($server, $blocks): void {
			$server->getAsyncPool()->submitTaskToWorker(new AsyncRegisterBlocksTask($blocks), $worker);
		});
	}

	/**
	 * Get a custom block from its identifier. An exception will be thrown if the block is not registered.
	 * @param string $identifier Unique block identifier (e.g. "namespace:block_name")
	 * @return Block A clone of the registered block.
	 * @throws InvalidArgumentException If the block is not registered
	 */
	public function get(string $identifier): Block {
		if(!isset($this->customBlocks[$identifier])){
			throw new InvalidArgumentException("Custom block $identifier is not registered");
		}
		return clone $this->customBlocks[$identifier];
	}

	/**
	 * Returns all the block palette entries that need to be sent to the client.
	 * @return BlockPaletteEntry[]
	 */
	public function getBlockPaletteEntries(): array {
		return $this->blockPaletteEntries;
	}

	/**
	 * Register a block to the BlockFactory and all the required mappings. A custom stateReader and stateWriter can be
	 * provided to allow for custom block state serialization.
	 * @param Closure $blockFunc A closure that returns a new instance of the block to register.
	 * @param string $identifier The unique identifier for the block (e.g. "namespace:block_name").
	 * @param CreativeInventoryInfo $creativeInfo Creative inventory information for the block. Default set to `Equipment` Category.
	 * @param Closure|null $serializer Optional closure that takes a BlockStateWriter and returns it after writing the block state.
	 * @param Closure|null $deserializer Optional closure that takes a BlockStateReader and returns a new instance of the block after reading the state.
	 * @throws InvalidArgumentException If the blockFunc does not return a Block instance.
	 */
	public function registerBlock(
		Closure $blockFunc,
		string $identifier,
		CreativeInventoryInfo $creativeInfo = new CreativeInventoryInfo(CreativeInventoryInfo::CATEGORY_EQUIPMENT),
		?Closure $serializer = null,
		?Closure $deserializer = null
	): void {
		$block = $blockFunc();
		if(!$block instanceof Block){
			throw new InvalidArgumentException("Class returned from closure is not a Block");
		}

		RuntimeBlockStateRegistry::getInstance()->register($block);
		CustomiesItemFactory::getInstance()->registerBlockItem($identifier, $block);
		$this->customBlocks[$identifier] = $block;

		$nbtTag = CompoundTag::create();
		$componentsTag = CompoundTag::create();
		// Adds Components to Block
		if($block instanceof BlockComponents){
			foreach($block->getComponents() as $component){
				$tag = NBT::getTagType($component->getValue()) ?? throw new RuntimeException("Failed to get tag type for component: " . $component->getName());
				$componentsTag->setTag($component->getName(), $tag);
			}
		}
        //The following is from Histeria
        // Merge external components registered for this identifier
        if(isset($this->externalComponents[$identifier])){
            foreach ($this->externalComponents[$identifier] as $component){
                $tag = NBT::getTagType($component->getValue());
                if($tag === null) {
                    throw new RuntimeException("Failed to get tag type for component " . $component->getName());
                }
                $componentsTag->setTag($component->getName(), $tag);
            }
        }

        if ($block instanceof InterfaceBlock) {
            //Avoid placing blocks against blocks that implement InterfaceBlock
            $componentsTag->setTag("minecraft:on_interact", CompoundTag::create());
        }

        if ($block instanceof CustomFlower) {
            //I didn't manage to get it work properly but the only presence of this tag prevent placing in any case
            //It's placed server side currently
            //Possible fix: https://github.com/AID-LEARNING/SymplyPlugin/blob/7758dd827f1cd5886da81ded6b6ac2915cdf98e0/src/SenseiTarzan/SymplyPlugin/Behavior/blocks/component/PlacementFilterComponent.php
            /** @see CustomFlower */
            $componentsTag->setTag("minecraft:placement_filter", CompoundTag::create());
        }
        //end from Histeria

        // Creative NBT
        $nbtTag->setTag("menu_category",
            CompoundTag::create()
                ->setString("category", $creativeInfo->getCategory())
                ->setString("group", $creativeInfo->getGroup())
                ->setByte("is_hidden_in_commands", 0)
        );

		// Adds States/Permutation to Block
		if($block instanceof BlockPermutations){
			// Register the States/Permutation to the block
			$this->registerPermutations($block, $identifier, $nbtTag, $serializer, $deserializer);
		}else{
			// If a block does not contain any permutations we can just insert the one state.
			BlockPalette::getInstance()->insertState(
				CompoundTag::create()
					->setString(BlockStateData::TAG_NAME, $identifier)
					->setTag(BlockStateData::TAG_STATES, CompoundTag::create())
			);
			$serializer ??= BlockStateWriter::create($identifier);
			$deserializer ??= static fn(BlockStateReader $in) => $block;
		}
		GlobalBlockStateHandlers::getSerializer()->map($block, $serializer);
		GlobalBlockStateHandlers::getDeserializer()->map($identifier, $deserializer);
        // The 'minecraft:on_player_placing' component is required for the client to predict block placement, making
		// it a smoother experience for the end-user.
		$componentsTag->setTag("minecraft:on_player_placing", CompoundTag::create());
		$nbtTag->setTag("blockTags", new ListTag());
		$nbtTag->setTag("components", $componentsTag);
		$nbtTag->setInt("molangVersion", 13);
		// Registers the block to creative inventory
		$this->registerCreativeInfo($block, $creativeInfo);
		$this->blockPaletteEntries[] = new BlockPaletteEntry($identifier, new CacheableNbt($nbtTag));
		$this->blockFuncs[$identifier] = [$blockFunc, $serializer, $deserializer];
		// 1.20.60 added a new "block_id" field which depends on the order of the block palette entries. Every time we
		// insert a new block, we need to re-sort the block palette entries to keep in sync with the client.
		usort($this->blockPaletteEntries, static function(BlockPaletteEntry $a, BlockPaletteEntry $b): int {
			return strcmp(hash("fnv164", $a->getName()), hash("fnv164", $b->getName()));
		});
		foreach($this->blockPaletteEntries as $i => $entry){
			$root = $entry->getStates()->getRoot();
			$root->setTag("vanilla_block_data", CompoundTag::create()->setInt("block_id", 10000 + $i));
			$this->blockPaletteEntries[$i] = new BlockPaletteEntry($entry->getName(), new CacheableNbt($root));
		}
	}

	/**
	 * Registers permutations and states for a BlockPermutations instance.
	 * @param BlockPermutations $block The block instance
	 * @param string $identifier The unique block identifier
	 * @param CompoundTag $nbt The NBT tag to populate with permutation data
	 * @param Closure|null &$serializer Reference to the serializer closure to set
	 * @param Closure|null &$deserializer Reference to the deserializer closure to set
	 */
	private function registerPermutations(
		BlockPermutations $block,
		string $identifier,
		CompoundTag $nbt,
		?Closure &$serializer,
		?Closure &$deserializer
	): void {
		$blockNames = $blockValues = $blockProperties = [];
		foreach($block->getStates() as $state){
			$blockNames[] = $state->getName();
			$blockValues[] = $state->getValues();
			$blockProperties[] = NBT::getTagType($state->getValue());
		}
		$nbt->setTag("permutations", new ListTag(array_map(
			static fn(BlockPermutation $p) => NBT::getTagType($p->toArray()),
			$block->getPermutations()
		)));
		$nbt->setTag("properties", new ListTag(array_reverse($blockProperties)));
		foreach(BlockPermutation::getCartesianProduct($blockValues) as $meta => $stateValues){
			$stateTag = CompoundTag::create();
			// We need to insert states for every possible permutation to allow for all blocks to be used and to
			// keep in sync with the client's block palette.
			foreach($stateValues as $i => $value){
				$stateTag->setTag($blockNames[$i], NBT::getTagType($value));
			}
			BlockPalette::getInstance()->insertState(
				CompoundTag::create()
					->setString(BlockStateData::TAG_NAME, $identifier)
					->setTag(BlockStateData::TAG_STATES, $stateTag),
				$meta
			);
		}
		$serializer ??= static function (BlockPermutations $b) use ($identifier): BlockStateWriter {
			$writer = BlockStateWriter::create($identifier);
			$b->serializeState($writer);
			return $writer;
		};
		$deserializer ??= static function (BlockStateReader $in) use ($identifier): BlockPermutations {
			$b = CustomiesBlockFactory::getInstance()->get($identifier);
			assert($b instanceof BlockPermutations);
			$b->deserializeState($in);
			return $b;
		};
	}

	/**
	 * Registers the block in the creative inventory based on the provided CreativeInventoryInfo.
	 * @param Block $block The block to register
	 * @param CreativeInventoryInfo $creativeInfo The creative inventory information
	 */
	private function registerCreativeInfo(
		Block $block,
		CreativeInventoryInfo $creativeInfo
	): void {
        $group = null;
		if($creativeInfo->getGroup() !== CreativeInventoryInfo::NONE){
			$group = CreativeInventoryInfo::get($creativeInfo->getGroup()) ?? new CreativeGroup(new Translatable($creativeInfo->getGroup()), $block->asItem());
			CreativeInventoryInfo::set($group);
		}
		$category = match($creativeInfo->getCategory()){
			CreativeInventoryInfo::CATEGORY_CONSTRUCTION => CreativeCategory::CONSTRUCTION,
			CreativeInventoryInfo::CATEGORY_ITEMS => CreativeCategory::ITEMS,
			CreativeInventoryInfo::CATEGORY_NATURE => CreativeCategory::NATURE,
			CreativeInventoryInfo::CATEGORY_EQUIPMENT => CreativeCategory::EQUIPMENT,
			default => throw new AssumptionFailedError("Unknown Creative Category"),
		};
		CreativeInventory::getInstance()->add($block->asItem(), $category, $group);
	}
}