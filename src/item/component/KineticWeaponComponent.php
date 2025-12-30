<?php
declare(strict_types=1);

namespace customiesdevs\customies\item\component;

final class KineticWeaponComponent implements ItemComponent {

	private array $creativeReach = [];
	private float $damageModifier;
	private float $damageMultiplier;
	private int $delay;

	private array $dismountConditions = [];
	private float $hitboxMargin;
	private array $knockbackConditions = [];
	private array $reach = [];

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
		$this->creativeReach = $creativeReach;
		$this->damageModifier = $damageModifier;
		$this->damageMultiplier = $damageMultiplier;
		$this->delay = $delay;
		$this->dismountConditions = $dismountConditions;
		$this->hitboxMargin = $hitboxMargin;
		$this->knockbackConditions = $knockbackConditions;
		$this->reach = $reach;
	}

	public function getName(): string {
		return 'minecraft:kinetic_weapon';
	}

	public function getValue(): array {
		return [
			"creative_reach" => [
				"min" => (float) $this->creativeReach['min'],
				"max" => (float) $this->creativeReach['max']
			],
			"damage_modifier" => $this->damageModifier,
			"damage_multiplier" => $this->damageMultiplier,
			"delay" => $this->delay,
			"dismount_conditions" => [
				"min_speed" => (float) $this->dismountConditions['min_speed'],
				"min_relative_speed" => (float) $this->dismountConditions['min_relative_speed'],
				"max_duration" => (int) $this->dismountConditions['max_duration']
			],
			"hitbox_margin" => $this->hitboxMargin,
			"knockback_conditions" => [
				"min_speed" => (float) $this->knockbackConditions['min_speed'],
				"min_relative_speed" => (float) $this->knockbackConditions['min_relative_speed'],
				"max_duration" => (int) $this->knockbackConditions['max_duration']
			],
			"reach" => [
				"min" => (float) $this->reach['min'],
				"max" => (float) $this->reach['max']
			],
		];
	}

	public function getPropertyMapping(): ?array {
		return null;
	}
}