<?php
// database/seeders/FormaPagoSeeder.php

namespace Database\Seeders;

use App\Models\FormaPago;
use Illuminate\Database\Seeder;

class FormaPagoSeeder extends Seeder
{
    public function run(): void
    {
        $empresaId = 1;

        // Formas de pago habilitadas por defecto
        $formasPago = [
            [
                'clave' => 'efectivo',
                'nombre' => 'Efectivo',
                'icono' => '💵',
                'orden' => 1,
                'activo' => true,
                'requiere_referencia' => false,
                'requiere_autorizacion' => false
            ],
            [
                'clave' => 'tarjeta_debito',
                'nombre' => 'Tarjeta Débito',
                'icono' => '💳',
                'orden' => 2,
                'activo' => true,
                'requiere_referencia' => false,
                'requiere_autorizacion' => false
            ],
            [
                'clave' => 'tarjeta_credito',
                'nombre' => 'Tarjeta Crédito',
                'icono' => '💎',
                'orden' => 3,
                'activo' => true,
                'requiere_referencia' => false,
                'requiere_autorizacion' => false
            ],
            [
                'clave' => 'transferencia',
                'nombre' => 'Transferencia',
                'icono' => '🏦',
                'orden' => 4,
                'activo' => true,
                'requiere_referencia' => true,
                'requiere_autorizacion' => false
            ],
            [
                'clave' => 'vale_despensa',
                'nombre' => 'Vale de Despensa',
                'icono' => '🛒',
                'orden' => 5,
                'activo' => true,
                'requiere_referencia' => false,
                'requiere_autorizacion' => false
            ],
            // Formas de pago deshabilitadas (activo = false)
            [
                'clave' => 'spei',
                'nombre' => 'SPEI',
                'icono' => '⚡',
                'orden' => 6,
                'activo' => false,
                'requiere_referencia' => true,
                'requiere_autorizacion' => false
            ],
            [
                'clave' => 'cheque',
                'nombre' => 'Cheque',
                'icono' => '📄',
                'orden' => 7,
                'activo' => false,
                'requiere_referencia' => true,
                'requiere_autorizacion' => false
            ],
            [
                'clave' => 'vale',
                'nombre' => 'Vale',
                'icono' => '🎫',
                'orden' => 8,
                'activo' => false,
                'requiere_referencia' => false,
                'requiere_autorizacion' => false
            ],
            [
                'clave' => 'monedero_electronico',
                'nombre' => 'Monedero Electrónico',
                'icono' => '📱',
                'orden' => 9,
                'activo' => false,
                'requiere_referencia' => false,
                'requiere_autorizacion' => false
            ],
            [
                'clave' => 'gift_card',
                'nombre' => 'Gift Card',
                'icono' => '🎁',
                'orden' => 10,
                'activo' => false,
                'requiere_referencia' => true,
                'requiere_autorizacion' => false
            ],
            [
                'clave' => 'paypal',
                'nombre' => 'PayPal',
                'icono' => '🅿️',
                'orden' => 11,
                'activo' => false,
                'requiere_referencia' => true,
                'requiere_autorizacion' => false
            ],
            [
                'clave' => 'mercado_pago',
                'nombre' => 'Mercado Pago',
                'icono' => '🟡',
                'orden' => 12,
                'activo' => false,
                'requiere_referencia' => true,
                'requiere_autorizacion' => false
            ],
            [
                'clave' => 'stripe',
                'nombre' => 'Stripe',
                'icono' => '⚡',
                'orden' => 13,
                'activo' => false,
                'requiere_referencia' => true,
                'requiere_autorizacion' => false
            ],
            [
                'clave' => 'wallet_digital',
                'nombre' => 'Wallet Digital',
                'icono' => '👛',
                'orden' => 14,
                'activo' => false,
                'requiere_referencia' => false,
                'requiere_autorizacion' => false
            ],
            [
                'clave' => 'deposito_bancario',
                'nombre' => 'Depósito Bancario',
                'icono' => '🏧',
                'orden' => 15,
                'activo' => false,
                'requiere_referencia' => true,
                'requiere_autorizacion' => false
            ],
            [
                'clave' => 'criptoactivo',
                'nombre' => 'Criptoactivo',
                'icono' => '₿',
                'orden' => 16,
                'activo' => false,
                'requiere_referencia' => true,
                'requiere_autorizacion' => false
            ],
        ];

        foreach ($formasPago as $forma) {
            FormaPago::updateOrCreate(
                ['empresa_id' => $empresaId, 'clave' => $forma['clave']],
                [
                    'nombre' => $forma['nombre'],
                    'icono' => $forma['icono'],
                    'orden' => $forma['orden'],
                    'activo' => $forma['activo'],
                    'requiere_referencia' => $forma['requiere_referencia'],
                    'requiere_autorizacion' => $forma['requiere_autorizacion']
                ]
            );
        }
    }
}