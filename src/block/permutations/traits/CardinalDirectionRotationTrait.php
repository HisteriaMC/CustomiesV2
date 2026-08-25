<?php
declare(strict_types=1);

namespace customiesdevs\customies\block\permutations\traits;

use customiesdevs\customies\block\component\TransformationComponent;
use customiesdevs\customies\block\permutations\BlockPermutation;
use customiesdevs\customies\block\permutations\BlockPermutationsTrait;
use customiesdevs\customies\block\states\BlockState;
use minicore\MiniCore;
use pocketmine\block\Block;
use pocketmine\data\bedrock\block\BlockStateDeserializeException;
use pocketmine\data\bedrock\block\convert\BlockStateReader;
use pocketmine\data\bedrock\block\convert\BlockStateWriter;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

/**
 * Trait for blocks that face horizontally (4 cardinal directions).
 * Used by carved pumpkins and furnaces
 * 4 directions - 'north', 'south', 'east' and 'west'.
 *
 * Consumers must provide $facing (e.g. via HorizontalFacingTrait).
 * Unlike vanilla FacesOppositePlacingPlayerTrait, the block faces the same
 * way as the placing player (front toward the direction the player looks).
 */
trait CardinalDirectionRotationTrait {
	use BlockPermutationsTrait;

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

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null): bool {
		if($player !== null){
			$this->facing = $player->getHorizontalFacing();
		}
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function serializeState(BlockStateWriter $out): void {
		$out->writeString(
			"minecraft:cardinal_direction",
			match($this->facing) {
				Facing::NORTH => "north",
				Facing::SOUTH => "south",
				Facing::WEST => "west",
				Facing::EAST => "east",
			}
		);
	}

	public function deserializeState(BlockStateReader $in): void {
		try {
			$this->facing = match($in->readString("minecraft:cardinal_direction")) {
				"north" => Facing::NORTH,
				"south" => Facing::SOUTH,
				"west" => Facing::WEST,
				"east" => Facing::EAST,
			};
		} catch (BlockStateDeserializeException $exception) {
			//Try with older format
			MiniCore::getInstance()->getLogger()->debug($exception->getMessage());
			$this->facing = $in->readInt("customies:rotation");
		}
	}
}
