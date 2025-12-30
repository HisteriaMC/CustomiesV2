<?php
declare(strict_types=1);

namespace customiesdevs\customies\item\component;

final class SwingSoundsComponent implements ItemComponent {

	private string $critical;
	private string $hit;
	private string $miss;

	public function __construct(
		string $critical = "attack.critical",
		string $hit = "attack.strong",
		string $miss = "attack.nodamage"
	) {
		$this->critical = $critical;
		$this->hit = $hit;
		$this->miss = $miss;
	}

	public function getName(): string {
		return "minecraft:swing_sounds";
	}

	public function getValue(): array {
		return [
			"attack_critical_hit" => $this->critical,
			"attack_hit" => $this->hit,
			"attack_miss" => $this->miss,
		];
	}

	public function getPropertyMapping(): ?array {
		return null;
	}
}