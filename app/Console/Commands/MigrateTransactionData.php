<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MigrateTransactionData extends Command
{
    protected $signature = 'migrate:data';
    protected $description = 'Migrar pagos de servicios, pagos con tarjeta de crédito y usuarios de la base de datos 2 a la base de datos 1';

    public function handle(): int
    {
        try {
            
            $this->migrateUsers(); // Migrar pagos con tarjeta de crédito

            $this->migrateSpecialSections(); // Migrar pagos con tarjeta de crédito

            $this->migrateServicePayments(); // Migrar pagos de servicios
            $this->migrateCreditCardPayments(); // Migrar pagos con tarjeta de crédito

            $this->info('¡Todas las transacciones y usuarios fueron migrados con éxito!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("La migración falló: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    private function migrateUsers(): void
    {
        $this->info('Migrando usuarios...');

        // Obtener todos los usuarios de la tabla 'clientes'
        $users = DB::connection('mysql2') // Conéctate a la base de datos que contiene 'clientes'
            ->table('clientes')
            ->select('id_cliente', 'nombre') // Seleccionar los campos necesarios
            ->get();

        $usersData = [];
        
        // Prepara los datos para la inserción
        foreach ($users as $user) {
            $usersData[] = [
                'id' => $user->id_cliente,    // id de la tabla 'users' será el id_cliente de la tabla 'clientes'
                'name' => $user->nombre,      // name será el nombre de la tabla 'clientes'
                'role' => 'client',           // role siempre será 'client'
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        // Inserta los datos en la tabla 'users'
        if (!empty($usersData)) {
            DB::connection('mysql')->table('users')->insert($usersData);
            $this->info('¡Usuarios migrados con éxito!');
        } else {
            $this->info('No se encontraron usuarios para migrar.');
        }
    }

    private function migrateSpecialSections(): void
    {
        $this->info('Migrando secciones especiales...');
    
        $specialSectionsData = [];
    
        // Insertar 25 registros con id de 1 a 25
        for ($i = 1; $i <= 25; $i++) {
            $specialSectionsData[] = [
                'id' => $i,  // id desde 1 hasta 25
                'user_id' => $i,  // user_id igual al id
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }
    
        // Inserta los datos
        if (!empty($specialSectionsData)) {
            DB::connection('mysql')->table('special_sections')->insert($specialSectionsData);
            $this->info('¡Secciones especiales migradas con éxito!');
        }
    }

    private function migrateServicePayments(): void
    {
        $this->info('Migrando pagos de servicios...');

        $servicePayments = DB::connection('mysql2')
            ->table('pagos_servicios')
            ->orderBy('id_pago')
            ->lazy();

        $transactions = [];

        foreach ($servicePayments as $payment) {
            // Obtener el cliente_id usando id_cuenta de la tabla cuentas
            $clienteId = DB::connection('mysql2')
                ->table('cuentas')
                ->join('clientes', 'cuentas.id_cliente', '=', 'clientes.id_cliente')
                ->where('cuentas.id_cuenta', $payment->id_cuenta)
                ->value('clientes.id_cliente');

            if (!$clienteId) {
                $this->warn("No se encontró cliente para el pago de servicio {$payment->id_pago}. Saltando...");
                continue; // Si no se encuentra cliente_id, se salta la iteración
            }

            $transactions[] = [
                'user_id' => $clienteId,         // Usamos cliente_id como user_id
                'special_section_id' => 1,       // Asignamos la sección especial 1
                'transaction_type' => 'pago_servicio',
                'amount' => $payment->cantidad,
                'balance_after' => 0,
                'created_at' => Carbon::parse($payment->fecha_movimiento),
                'updated_at' => Carbon::parse($payment->fecha_movimiento),
            ];
        }

        if (!empty($transactions)) {
            DB::connection('mysql')->table('transactions')->insert($transactions);
            $this->info('¡Pagos de servicios migrados con éxito!');
        }
    }

    private function migrateCreditCardPayments(): void
    {
        $this->info('Migrando pagos con tarjeta de crédito...');

        $creditCardPayments = DB::connection('mysql2')
            ->table('pagos_tdc')
            ->orderBy('id_pago')
            ->lazy();

        $transactions = [];

        foreach ($creditCardPayments as $payment) {
            // Verificamos si id_tarjeta está presente en el registro de pago
            if (!isset($payment->id_tarjeta)) {
                $this->warn("El pago de tarjeta de crédito {$payment->id_pago} no tiene id_tarjeta. Saltando...");
                continue; // Si no tiene id_tarjeta, se salta la iteración
            }

            // Obtener el cliente_id usando id_tarjeta de la tabla tarjetas_credito
            $clienteId = DB::connection('mysql2')
                ->table('tarjetas_credito')
                ->join('clientes', 'tarjetas_credito.id_cliente', '=', 'clientes.id_cliente')
                ->where('tarjetas_credito.id_tarjeta', $payment->id_tarjeta)
                ->value('clientes.id_cliente');

            if (!$clienteId) {
                $this->warn("No se encontró cliente para el pago de tarjeta de crédito {$payment->id_pago}. Saltando...");
                continue; // Si no se encuentra cliente_id, se salta la iteración
            }

            $transactions[] = [
                'user_id' => $clienteId,         // Usamos cliente_id como user_id
                'special_section_id' => 1,
                'transaction_type' => 'pago_tdc',
                'amount' => $payment->cantidad_total,
                'balance_after' => 0,
                'created_at' => Carbon::parse($payment->fecha_movimiento),
                'updated_at' => Carbon::parse($payment->fecha_movimiento),
            ];
        }

        if (!empty($transactions)) {
            DB::connection('mysql')->table('transactions')->insert($transactions);
            $this->info('¡Pagos con tarjeta de crédito migrados con éxito!');
        }
    }
}
