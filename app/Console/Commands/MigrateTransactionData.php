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
            $this->migrateInvestments();
            $this->migrateInvestmentResults();
            $this->migrateFinancialAdvices();

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

    $transactions = DB::connection('mysql')
        ->table('transactions')
        ->select('user_id', 'amount', 'created_at', 'updated_at')
        ->get();

    $dailyExpensesData = [];

    foreach ($transactions as $transaction) {
        $balanceAfter = DB::connection('mysql2')
            ->table('cuentas')
            ->where('id_cliente', $transaction->user_id)
            ->value('saldo'); 
        if ($balanceAfter === null) {
            $this->warn("No se encontró saldo para el cliente con user_id {$transaction->user_id}. Saltando...");
            continue;
        }

        $dailyExpensesData[] = [
            'user_id' => $transaction->user_id,
            'expense_amount' => $transaction->amount,
            'balance_after' => $balanceAfter, 
            'created_at' => $transaction->created_at,
            'updated_at' => $transaction->updated_at,
        ];
    }

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

    DB::connection('mysql')->table('daily_incomes')->truncate();

    $cuentas = DB::connection('mysql2')
        ->table('cuentas')
        ->whereNotNull('id_cliente') 
        ->whereNotNull('saldo')    
        ->select('id_cliente', 'saldo') 
        ->get();

    $dailyIncomesData = [];

    foreach ($cuentas as $cuenta) {
        
        $dailyIncomesData[] = [
            'user_id' => $cuenta->id_cliente,  
            'income_amount' => $cuenta->saldo,
            'balance_after' => $cuenta->saldo,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    
    if (!empty($dailyIncomesData)) {
        DB::connection('mysql')->table('daily_incomes')->insert($dailyIncomesData);
        $this->info('¡Ingresos diarios migrados con éxito!');
    } else {
        $this->info('No se encontraron saldos válidos para migrar a daily_incomes.');
    }
}


private function migrateInvestments(): void
{
    $this->info('Migrando inversiones...');

    // Vaciar la tabla 'investments'
    DB::connection('mysql')->table('investments');

    // Obtener todos los usuarios
    $users = DB::connection('mysql')->table('users')->pluck('id');

    // Obtener todas las secciones especiales
    $specialSections = DB::connection('mysql')->table('special_sections')->pluck('id');

    $investmentTypes = ['acciones', 'otro', 'crypto'];
    $statuses = ['completado', 'pendiente', 'fallida'];

    $investmentsData = [];

    // Generar 500 registros
    for ($i = 0; $i < 500; $i++) {
        $userId = $users->random();

        $specialSectionId = $specialSections->random();

        $investmentType = $investmentTypes[array_rand($investmentTypes)];

        $amount = rand(1000, 100000);

        $result = rand(-10000, 20000); // Pérdidas de hasta -10,000 y ganancias de hasta 20,000

        $status = $statuses[array_rand($statuses)];

        $investmentsData[] = [
            'user_id' => $userId,
            'special_section_id' => $specialSectionId,
            'investment_type' => $investmentType,
            'amount' => $amount,
            'result' => $result,
            'status' => $status,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    if (!empty($investmentsData)) {
        DB::connection('mysql')->table('investments')->insert($investmentsData);
        $this->info('¡Inversiones migradas con éxito!');
    } else {
        $this->info('No se encontraron inversiones para migrar.');
    }
}

private function migrateInvestmentResults(): void
{
    $this->info('Migrando resultados de inversiones...');

    // Obtener todos los registros de la tabla 'investments'
    $investments = DB::connection('mysql')->table('investments')->select('id')->get();

    // Generar datos para la tabla 'investment_results'
    $investmentResultsData = [];

    foreach ($investments as $investment) {
        // Simular un resultado para cada inversión
        $result = rand(-10000, 20000); // Resultados de inversión entre -10,000 y 20,000

        $investmentResultsData[] = [
            'investment_id' => $investment->id,  // Relacionamos con el id de la inversión
            'result' => $result,                  // Resultado de la inversión
            'date' => Carbon::now()->format('Y-m-d'), // Fecha del resultado (puedes modificarlo según tu lógica)
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    // Insertar los resultados de las inversiones en la tabla 'investment_results'
    if (!empty($investmentResultsData)) {
        DB::connection('mysql')->table('investment_results')->insert($investmentResultsData);
        $this->info('¡Resultados de inversiones migrados con éxito!');
    } else {
        $this->info('No se encontraron inversiones para migrar a investment_results.');
    }
}
private function migrateFinancialAdvices(): void
{
    $this->info('Migrando consejos financieros...');

    // Obtener todos los usuarios
    $users = DB::connection('mysql')->table('users')->pluck('id');

    $adviceTypes = ['inversión', 'ahorro', 'presupuesto', 'riesgo', 'diversificación'];
    $advices = [
        'Diversificar las inversiones para minimizar el riesgo.',
        'Establecer un presupuesto mensual para el ahorro.',
        'Invertir en acciones con un análisis profundo de mercado.',
        'Mantener un fondo de emergencia para imprevistos.',
        'Evitar el gasto impulsivo y priorizar el ahorro.',
        'Revisar tus finanzas periódicamente para ajustar tus metas.'
    ];

    $financialAdvicesData = [];

    // Generar 500 registros
    for ($i = 0; $i < 500; $i++) {
        $userId = $users->random();
        $adviceType = $adviceTypes[array_rand($adviceTypes)];
        $advice = $advices[array_rand($advices)];

        $financialAdvicesData[] = [
            'user_id' => $userId,
            'advice' => $advice,
            'advice_type' => $adviceType,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    // Inserta los datos en la tabla 'financial_advices'
    if (!empty($financialAdvicesData)) {
        DB::connection('mysql')->table('financial_advices')->insert($financialAdvicesData);
        $this->info('¡Consejos financieros migrados con éxito!');
    } else {
        $this->info('No se encontraron consejos financieros para migrar.');
    }
}

}
