<?php
declare(strict_types=1);

namespace customiesdevs\customies\item\component;

final class PiercingWeaponComponent implements ItemComponent {

	private array $creativeReach;
	private float $hitboxMargin;
	private array $reach;

	/**
	 * The piercing weapon component defines the behavior of piercing weapons, which can hit multiple entities in a single attack.
	 * @param array $creativeReach The reach of the weapon in creative mode
	 * @param float $hitboxMargin The margin added to the hitbox of the weapon
	 * @param array $reach The reach of the weapon in survival mode
	 */
	public function __construct(
		array $creativeReach = ['min' => 2.0, 'max' => 7.5],
		float $hitboxMargin = 0.25,
		array $reach = ['min' => 2.0, 'max' => 4.5]
	) {
		$this->creativeReach = self::validateRange($creativeReach, 'creative_reach');
		$this->reach = self::validateRange($reach, 'reach');
		$this->hitboxMargin = $hitboxMargin;
	}

	public function getName(): string {
		return 'minecraft:piercing_weapon';
	}

	public function getValue(): array {
		return [
			"creative_reach" => self::rangeToArray($this->creativeReach),
			"hitbox_margin" => $this->hitboxMargin,
			"reach" => self::rangeToArray($this->reach)
		];
	}

	public function getPropertyMapping(): ?array {
		return null;
	}

	private static function validateRange(array $range, string $name): array {
		if(!isset($range['min'], $range['max'])){
			throw new \InvalidArgumentException("$name must contain min and max values");
		}
		return [
			'min' => (float) $range['min'],
			'max' => (float) $range['max']
		];
	}

	private static function rangeToArray(array $range): array {
		return [
			"min" => (float) $range['min'],
			"max" => (float) $range['max']
		];
	}
}