<?php
declare(strict_types=1);

namespace customiesdevs\customies\item\component;

use pocketmine\nbt\tag\ShortTag;

final class KineticWeaponComponent implements ItemComponent {

	private array $creativeReach;
	private float $damageModifier;
	private float $damageMultiplier;
	private int $delay;
	private array $dismountConditions;
	private float $hitboxMargin;
	private array $knockbackConditions;
	private array $reach;

	/**
	 * The kinetic weapon component defines the behavior of kinetic weapons, which deal damage based on their speed and other conditions.
	 * @param array $creativeReach The reach of the weapon in creative mode
	 * @param float $damageModifier A flat modifier added to the damage dealt by the weapon
	 * @param float $damageMultiplier A multiplier applied to the damage dealt by the weapon
	 * @param int $delay The delay between uses of the weapon, in ticks
	 * @param array $dismountConditions Conditions that must be met for the weapon to dismount entities
	 * @param float $hitboxMargin The margin added to the hitbox of the weapon
	 * @param array $knockbackConditions Conditions that must be met for the weapon to apply knockback
	 * @param array $reach The reach of the weapon in survival mode
	 */
	public function __construct(
		array $creativeReach = ['min' => 2.0, 'max' => 7.5],
		float $damageModifier = 0.0,
		float $damageMultiplier = 0.7,
		int $delay = 15,
		array $dismountConditions = [
			'min_speed' => 14.0,
			'min_relative_speed' => 0.0,
			'max_duration' => 100
		],
		float $hitboxMargin = 0.25,
		array $knockbackConditions = [
			'min_speed' => 5.1,
			'min_relative_speed' => 0.0,
			'max_duration' => 120
		],
		array $reach = ['min' => 2.0, 'max' => 4.5]
	) {
		$this->creativeReach = self::validateRange($creativeReach, 'creative_reach');
		$this->reach = self::validateRange($reach, 'reach');
		$this->damageModifier = $damageModifier;
		$this->damageMultiplier = $damageMultiplier;
		$this->delay = $delay;
		$this->dismountConditions = self::validateConditions($dismountConditions, 'dismount_conditions');
		$this->knockbackConditions = self::validateConditions($knockbackConditions, 'knockback_conditions');
		$this->hitboxMargin = $hitboxMargin;
	}
	public function getName(): string {
		return 'minecraft:kinetic_weapon';
	}

	public function getValue(): array {
		return [
			"minecraft:kinetic_weapon" => [
				"creative_reach" => self::rangeToArray($this->creativeReach),
				"damage_modifier" => $this->damageModifier,
				"damage_multiplier" => $this->damageMultiplier,
				"delay" => new ShortTag($this->delay),
				"dismount_conditions" => self::conditionsToArray($this->dismountConditions),
				"hitbox_margin" => $this->hitboxMargin,
				"knockback_conditions" => self::conditionsToArray($this->knockbackConditions),
				"reach" => self::rangeToArray($this->reach),
			]
		];
	}

	public function getPropertyMapping(): ?array {
		return null;
	}

	private static function validateRange(array $range, string $name): array {
		if(!isset($range['min'], $range['max'])){
			throw new \InvalidArgumentException("$name must contain min and max");
		}
		return [
			'min' => (float) $range['min'],
			'max' => (float) $range['max']
		];
	}

	private static function validateConditions(array $conditions, string $name): array {
		foreach(['min_speed', 'min_relative_speed', 'max_duration'] as $key){
			if(!array_key_exists($key, $conditions)){
				throw new \InvalidArgumentException("$name missing $key");
			}
		}
		return [
			'min_speed' => (float) $conditions['min_speed'],
			'min_relative_speed' => (float) $conditions['min_relative_speed'],
			'max_duration' => (int) $conditions['max_duration']
		];
	}

	private static function rangeToArray(array $range): array {
		return [
			"min" => $range['min'],
			"max" => $range['max']
		];
	}

	private static function conditionsToArray(array $conditions): array {
		return [
			"min_speed" => $conditions['min_speed'],
			"min_relative_speed" => $conditions['min_relative_speed'],
			"max_duration" => new ShortTag($conditions['max_duration'])
		];
	}
}