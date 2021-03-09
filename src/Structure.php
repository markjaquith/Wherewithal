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

	public function __toString(): string {
		$parts = array_map(fn($token) => $token->getSqlValue(), $this->tokens);
		$parts = array_filter($parts, fn($part) => strlen($part) > 0);

		return trim(join(' ', $parts));
	}

	public function toString(): string {
		return $this->__toString();
	}

	public function getBindings(): array {
		$values = array_filter($this->tokens, fn($token) => $token->isType(Token::VALUE));

		return array_values(array_map(fn($token) => $token->getValue(), $values));
	}

	public function toArray(): array {
		return $this->tokens;
	}
}
