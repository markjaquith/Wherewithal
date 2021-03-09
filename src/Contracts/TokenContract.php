<?php

namespace MarkJaquith\Wherewithal\Contracts;

interface TokenContract {
	public function getValue(): string;
	public function getSqlValue(): string;
	public static function from(string $input): self;
}