<?php
declare(strict_types=1);

namespace customiesdevs\customies\block\permutations;

use customiesdevs\customies\block\component\TransformationComponent;
use pocketmine\block\Block;
use pocketmine\block\utils\AnyFacingTrait;
use pocketmine\data\bedrock\block\convert\BlockStateReader;
use pocketmine\data\bedrock\block\convert\BlockStateWriter;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

trait RotatableTrait {
	use AnyFacingTrait;

	protected int $downRotation = 2;
	protected int $upRotation = 2;

	public function getDownwardsRotation(): int{ return $this->downRotation; }
	public function getUpwardsRotation(): int{ return $this->upRotation; }

	public function setDownwardsRotation(int $rotation): self{
		$this->downRotation = $rotation;
		return $this;
	}
	public function setUpwardsRotation(int $rotation): self{
		$this->upRotation = $rotation;
		return $this;
	}

	/**
	 * @return BlockProperty[]
	 */
	public function getBlockProperties(): array {
		return [
			new BlockProperty("customies:rotation", Facing::ALL),
			new BlockProperty("customies:down_rotation", [2, 3, 4, 5]),
			new BlockProperty("customies:up_rotation", [2, 3, 4, 5]),
		];
	}

	/**
	 * @return Permutation[]
	 */
	public function getPermutations(): array {
		return [
			(new Permutation("q.block_state('customies:rotation') == " . Facing::DOWN)) // Down
				->withComponent(new TransformationComponent(rotation: new Vector3(180, 0.0, 0.0))),
			(new Permutation("q.block_state('customies:rotation') == " . Facing::UP)) // Up
				->withComponent(new TransformationComponent(rotation: new Vector3(-180, 0.0, 0.0))),
			(new Permutation("q.block_state('customies:rotation') == " . Facing::NORTH))
				->withComponent(new TransformationComponent(rotation: new Vector3(0.0, 0.0, 0.0))),
			(new Permutation("q.block_state('customies:rotation') == " . Facing::SOUTH))
				->withComponent(new TransformationComponent(rotation: new Vector3(0.0, 180.0, 0.0))),
			(new Permutation("q.block_state('customies:rotation') == " . Facing::WEST))
				->withComponent(new TransformationComponent(rotation: new Vector3(0.0, 90.0, 0.0),)),
			(new Permutation("q.block_state('customies:rotation') == " . Facing::EAST))
				->withComponent(new TransformationComponent(rotation: new Vector3(0.0, -90.0, 0.0))),
			
			(new Permutation("q.block_state('customies:rotation') == ".Facing::DOWN." && q.block_state('customies:down_rotation') == 2"))
				->withComponent(new TransformationComponent(rotation: new Vector3(0.0, 0.0, 0.0))),
			(new Permutation("q.block_state('customies:rotation') == ".Facing::DOWN." && q.block_state('customies:down_rotation') == 3"))
				->withComponent(new TransformationComponent(rotation: new Vector3(0.0, -180.0, 0.0))),
			(new Permutation("q.block_state('customies:rotation') == ".Facing::DOWN." && q.block_state('customies:down_rotation') == 4"))
				->withComponent(new TransformationComponent(rotation: new Vector3(0.0, 90.0, 0.0))),
			(new Permutation("q.block_state('customies:rotation') == ".Facing::DOWN." && q.block_state('customies:down_rotation') == 5"))
				->withComponent(new TransformationComponent(rotation: new Vector3(0.0, -90.0, 0.0))),
			
			(new Permutation("q.block_state('customies:rotation') == ".Facing::UP." && q.block_state('customies:up_rotation') == 2"))
				->withComponent(new TransformationComponent(rotation: new Vector3(0.0, 0.0, 180.0))),
			(new Permutation("q.block_state('customies:rotation') == ".Facing::UP." && q.block_state('customies:up_rotation') == 3"))
				->withComponent(new TransformationComponent(rotation: new Vector3(-180.0, 0.0, 0.0))),
			(new Permutation("q.block_state('customies:rotation') == ".Facing::UP." && q.block_state('customies:up_rotation') == 4"))
				->withComponent(new TransformationComponent(rotation: new Vector3(0.0, -90.0, 180.0))),
			(new Permutation("q.block_state('customies:rotation') == ".Facing::UP." && q.block_state('customies:up_rotation') == 5"))
				->withComponent(new TransformationComponent(rotation: new Vector3(0.0, 90.0, -180.0))),
		];
	}

	public function getCurrentBlockProperties(): array {
		return [$this->facing, $this->downRotation, $this->upRotation];
	}

	protected function writeStateToMeta(): int {
		return Permutations::toMeta($this);
	}

	public function readStateFromData(int $id, int $stateMeta): void {
		$blockProperties = Permutations::fromMeta($this, $stateMeta);
		$this->facing = $blockProperties[0] ?? Facing::DOWN;
		$this->downRotation = $blockProperties[1] ?? 2;
		$this->upRotation = $blockProperties[2] ?? 2;
	}

	public function getStateBitmask(): int {
		return Permutations::getStateBitmask($this);
	}

	public function serializeState(BlockStateWriter $out): void {
		$out->writeInt("customies:rotation", $this->facing);
		$out->writeInt("customies:down_rotation", $this->downRotation);
		$out->writeInt("customies:up_rotation", $this->upRotation);
	}

	public function deserializeState(BlockStateReader $in): void {
		$this->facing = $in->readInt("customies:rotation");
		$this->downRotation = $in->readInt("customies:down_rotation");
		$this->upRotation = $in->readInt("customies:up_rotation");
	}

	public function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->facing($this->facing);
		$w->int(3, $this->downRotation);
		$w->int(3, $this->upRotation);
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null): bool{
		if($player !== null){
			if($face === Facing::UP){
				$this->upRotation = match($face) {
					Facing::NORTH => 2,
					Facing::SOUTH => 3,
					Facing::WEST  => 4,
					Facing::EAST  => 5,
					default => 2,
				};
			}elseif($face === Facing::DOWN){
				$this->downRotation = match($face) {
					Facing::NORTH => 2,
					Facing::SOUTH => 3,
					Facing::WEST  => 4,
					Facing::EAST  => 5,
					default => 2,
				};
			}
			$this->facing = $face;
		}
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}
}