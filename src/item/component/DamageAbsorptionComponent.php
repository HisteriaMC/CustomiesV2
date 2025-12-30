<?php
declare(strict_types=1);

namespace customiesdevs\customies\item\component;

use customiesdevs\customies\item\utils\DamageCause;

final class DamageAbsorptionComponent implements ItemComponent {

	 /**
	 * List of damage causes that can be absorbed by the item.
	 * @var DamageCause[] Must contain at least 1 item for meaningful effect.
	 */
	private array $absorbableCauses = [];

	/**
	 * It allows an item to absorb damage that would otherwise be dealt to its wearer.
	 * For this to happen, the item needs to be equipped in an armor slot.
	 * The absorbed damage reduces the item's durability, with any excess damage being ignored.
	 * Because of this, the item also needs a `minecraft:durability` component.
	 * @param array $absorbableCauses List of damage causes that can be absorbed by the item. By default, no damage cause is absorbed. Value must have at least 1 items.
	 */
	public function __construct(array $absorbableCauses = []) {
		$this->absorbableCauses = $absorbableCauses;
	}

	public function getName(): string {
		return 'minecraft:damage_absorption';
	}

	public function getValue(): array {
		return [
			"absorbable_causes" => array_map(
				fn(DamageCause $cause) => $cause->value,
				$this->absorbableCauses
			)
		];
	}

	public function getPropertyMapping(): ?array {
		return null;
	}

	/**
	 * Adds a single damage cause to the absorbable list.
	 * @param DamageCause $cause
	 */
	public function addCause(DamageCause $cause): self {
		if(!in_array($cause, $this->absorbableCauses, true)){
			$this->absorbableCauses[] = $cause;
		}
		return $this;
	}
}