<?php
declare(strict_types=1);

namespace customiesdevs\customies\item\component;

final class PiercingWeaponComponent implements ItemComponent {

	private array $creativeReach = [];
	private float $hitboxMargin;
	private array $reach = [];

	public function __construct(
		array $creativeReach = ['min' => 2.0, 'max' => 7.5],
		float $hitboxMargin = 0.25,
		array $reach = ['min' => 2.0, 'max' => 4.5]
	) {
		$this->creativeReach = $creativeReach;
		$this->hitboxMargin = $hitboxMargin;
		$this->reach = $reach;
	}

	public function getName(): string {
		return 'minecraft:piercing_weapon';
	}

	public function getValue(): array {
		return [
			"creative_reach" => [
				"min" => (float) $this->creativeReach['min'],
				"max" => (float) $this->creativeReach['max']
			],
			"hitbox_margin" => $this->hitboxMargin,
			"reach" => [
				"min" => (float) $this->reach['min'],
				"max" => (float) $this->reach['max']
			]
		];
	}

	public function getPropertyMapping(): ?array {
		return null;
	}
}