<?php
declare(strict_types=1);

namespace customiesdevs\customies\block\utils;

/**
 * Describes a block reference for placement filtering.
 *
 * Can reference blocks by:
 * - Name
 * - Name + states
 * - Molang tag query
 */
final class BlockDescriptor {

	/** @var string|null */
	private ?string $name;
	/** @var array<int, array{state: string, type: int, value: mixed}> */
	private array $states = [];
	/** @var string|null */
	private ?string $tags;
	/** @var int|null */
	private ?int $tagsVersion;

	/**
	 * @param string|null $name Block identifier (e.g. minecraft:dirt)
	 * @param array<int, array{state: string, type: int, value: mixed}> $states
	 * @param string|null $tags Molang tag query
	 * @param int|null $tagsVersion Molang version
	 */
	public function __construct(
		?string $name = null,
		array $states = [],
		?string $tags = null,
		?int $tagsVersion = null
	) {
		$this->name = $name;
		$this->states = $states;
		$this->tags = $tags;
		$this->tagsVersion = $tagsVersion;
	}

	/**
	 * Converts descriptor to Bedrock format.
	 */
	public function toArray(): array {
		$data = [];
		if($this->name !== null){
			$data["name"] = $this->name;
		}
		if(!empty($this->states)){
			$data["states"] = $this->states;
		}
		if($this->tags !== null){
			$data["tags"] = $this->tags;
			if($this->tagsVersion !== null) {
				$data["tags_version"] = $this->tagsVersion;
			}
		}
		return $data;
	}

	/**
	 * Creates a BlockDescriptor from an array.
	 */
	public static function fromArray(array $data): self {
		return new self(
			$data["name"] ?? null,
			$data["states"] ?? [],
			$data["tags"] ?? null,
			$data["tags_version"] ?? null
		);
	}
}