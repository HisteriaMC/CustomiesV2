<?php
declare(strict_types=1);

namespace customiesdevs\customies\block\states\templates;

use customiesdevs\customies\block\component\TransformationComponent;
use customiesdevs\customies\block\permutations\BlockPermutation;
use customiesdevs\customies\block\permutations\BlockPermutations;
use customiesdevs\customies\block\permutations\BlockPermutationsTrait;
use customiesdevs\customies\block\states\BlockState;
use pocketmine\block\Block;
use pocketmine\block\utils\FacesOppositePlacingPlayerTrait;
use pocketmine\block\utils\HorizontalFacing;
use pocketmine\block\utils\HorizontalFacingTrait;
use pocketmine\data\bedrock\block\convert\BlockStateReader;
use pocketmine\data\bedrock\block\convert\BlockStateWriter;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;

/**
 * - Used by carved pumpkins and furnaces
 * - 4 directions - 'north', 'south', 'east' and 'west'.
 */
abstract class HorizontalFacingState extends Block implements BlockPermutations, HorizontalFacing {
	use BlockPermutationsTrait;
	use HorizontalFacingTrait;
	use FacesOppositePlacingPlayerTrait;

	protected function initStates(): void {
		$this->addState(new BlockState("minecraft:cardinal_direction",
			["north", "south", "west", "east"]
		));
	}

	protected function initPermutations(): void {
		$this->addPermutations([
			new BlockPermutation(
				"q.block_state('minecraft:cardinal_direction') == 'north'",
				new TransformationComponent(new Vector3(0, 0, 0))
			),
			new BlockPermutation(
				"q.block_state('minecraft:cardinal_direction') == 'south'",
				new TransformationComponent(new Vector3(0, 180, 0))
			),
			new BlockPermutation(
				"q.block_state('minecraft:cardinal_direction') == 'west'",
				new TransformationComponent(new Vector3(0, 90, 0))
			),
			new BlockPermutation(
				"q.block_state('minecraft:cardinal_direction') == 'east'",
				new TransformationComponent(new Vector3(0, -90, 0))
			),
		]);
	}

	public function getCurrentStates(): array {
		return [$this->facing];
	}

	public function serializeState(BlockStateWriter $out): void {
		$out->writeString(
			"minecraft:cardinal_direction",
			match($this->facing){
				Facing::DOWN => "down",
				Facing::UP => "up",
				Facing::NORTH => "north",
				Facing::SOUTH => "south",
				Facing::WEST => "west",
				Facing::EAST => "east",
			}
		);
	}

	public function deserializeState(BlockStateReader $in): void {
		$this->facing = match($in->readString("minecraft:cardinal_direction")){
			"north" => Facing::NORTH,
			"south" => Facing::SOUTH,
			"west" => Facing::WEST,
			"east" => Facing::EAST,
		};
	}
}