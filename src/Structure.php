<?php

namespace MarkJaquith\Wherewithal;

use MarkJaquith\Wherewithal\Contracts\TokenContract;

class Structure implements Contracts\StructureContract {
	private array $tokens;

	public function __construct(array $tokens) {
		foreach ($tokens as $token) {
			if (!$token instanceof TokenContract) {
				throw new \InvalidArgumentException('Structure constructor only accepts an array of MarkJaquith\Wherewithal\Contracts\TokenContract');
			}
		}

		$this->tokens = $tokens;
	}

	public function toString(): string {
//		$out = '';
//		foreach ($this->tokens as $part) {
//
//		}
	}

	public function getBindings(): array {
		$values = array_filter($this->tokens, fn($part) => $part['type'] === Parser::TOKEN_VALUE);

		return array_map(fn($part) => $part['value'], $values);
	}

	public function toArray(): array {
		return $this->tokens;
	}
}
