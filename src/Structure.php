<?php

namespace MarkJaquith\Wherewithal;

class Structure implements Contracts\StructureContract {
	private array $structure;

	public function __construct(array $structure) {
		$this->structure = $structure;
	}

	public function toString(): string {
		return var_export($this->structure);
	}

	public function toArray(): array {
		return $this->structure;
	}
}
