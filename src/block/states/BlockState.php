<?php
declare(strict_types=1);

namespace customiesdevs\customies\block\states;

/**
 * Represents a block state property with a name and array of possible values.
 * Automatically detects the value type (bool, int, string) for serialization.
 */
class BlockState {

	/** @var mixed The current value of the state property */
	protected mixed $currentValue;

	/**
	 * @param string $name The state property name (e.g., "customies:rotation")
	 * @param array $values Array of possible values (bool[], int[], or string[])
	 */
	public function __construct(
		protected readonly string $name,
		protected readonly array $values
	) {
		$this->currentValue = $values[0] ?? null;
	}

	/**
	 * Returns the state property name.
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Returns the possible values array.
	 * @return array
	 */
	public function getValues(): array {
		return $this->values;
	}

	/**
	 * Returns the NBT value definition for client (enum + name).
	 * @return array
	 */
	public function getValue(): array {
		return [
			"enum" => $this->values,
			"name" => $this->name
		];
	}
}