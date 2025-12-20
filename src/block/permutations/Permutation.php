<?php
declare(strict_types=1);

namespace customiesdevs\customies\block\permutations;

use customiesdevs\customies\block\component\BlockComponent;
use customiesdevs\customies\block\component\MaterialInstancesComponent;
use customiesdevs\customies\util\NBT;
use pocketmine\nbt\tag\CompoundTag;

final class Permutation {

	private CompoundTag $components;

	public function __construct(private readonly string $condition) {
		$this->components = CompoundTag::create();
	}

	/**
	 * Returns the permutation with the provided component added to the current list of components.
	 */
	public function withComponent(BlockComponent $component) : self {
		$value = ($component instanceof MaterialInstancesComponent) ? $component->getValue(4) : $component->getValue();
		$tag = NBT::getTagType($value);
		if($tag === null){
			throw new \RuntimeException("Failed to get tag type for component " . $component->getName());
		}
		$this->components->setTag($component->getName(), $tag);
		return $this;
	}

	/**
	 * Returns the permutation in the correct NBT format supported by the client.
	 */
	public function toNBT(): CompoundTag {
		return CompoundTag::create()
			->setTag("components", $this->components)
			->setString("condition", $this->condition);
	}
}