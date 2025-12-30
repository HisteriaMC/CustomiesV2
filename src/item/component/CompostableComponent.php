<?php
declare(strict_types=1);

namespace customiesdevs\customies\item\component;

final class CompostableComponent implements ItemComponent {

	private int $compostingChance;

	/**
	 * Specifies that an item is compostable and provides the chance of creating a composting layer in the composter.
	 * @param int $compostingChance The chance of this item to create a layer upon composting with the composter. Valid value range is 1 - 100 inclusive Value must be >= 1. Value must be <= 100.
	 */
	public function __construct(int $compostingChance) {
		$this->compostingChance = $compostingChance;
	}

	public function getName(): string {
		return 'minecraft:compostable';
	}

	public function getValue(): array {
		return [
			"composting_chance" => $this->compostingChance
		];
	}

	public function getPropertyMapping(): ?array {
		return null;
	}
}