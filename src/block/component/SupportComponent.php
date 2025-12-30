<?php

namespace customiesdevs\customies\block\component;

final class SupportComponent implements BlockComponent {

	public const FENCE = "fence";
	public const STAIRS = "stair";

	private string $shape;

	/**
	 * @param string $shape Support shape (e.g. "stair")
	 */
	public function __construct(string $shape = self::STAIRS) {
		$this->shape = $shape;
	}

	public function getName(): string {
		return 'minecraft:support';
	}

	public function getValue(): array {
		return [
			"shape" => $this->shape
		];
	}
}