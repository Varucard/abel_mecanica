<?php

declare(strict_types=1);

namespace App\Enums;

enum Combustible: string
{
  case Nafta = 'nafta';
  case Diesel = 'diesel';
  case Gnc = 'gnc';
  case NaftaGnc = 'nafta_gnc';
  case Electrico = 'electrico';
  case Hibrido = 'hibrido';

  public function label(): string
  {
    return match ($this) {
      self::Nafta => 'Nafta',
      self::Diesel => 'Diésel',
      self::Gnc => 'GNC',
      self::NaftaGnc => 'Nafta / GNC',
      self::Electrico => 'Eléctrico',
      self::Hibrido => 'Híbrido',
    };
  }
}
