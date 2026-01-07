<?php

namespace App\Objects;

class MoneyObject
{
    public function __construct(
        private ?int $minorUnits = 0,
        private int  $decimalPlaces = 2
    )
    {
    }

    public function getMinorUnits(): int
    {
        return $this->minorUnits ?? 0;
    }

    public function getMajorUnits(): float
    {
        return $this->minorUnits / pow(10, $this->decimalPlaces);
    }

    public function format(): string
    {
        return number_format($this->getMajorUnits(), $this->decimalPlaces, '.', '');
    }

    public function increase(float|int $amount): self
    {
        return new self($this->minorUnits + $amount, $this->decimalPlaces);
    }

    public function decrease(float|int $amount): self
    {
        return new self($this->minorUnits - $amount, $this->decimalPlaces);
    }

    public function isGreaterThan(MoneyObject $other): bool
    {
        return $this->minorUnits > $other->minorUnits;
    }

    public function isLessThan(MoneyObject $other): bool
    {
        return $this->minorUnits < $other->minorUnits;
    }

    public function isEqual(MoneyObject $other): bool
    {
        return $this->minorUnits === $other->minorUnits;
    }

    public function __toString(): string
    {
        return $this->format();
    }
}
