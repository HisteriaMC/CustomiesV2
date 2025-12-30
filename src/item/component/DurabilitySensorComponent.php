<?php
declare(strict_types=1);

namespace customiesdevs\customies\item\component;

use customiesdevs\customies\item\utils\ParticleType;
use customiesdevs\customies\item\utils\SoundEvent;

final class DurabilitySensorComponent implements ItemComponent {

	private array $durabilityThresholds = [];

	/**
	 * Enables an item to emit effects when it receives damage. Because of this, the item also needs a `minecraft:durability` component.
	 * @param array<int, array{durability: int, particle_type: ?string, sound_event: ?string}> $durabilityThresholds
 	*/
	public function __construct(array $durabilityThresholds = []) {
		$this->durabilityThresholds = $durabilityThresholds;
	}

	public function getName(): string {
		return 'minecraft:durability_sensor';
	}

	public function getValue(): array {
		return [
			"durability_thresholds" => array_map(
				fn(array $threshold) => [
					"durability" => $threshold["durability"],
					"particle_type" => $threshold["particle_type"]?->value,
					"sound_event" => $threshold["sound_event"]?->value
				],
				$this->durabilityThresholds
			)
		];
	}

	public function getPropertyMapping(): ?array {
		return null;
	}

	/**
	 * Adds a new durability threshold.
	 *
	 * @param int $durability The durability at which this threshold triggers
	 * @param ParticleType|null $particleType Optional particle effect
	 * @param SoundEvent|null $soundEvent Optional sound effect
	 */
	public function addDurabilityThreshold(
		int $durability,
		?ParticleType $particleType = null,
		?SoundEvent $soundEvent = null
	): self {
	   $this->durabilityThresholds[] = [
			"durability" => $durability,
			"particle_type" => $particleType,
			"sound_event" => $soundEvent
		];
		return $this;
	}
}