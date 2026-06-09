<?php
// app/Imports/Sheets/ClientesImport.php

namespace App\Imports\Sheets;

use App\Models\Cliente;

class ClientesImport extends BaseImport
{
    public function model(array $row)
    {
        // Verificar si ya existe por ID o por RFC
        $cliente = Cliente::find($row['id'] ?? null);
        
        if ($cliente && $cliente->empresa_id == $this->empresaId) {
            // Actualizar existente
            $cliente->update([
                'nombre' => $row['nombre'],
                'rfc' => $row['rfc'],
                'telefono' => $row['telefono'],
                'correo' => $row['correo'],
                'direccion' => $row['direccion'],
                'tipo' => $row['tipo'],
                'limite_credito' => $row['limite_credito'],
                'dias_credito' => $row['dias_credito'],
                'activo' => $row['activo'] == 'Sí' ? 1 : 0,
            ]);
        } else {
            // Crear nuevo
            Cliente::create([
                'empresa_id' => $this->empresaId,
                'nombre' => $row['nombre'],
                'rfc' => $row['rfc'],
                'telefono' => $row['telefono'],
                'correo' => $row['correo'],
                'direccion' => $row['direccion'],
                'tipo' => $row['tipo'],
                'limite_credito' => $row['limite_credito'],
                'dias_credito' => $row['dias_credito'],
                'activo' => $row['activo'] == 'Sí' ? 1 : 0,
            ]);
        }
        
        $this->mensajes[] = "✓ Clientes procesados";
        return null;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required',
        ];
    }
}