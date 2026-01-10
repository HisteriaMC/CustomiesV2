<?php
declare(strict_types=1);

namespace customiesdevs\customies\item\component;

use pocketmine\nbt\tag\ByteTag;

final class DamageComponent implements ItemComponent {

	private int $damage;

	/**
	 * Determines how much extra damage the item does on attack. 
	 * Note that this must be a positive value.
	 * @param int $damage Specifies how much extra damage the item does, must be a positive number.
	 * @throws \InvalidArgumentException if the damage value is negative.
	 */
	public function __construct(int $damage) {
		if($damage < 0) {
			throw new \InvalidArgumentException("Damage value must be a positive number, $damage given");
		}
		$this->damage = $damage;
	}

	public function getName(): string {
		return 'minecraft:damage';
	}

	public function getValue(): array {
		return [
			"value" => new ByteTag($this->damage)
		];
	}

	public function getPropertyMapping(): ?array {
		return ['damage' => $this->damage];
	}
}