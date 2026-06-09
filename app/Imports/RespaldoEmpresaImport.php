<?php
// app/Imports/RespaldoEmpresaImport.php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RespaldoEmpresaImport implements WithMultipleSheets
{
    protected $empresaId;
    protected $mensajes = [];

    public function __construct($empresaId)
    {
        $this->empresaId = $empresaId;
    }

    public function sheets(): array
    {
        return [
            'clientes' => new Sheets\ClientesImport($this->empresaId, $this->mensajes),
            'productos' => new Sheets\ProductosImport($this->empresaId, $this->mensajes),
            'insumos' => new Sheets\InsumosImport($this->empresaId, $this->mensajes),
            'proveedores' => new Sheets\ProveedoresImport($this->empresaId, $this->mensajes),
            'categorias' => new Sheets\CategoriasImport($this->empresaId, $this->mensajes),
            'unidades_medida' => new Sheets\UnidadesMedidaImport($this->empresaId, $this->mensajes),
            'formas_pago' => new Sheets\FormasPagoImport($this->empresaId, $this->mensajes),
            'ventas' => new Sheets\VentasImport($this->empresaId, $this->mensajes),
            'creditos' => new Sheets\CreditosImport($this->empresaId, $this->mensajes),
            'cajas' => new Sheets\CajasImport($this->empresaId, $this->mensajes),
        ];
    }

    public function getMensajes()
    {
        return $this->mensajes;
    }
}