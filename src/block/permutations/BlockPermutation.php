<?php
declare(strict_types=1);

namespace customiesdevs\customies\block\permutations;

use customiesdevs\customies\block\component\BlockComponent;

class BlockPermutation {

	/**
	 * @param string $condition The condition to evaluate for this permutation
	 * @param BlockComponent $components The components to apply if the condition is met
	 */
	public function __construct(
		private readonly string $condition,
		private readonly BlockComponent $components
	) {}

	/**
	 * Gets the condition string for this permutation.
	 */
	public function getCondition(): string {
		return $this->condition;
	}

	/**
	 * Gets the components associated with this permutation.
	 */
	public function getComponents(): BlockComponent {
		return $this->components;
	}

	/**
	 * Converts the BlockPermutation to an array format.
	 */
	public function toArray(): array {
		return [
			"condition" => $this->condition,
			"components" => [
				$this->components->getName() => $this->components->getValue()
			]
		];
	}

	/**
	 * Computes the Cartesian product of the provided arrays.
	 * Each array in the input represents a set of possible values for a block property.
	 * The result is an array of all possible combinations of these values.
	 *
	 * @param array[] $arrays An array of arrays, each containing possible values for a block property
	 * @return array An array of arrays, each representing a unique combination of property values
	 */
	public static function getCartesianProduct(array $arrays): array {
		if($arrays === []){
			return [[]];
		}
		$result = [];
		$count = count($arrays) - 1;
		$combinations = array_product(array_map(static fn(array $array) => count($array), $arrays));
		for($i = 0; $i < $combinations; $i++){
			$row = [];
			foreach($arrays as $index => $_){
				$row[] = current($arrays[$index]);
			}
			$result[] = $row;
			for($j = $count; $j >= 0; $j--){
				if(next($arrays[$j]) !== false){
					break;
				}
				reset($arrays[$j]);
			}
		}
		return $result;
	}
}