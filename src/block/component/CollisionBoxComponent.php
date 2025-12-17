<?php
declare(strict_types=1);

namespace customiesdevs\customies\block\component;

use customiesdevs\customies\block\properties\Box;
use pocketmine\math\Vector3;

class CollisionBoxComponent implements BlockComponent {

	private const DEFAULT_ORIGIN = [-8, 0, -8];
	private const DEFAULT_SIZE = [16, 8, 16];
	private const NO_COLLISION_SIZE = [0.001, 0.001, 0.001];

	private bool $enabled;
	/** @var Box[] */
	private array $boxes = [];

	/**
	 * Defines the area of the block that collides with entities.
	 * @param bool $enabled If collision should be enabled
	 */
	public function __construct(bool $enabled = true) {
		$this->enabled = $enabled;
		if($enabled){
			$this->boxes[] = self::createDefaultBox();
		}
	}

	private static function createDefaultBox(): Box {
		return new Box(
			new Vector3(...self::DEFAULT_ORIGIN),
			new Vector3(...self::DEFAULT_SIZE)
		);
	}

	private static function createNoCollisionBox(): Box {
		return new Box(
			new Vector3(...self::DEFAULT_ORIGIN),
			new Vector3(...self::NO_COLLISION_SIZE)
		);
	}

	public function setNoCollision(): self {
		$this->boxes = [self::createNoCollisionBox()];
		return $this;
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

	public function getName(): string {
		return 'minecraft:collision_box';
	}

	public function getValue(): array {
		$convertedBoxes = [];
		foreach($this->boxes as $box){
			$convertedBoxes[] = $box->toNbtArray();
		}
		return [
			"boxes" => $convertedBoxes,
			"enabled" => $this->enabled ? 1 : 0,
		];
	}

	public static function fromJson(mixed $data): static {
		if(is_bool($data)) {
			return new self($data);
		}
		$component = new self(true);
		$component->boxes = [];
		// No Collision
		if(is_array($data) && ($data['enabled'] ?? false) === true){
			return $component->setNoCollision();
		}
		// Array of boxes
		if(is_array($data) && isset($data[0])) {
			foreach($data as $box) {
				$origin = $box['origin'] ?? self::DEFAULT_ORIGIN;
				$size = $box['size'] ?? self::DEFAULT_SIZE;
				$component->addBox(new Box(
					new Vector3($origin[0], $origin[1], $origin[2]),
					new Vector3($size[0], $size[1], $size[2])
				));
			}
			return $component;
		}
		// Single box object
		if(is_array($data) && isset($data['origin'])) {
			$origin = $data['origin'];
			$size = $data['size'] ?? self::DEFAULT_SIZE;
			return $component->addBox(new Box(
				new Vector3($origin[0], $origin[1], $origin[2]),
				new Vector3($size[0], $size[1], $size[2])
			));
		}
		return $component;
	}
}