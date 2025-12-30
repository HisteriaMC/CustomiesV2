<?php

namespace customiesdevs\customies\item\utils;

final class RepairItems {

	/**
	 * @param string[] $items List of item names that may be used for repairing.
	 * @param int $repairAmount Amount by which the item is repaired.
	 */
	public function __construct(
		private readonly array $items = [],
		private readonly int $repairAmount,
	) {}

	/**
	 * Returns an array representation of the repair items.
	 *
	 * The returned array has the following structure:
	 * [
	 *   "items" => [
	 *       ["name" => "item1"],
	 *       ["name" => "item2"],
	 *       ...
	 *   ],
	 *   "repair_amount" => int
	 * ]
	 *
	 * @return array{
	 *     items: array<int, array{name: string}>,
	 *     repair_amount: int
	 * }
	 */
	public function toArray(): array {
		$items = [];
		foreach($this->items as $item) {
			$items[] = [
				"name" => $item
			];
		}
		return [
			"items" => $items,
			"repair_amount" => $this->repairAmount
		];
	}

	/**
	 * Creates a RepairItems instance from an array representation.
	 *
	 * Expects an array in the format returned by `toArray()`.
	 * Missing or invalid values are replaced with defaults (empty array or 0).
	 *
	 * @param array{
	 *     items?: array<int, array{name?: string}>,
	 *     repair_amount?: int
	 * } $data
	 * @return self
	 */
	public static function fromArray(array $data): self {
		$items = [];
		if(is_array($data["items"] ?? null)) {
			foreach($data["items"] as $item) {
				$items[] = $item["name"] ?? "";
			}
		}
		return new self($items, $data["repair_amount"] ?? 0);
	}

	/**
	 * Returns the list of item names that can be used for repair.
	 *
	 * @return string[]
	 */
	public function getItems(): array {
		return $this->items;
	}

	/**
	 * Returns the repair amount provided by these items.
	 * @return int
	 */
	public function getRepairAmount(): int {
		return $this->repairAmount;
	}
}