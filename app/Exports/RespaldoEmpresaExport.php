<?php
// app/Exports/RespaldoEmpresaExport.php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RespaldoEmpresaExport implements WithMultipleSheets
{
    protected $empresaId;

    public function __construct($empresaId)
    {
        $this->empresaId = $empresaId;
    }

    public function sheets(): array
    {
        return [
            'info_empresa' => new Sheets\InfoEmpresaSheet($this->empresaId),
            'clientes' => new Sheets\ClientesSheet($this->empresaId),
            'productos' => new Sheets\ProductosSheet($this->empresaId),
            'insumos' => new Sheets\InsumosSheet($this->empresaId),
            'proveedores' => new Sheets\ProveedoresSheet($this->empresaId),
            'categorias' => new Sheets\CategoriasSheet($this->empresaId),
            'unidades_medida' => new Sheets\UnidadesMedidaSheet($this->empresaId),
            'formas_pago' => new Sheets\FormasPagoSheet($this->empresaId),
            'ventas' => new Sheets\VentasSheet($this->empresaId),
            'ventas_detalles' => new Sheets\VentasDetallesSheet($this->empresaId),
            'creditos' => new Sheets\CreditosSheet($this->empresaId),
            'pagares' => new Sheets\PagaresSheet($this->empresaId),
            'cobranzas' => new Sheets\CobranzasSheet($this->empresaId),
            'cajas' => new Sheets\CajasSheet($this->empresaId),
            'cotizaciones' => new Sheets\CotizacionesSheet($this->empresaId),
            'cotizaciones_detalles' => new Sheets\CotizacionesDetallesSheet($this->empresaId),
            'inventario_movimientos' => new Sheets\InventarioMovimientosSheet($this->empresaId),
            //'sucursales' => new Sheets\SucursalesSheet($this->empresaId),
        ];
    }
}