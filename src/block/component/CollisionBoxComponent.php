<?php
declare(strict_types=1);

namespace customiesdevs\customies\block\component;

use customiesdevs\customies\block\utils\Box;
use pocketmine\math\Vector3;

final class CollisionBoxComponent implements BlockComponent {

	private bool $enabled;
	/** @var Box[] */
	private array $boxes = [];

	/**
	 * Defines the area of the block that collides with entities.
	 * @param bool $enabled If collision should be enabled
	 */
	public function __construct(bool $enabled = true) {
		$this->enabled = $enabled;
	}

	public function getName(): string {
		return 'minecraft:collision_box';
	}

	public function getValue(): array {
		$boxes = [];
		foreach($this->boxes as $box) {
			$boxes[] = $box->toNbtArray();
		}
		//if no boxes are defined we add a default full block box
		if(empty($boxes)){
			$boxes[] = $this->enabled ? self::defaultCollisionBox()->toNbtArray() : self::noCollisionBox()->toNbtArray();
		}
		return [
			"boxes" => $boxes,
			"enabled" => $this->enabled
		];
	}

	public static function defaultCollisionBox(): Box {
		return new Box(new Vector3(-8, 0, -8), new Vector3(16, 8, 16));
	}

	public static function noCollisionBox(): Box {
		return new Box(new Vector3(-8, 0, -8), new Vector3(0.0001, 0.0001, 0.0001));
	}

	/**
	 * Adds a single collision box.
	 * @param Box $box
	 * The collision box to add.
	 * @return $this
	 */
	public function addBox(Box $box): self {
		$this->boxes[] = $box;
		return $this;
	}

	/**
	 * Adds multiple collision boxes.
	 * @param Box[] $boxes
	 * An array of collision boxes to add.
	 * @return $this
	 */
	public function addBoxes(array $boxes): self {
		foreach($boxes as $box) {
			$this->boxes[] = $box;
		}
		return $this;
	}
}