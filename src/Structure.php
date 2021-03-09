<?php

namespace MarkJaquith\Wherewithal;

class Structure implements Contracts\StructureContract {
	private array $structure;

	public function __construct(array $structure) {
		$this->structure = $structure;
	}

	public function toString(): string {
//		$out = '';
//		foreach ($this->structure as $part) {
//
//		}
	}

	public function getBindings(): array {
		$values = array_filter($this->structure, fn($part) => $part['type'] === Parser::TOKEN_VALUE);

		return array_map(fn($part) => $part['value'], $values);
	}

	public function toArray(): array {
		return $this->structure;
	}
}
