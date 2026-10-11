<?php

namespace App\Ejma;

/** Expansion joint assemblies in scope (EJMA 9th Ed. §2.5–2.9, p031–p046). */
enum AssemblyType: string
{
    case Single = 'single';
    case Universal = 'universal';
    case TiedUniversal = 'tied_universal';
    case Hinged = 'hinged';
    case Gimbal = 'gimbal';

    /** Number of identical bellows in the assembly: 2 for universal types (dual bellows), else 1. */
    public function bellowsCount(): int
    {
        return $this->isUniversal() ? 2 : 1;
    }

    public function isUniversal(): bool
    {
        return $this === self::Universal || $this === self::TiedUniversal;
    }

    /** Angular-only assemblies: axial and lateral movement are restrained by the hardware. */
    public function isAngularOnly(): bool
    {
        return $this === self::Hinged || $this === self::Gimbal;
    }

    public function label(): string
    {
        return match ($this) {
            self::Single => 'Single',
            self::Universal => 'Universal',
            self::TiedUniversal => 'Tied universal',
            self::Hinged => 'Hinged',
            self::Gimbal => 'Gimbal',
        };
    }
}
