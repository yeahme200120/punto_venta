<?php
// app/Imports/Sheets/BaseImport.php

namespace App\Imports\Sheets;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

abstract class BaseImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $empresaId;
    protected $mensajes;

    public function __construct($empresaId, &$mensajes)
    {
        $this->empresaId = $empresaId;
        $this->mensajes = &$mensajes;
    }

    abstract public function model(array $row);
    abstract public function rules(): array;
}