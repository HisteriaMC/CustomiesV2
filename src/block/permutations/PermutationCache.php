<?php
declare(strict_types=1);

namespace customiesdevs\customies\block\permutations;

final class PermutationCache {

	/** @var array<class-string, list<list<mixed>>> */
	private static array $cartesian = [];

	/**
	 * @return list<list<mixed>>
	 */
	public static function getCartesian(Permutable $block): array {
		$class = $block::class;
		return self::$cartesian[$class] ??= Permutations::getCartesianProduct(
			array_map(
				static fn(BlockProperty $property) => $property->getValues(),
				$block->getBlockProperties()
			)
		);
	}
}