<?php
// app/Exports/Sheets/BaseSheet.php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;

abstract class BaseSheet implements FromQuery, WithTitle, WithHeadings, ShouldAutoSize, WithMapping
{
    protected $empresaId;
    protected $titulo;

    public function __construct($empresaId, $titulo)
    {
        $this->empresaId = $empresaId;
        $this->titulo = $titulo;
    }

    public function title(): string
    {
        return $this->titulo;
    }

    public function headings(): array
    {
        return $this->getHeadings();
    }

    abstract protected function getHeadings(): array;
    abstract public function query();
}