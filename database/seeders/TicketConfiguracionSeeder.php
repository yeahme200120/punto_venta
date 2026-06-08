<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\TicketConfiguracion;
use Illuminate\Database\Seeder;

class TicketConfiguracionSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            'movimiento',
            'transferencia',
            'arqueo',
            'cierre'
        ];

        $empresas = Empresa::all();

        foreach ($empresas as $empresa) {

            foreach ($tipos as $tipo) {

                TicketConfiguracion::updateOrCreate(
                    [
                        'empresa_id' => $empresa->id,
                        'tipo' => $tipo
                    ],
                    [
                        'nombre_empresa'     => $empresa->nombre,
                        'logo_url'           => $empresa->logo_url ?? null,
                        'direccion'          => $empresa->direccion ?? null,
                        'telefono'           => $empresa->telefono ?? null,
                        'email'              => $empresa->email ?? null,
                        'rfc'                => $empresa->rfc ?? null,

                        'cabecera'           => strtoupper($tipo),
                        'footer'             => 'Gracias por utilizar nuestro sistema.',

                        'mostrar_logo'       => true,
                        'mostrar_direccion'  => true,
                        'mostrar_telefono'   => true,
                        'mostrar_email'      => true,
                        'mostrar_rfc'        => true,

                        'ancho_papel'        => '80mm',
                        'fuente'             => 'monospace',
                        'tamano_fuente'      => 12,

                        'regimen_fiscal'     => null,
                        'uso_cfdi'           => null,
                        'mostrar_regimen'    => false,

                        'auto_imprimir'      => true,
                        'facturar'           => true,
                        'copias'             => 1,

                        'activo'             => true,
                    ]
                );
            }
        }
    }
}