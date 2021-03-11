<?php

namespace MarkJaquith\Wherewithal;

use MarkJaquith\Wherewithal\Contracts\TokenContract;

class Structure implements Contracts\StructureContract {
	/**
	 * @var TokenContract[]
	 */
	private array $tokens;

	/**
	 * Structure constructor.
	 *
	 * @param TokenContract[] $tokens
	 */
	public function __construct(array $tokens) {
		$this->tokens = $tokens;
	}

	public function __toString(): string {
		$parts = array_map(fn(TokenContract $token): string => $token->getSqlValue(), $this->tokens);
		$parts = array_filter($parts, fn(string $part): bool  => strlen($part) > 0);

		return trim(join(' ', $parts));
	}

	public function toString(): string {
		return $this->__toString();
	}

	/**
	 * Get the bindings for the SQL clause.
	 * 
	 * They will be listed in the order the appear in the query.
	 *
	 * @return string[]
	 */
	public function getBindings(): array {
		$values = array_filter($this->tokens, fn(TokenContract $token): bool => $token->isType(Token::VALUE));

		return array_values(array_map(fn(TokenContract $token): string => $token->getValue(), $values));
	}

	public function toArray(): array {
		return $this->tokens;
	}

	public function mapColumns(callable $fn): self {
		$out = [];

		foreach ($this->tokens as $token) {
			if ($token->isType(Token::COLUMN)) {
				$newColumn = (string) $fn($token->getValue());
				if ($token->getValue() !== $newColumn) {
					$out[] = new Token(Token::COLUMN, '(' . $newColumn . ')');
				} else {
					$out[] = $token;
				}
			} else {
				$out[] = $token;
			}
		}
		return new self($out);
	}
}
