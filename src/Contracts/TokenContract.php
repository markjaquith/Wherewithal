<?php

namespace MarkJaquith\Wherewithal\Contracts;

interface TokenContract {
	public function getValue(): string;
	public function getSqlValue(): string;
	public function getType(): int;
	public function isType(int $type): bool;
}