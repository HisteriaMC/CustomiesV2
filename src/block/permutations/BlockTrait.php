<?php
declare(strict_types=1);

namespace customiesdevs\customies\block\permutations;

use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;

trait BlockTrait{

	protected function getTraitsList(): ListTag {
		return new ListTag([], NBT::TAG_Compound);
	}

	public function addPlacementDirection(
		bool $cardinalDirection = true,
		bool $facingDirection = false,
		bool $cornerAndCardinalDirection = false,
		float $yRotationOffset = 180.0
	): CompoundTag {
		$trait = CompoundTag::create()
			->setTag("blocks_to_corner_with", new ListTag([], NBT::TAG_String))
			->setTag("enabled_states", CompoundTag::create()
				->setTag("cardinal_direction", new ByteTag($cardinalDirection ? 1 : 0))
				->setTag("corner_and_cardinal_direction", new ByteTag($cornerAndCardinalDirection ? 1 : 0))
				->setTag("facing_direction", new ByteTag($facingDirection ? 1 : 0))
			)
			->setTag("name", new StringTag("minecraft:placement_direction"))
			->setTag("y_rotation_offset", new FloatTag($yRotationOffset)); // Faces towards Player

		return CompoundTag::create()
			->setTag("traits", new ListTag([$trait], NBT::TAG_Compound));
	}

	public function addPlacementPosition(
		bool $blockFace = true,
		bool $verticalHalf = false
	): CompoundTag {
		$trait = CompoundTag::create()
			->setTag("enabled_states", CompoundTag::create()
				->setTag("block_face", new ByteTag($blockFace ? 1 : 0))
				->setTag("vertical_half", new ByteTag($verticalHalf ? 1 : 0))
			)
			->setTag("name", new StringTag("minecraft:placement_position"));

		return CompoundTag::create()
			->setTag("traits", new ListTag([$trait], NBT::TAG_Compound));
	}
}