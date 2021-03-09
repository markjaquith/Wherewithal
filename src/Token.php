<?php

namespace MarkJaquith\Wherewithal;

use MarkJaquith\Wherewithal\Contracts\TokenContract;

class Token implements TokenContract {
	private ?string $value = null;
	private int $type = -1;

	const UNKNOWN = -1;
	const PATTERN_START = 0;
	const GROUP_START = 1;
	const GROUP_END = 2;
	const AND = 4;
	const OR = 8;
	const OPERATOR = 16;
	const COLUMN = 32;
	const VALUE = 64;
	const PATTERN_END = 128;

	const VALUE_MAP = [
		-1 => '',
		0 => '',
		1 => '(',
		2 => ')',
		4 => 'and',
		8 => 'or',
		128 => '',
	];

	public function __construct(int $type = self::UNKNOWN, ?string $value = null) {
		$this->type = $type;
		$this->value = $value;
	}

	public function getValue(): string {
		switch ($this->type) {
			case self::VALUE:
			case self::COLUMN:
			case self::OPERATOR:
				return $this->value;
			default:
				return self::VALUE_MAP[$this->getType()] ?? '';
		}
	}

	public function getType(): int {
		return $this->type;
	}

	public function isType(int $type): bool {
		return $this->getType() === $type;
	}

	public function getSqlValue(): string {
		return $this->isType(self::VALUE) ? '?' : $this->getValue();
	}
}