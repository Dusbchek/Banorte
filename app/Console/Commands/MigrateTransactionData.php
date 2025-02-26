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
           
            $this->migrateUsers();
            $this->migrateSpecialSections();
            $this->migrateServicePayments();
            $this->migrateCreditCardPayments();

            $this->migrateDailyExpenses();  
            $this->migrateDailyIncomes();   
    

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

        $users = DB::connection('mysql2')
            ->table('clientes')
            ->select('id_cliente', 'nombre')
            ->get();

        $usersData = [];
        foreach ($users as $user) {
            $usersData[] = [
                'id' => $user->id_cliente,
                'name' => $user->nombre,
                'role' => 'client',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

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
    
        for ($i = 1; $i <= 25; $i++) {
            $specialSectionsData[] = [
                'id' => $i,
                'user_id' => $i,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }
    
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
            $clienteId = DB::connection('mysql2')
                ->table('cuentas')
                ->join('clientes', 'cuentas.id_cliente', '=', 'clientes.id_cliente')
                ->where('cuentas.id_cuenta', $payment->id_cuenta)
                ->value('clientes.id_cliente');

            if (!$clienteId) {
                $this->warn("No se encontró cliente para el pago de servicio {$payment->id_pago}. Saltando...");
                continue;
            }

            $transactions[] = [
                'user_id' => $clienteId,
                'special_section_id' => 1,
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
            if (!isset($payment->id_tarjeta)) {
                $this->warn("El pago de tarjeta de crédito {$payment->id_pago} no tiene id_tarjeta. Saltando...");
                continue;
            }

            $clienteId = DB::connection('mysql2')
                ->table('tarjetas_credito')
                ->join('clientes', 'tarjetas_credito.id_cliente', '=', 'clientes.id_cliente')
                ->where('tarjetas_credito.id_tarjeta', $payment->id_tarjeta)
                ->value('clientes.id_cliente');

            if (!$clienteId) {
                $this->warn("No se encontró cliente para el pago de tarjeta de crédito {$payment->id_pago}. Saltando...");
                continue;
            }

            $transactions[] = [
                'user_id' => $clienteId,
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

    


    private function migrateDailyExpenses(): void
{
    $this->info('Migrando gastos diarios...');

    // Obtener las transacciones desde la tabla 'transactions'
    $transactions = DB::connection('mysql')
        ->table('transactions')
        ->select('user_id', 'amount', 'created_at', 'updated_at')
        ->get();

    $dailyExpensesData = [];

    foreach ($transactions as $transaction) {
        // Obtener el saldo correspondiente del 'user_id' (que es 'id_cliente' en 'cuentas' de mysql2)
        $balanceAfter = DB::connection('mysql2')
            ->table('cuentas')
            ->where('id_cliente', $transaction->user_id)
            ->value('saldo'); // Obtener el saldo de la tabla 'cuentas' que corresponde al 'user_id'

        if ($balanceAfter === null) {
            $this->warn("No se encontró saldo para el cliente con user_id {$transaction->user_id}. Saltando...");
            continue; // Si no se encuentra saldo, se salta la iteración
        }

        // Preparamos los datos para la inserción
        $dailyExpensesData[] = [
            'user_id' => $transaction->user_id,
            'expense_amount' => $transaction->amount,
            'balance_after' => $balanceAfter, // Usamos el saldo obtenido
            'created_at' => $transaction->created_at,
            'updated_at' => $transaction->updated_at,
        ];
    }

    // Si hay datos para insertar, los insertamos en la tabla 'daily_expenses'
    if (!empty($dailyExpensesData)) {
        DB::connection('mysql')->table('daily_expenses')->insert($dailyExpensesData);
        $this->info('¡Gastos diarios migrados con éxito!');
    } else {
        $this->info('No se encontraron transacciones para migrar a daily_expenses.');
    }
}

private function migrateDailyIncomes(): void
{
    $this->info('Migrando ingresos diarios...');

    // Vaciar la tabla 'daily_incomes'
    DB::connection('mysql')->table('daily_incomes')->truncate();

    // Obtener los saldos de la tabla 'cuentas' y actualizar 'daily_incomes'
    $cuentas = DB::connection('mysql2')
        ->table('cuentas')
        ->whereNotNull('id_cliente')  // Filtrar para asegurarnos de que 'id_cliente' no sea NULL
        ->whereNotNull('saldo')      // Asegurarnos de que 'saldo' también no sea NULL
        ->select('id_cliente', 'saldo') // Obtener el id_cliente y saldo
        ->get();

    $dailyIncomesData = [];

    foreach ($cuentas as $cuenta) {
        // Preparar los datos para la inserción
        $dailyIncomesData[] = [
            'user_id' => $cuenta->id_cliente,  // 'user_id' será el 'id_cliente' de la tabla 'cuentas'
            'income_amount' => $cuenta->saldo, // 'income_amount' será el 'saldo'
            'balance_after' => $cuenta->saldo, // 'balance_after' será el 'saldo'
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    // Si hay datos para insertar, los insertamos en la tabla 'daily_incomes'
    if (!empty($dailyIncomesData)) {
        DB::connection('mysql')->table('daily_incomes')->insert($dailyIncomesData);
        $this->info('¡Ingresos diarios migrados con éxito!');
    } else {
        $this->info('No se encontraron saldos válidos para migrar a daily_incomes.');
    }
}


}
