<?php
declare(strict_types=1);

namespace customiesdevs\customies\block\properties;

use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;

/**
 * Represents a collision box definition used by the `minecraft:collision_box`
 * (and similar) block components.
 *
 * Coordinates are defined in **block-relative space**, where the block center
 * is `(0, 0, 0)` and the full block spans from `(-8, 0, -8)` to `(8, 24, 8)`.
 *
 * ### Constraints
 * - **Origin** must be in range `(-8, 0, -8)` to `(7, 23, 7)`
 * - **Size** must be in range `(1, 1, 1)` to `(16, 24, 16)`
 * - **Origin + Size** must not exceed `(8, 24, 8)`
 *
 * All values are automatically clamped to valid ranges.
 */
class Box {

	/** @var Vector3 Origin (minimum corner) of the box in block-relative coordinates. */
	private Vector3 $origin;
	/** @var Vector3 Size (width, height, depth) of the box. */
	private Vector3 $size;

	/**
	 * Creates a new collision box definition.
	 * Values are clamped to Minecraft's valid block collision bounds.
	 * @param Vector3 $origin Minimum corner of the box.
	 * @param Vector3 $size Dimensions of the box.
	 */
	public function __construct(Vector3 $origin, Vector3 $size) {
		[$ox, $oy, $oz] = self::clampOrigin([$origin->x, $origin->y, $origin->z], [$size->x, $size->y, $size->z]);
		[$sx, $sy, $sz] = self::clampSize([$ox, $oy, $oz], [$size->x, $size->y, $size->z]);
		$this->origin = new Vector3($ox, $oy, $oz);
		$this->size = new Vector3($sx, $sy, $sz);
	}

	/**
	 * Returns the origin (minimum corner) of the box.
	 * @return Vector3
	 */
	public function getOrigin(): Vector3 { return $this->origin; }

	/**
	 * Returns the size of the box.
	 * @return Vector3
	 */
	public function getSize(): Vector3 { return $this->size; }

	/**
	 * Clamps the origin so it stays within block bounds while accounting for size.
	 *
	 * @param array{0:float,1:float,2:float} $origin `[x, y, z]`
	 * @param array{0:float,1:float,2:float} $size `[width, height, depth]`
	 * @return array{0:float,1:float,2:float} Clamped origin
	 */
	private static function clampOrigin(array $origin, array $size): array {
		[$w, $h, $d] = $size;
		[$x, $y, $z] = $origin;
		$x = max(-8.0, min(8.0 - 1, $x));
		$y = max(0.0, min(24.0 - 1, $y));
		$z = max(-8.0, min(8.0 - 1, $z));
		$x = max(-8.0, min(8.0 - $w, $x));
		$y = max(0.0, min(24.0 - $h, $y));
		$z = max(-8.0, min(8.0 - $d, $z));
		return [$x, $y, $z];
	}

	/**
	 * Clamps the size so the box remains inside bounds given its origin.
	 * Size will always be at least `1` on every axis.
	 * 
	 * @param array{0:float,1:float,2:float} $origin `[x, y, z]`
	 * @param array{0:float,1:float,2:float} $size `[width, height, depth]`
	 * @return array{0:float,1:float,2:float} Clamped size
	 */
	private static function clampSize(array $origin, array $size): array {
		[$ox, $oy, $oz] = $origin;
		[$w, $h, $d] = $size;
		$w = max(1.0, min(8.0 - $ox, $w));
		$h = max(1.0, min(24.0 - $oy, $h));
		$d = max(1.0, min(8.0 - $oz, $d));
		return [$w, $h, $d];
	}

	/**
	 * Converts the box into the Bedrock NBT array format.
	 * Coordinates are converted from block-relative space into
	 * client-expected values (X and Z shifted by +8).
	 * @return array{
	 *   maxX: float,
	 *   maxY: float,
	 *   maxZ: float
	 *   minX: float,
	 *   minY: float,
	 *   minZ: float,
	 * }
	 */
	public function toNbtArray(): array {
		return [
			"maxX" => $this->origin->x + $this->size->x + 8.0,
			"maxY" => $this->origin->y + $this->size->y,
			"maxZ" => $this->origin->z + $this->size->z + 8.0,
			"minX" => $this->origin->x + 8.0,
			"minY" => $this->origin->y,
			"minZ" => $this->origin->z + 8.0,
		];
	}

	/**
	 * Creates a box from an {@see AxisAlignedBB}.
	 * @param AxisAlignedBB $bb The bounding box to convert.
	 * @return self
	 */
	public static function fromAABB(AxisAlignedBB $bb): self {
		$fx = fmod($bb->minX, 1.0);
		$fz = fmod($bb->minZ, 1.0);
		if ($fx < 0) $fx += 1.0;
		if ($fz < 0) $fz += 1.0;
		return new self(
			new Vector3(
				$fx - 8.0,
				$bb->minY - floor($bb->minY),
				$fz - 8.0
			),
			new Vector3(
				$bb->maxX - $bb->minX,
				$bb->maxY - $bb->minY,
				$bb->maxZ - $bb->minZ
			)
		);
	}

	/**
	 * Converts this box into an {@see AxisAlignedBB}.
	 * @return AxisAlignedBB
	 */
	public function toAxisAlignedBB(): AxisAlignedBB {
		return new AxisAlignedBB(
			$this->origin->x, // Min-X
			$this->origin->y, // Min-Y
			$this->origin->z, // Min-Z
			$this->origin->x + $this->size->x, // Max-X
			$this->origin->y + $this->size->y, // Max-Y
			$this->origin->z + $this->size->z // Max-Z
		);
	}
}