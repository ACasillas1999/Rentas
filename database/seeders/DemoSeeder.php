<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\Unit;
use App\Models\Tenant;
use App\Models\Lease;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Propiedades
        $properties = [
            ['name' => 'Plaza Fundadores', 'address' => 'Av. Central 100, Centro', 'notes' => 'Plaza comercial de lujo.'],
            ['name' => 'Edificio Mirador', 'address' => 'Calle 5 de Mayo 450', 'notes' => 'Edificio de oficinas corporativas.'],
            ['name' => 'Condominios del Parque', 'address' => 'Blvd. Reforma 1020', 'notes' => 'Departamentos habitacionales.'],
            ['name' => 'Bodegas Las Torres', 'address' => 'Zona Industrial Sur km 5', 'notes' => 'Complejo de bodegas industriales.'],
        ];

        $propertyModels = [];
        foreach ($properties as $p) {
            $propertyModels[] = Property::create($p);
        }

        // 2. Crear Unidades
        $units = [];
        foreach ($propertyModels as $prop) {
            for ($i = 1; $i <= 5; $i++) {
                $code = strtoupper(substr($prop->name, 0, 2)) . "-" . str_pad($i, 2, '0', STR_PAD_LEFT);
                $units[] = Unit::create([
                    'property_id' => $prop->id,
                    'code' => $code,
                    'floor' => rand(1, 3),
                    'area_m2' => rand(30, 100),
                    'monthly_rent' => rand(5000, 15000),
                    'status' => 'available',
                    'notes' => "Unidad {$i} en {$prop->name}",
                ]);
            }
        }

        // 3. Crear Inquilinos
        $tenantNames = [
            'Juan Pérez', 'María García', 'Roberto Hernández', 'Ana Martínez', 
            'Carlos Rodríguez', 'Lucía López', 'Sofía González', 'Fernando Ruiz',
            'Gabriela Sánchez', 'Miguel Ángel Torres', 'Jimena Díaz', 'Aarón Silva',
            'Alejandra Méndez', 'Ricardo Luna', 'Héctor Vega'
        ];

        $tenantModels = [];
        foreach ($tenantNames as $name) {
            $tenantModels[] = Tenant::create([
                'full_name' => $name,
                'email' => strtolower(str_replace(' ', '.', $name)) . '@ejemplo.com',
                'phone' => '555-' . rand(1000, 9999),
                'document_id' => 'CURP-' . rand(100000, 999999),
                'address' => 'Dirección conocida en la ciudad',
            ]);
        }

        // 4. Crear Contratos (85% de ocupación = ~17 unidades)
        $today = Carbon::today();
        $numLeases = 17;
        shuffle($units);
        shuffle($tenantModels);

        for ($i = 0; $i < $numLeases; $i++) {
            $unit = $units[$i];
            $tenant = $tenantModels[$i % count($tenantModels)];
            
            // Algunos inician hace meses, otros hace poco
            $monthsToSubtract = rand(1, 12);
            $startDate = $today->copy()->subMonths($monthsToSubtract)->startOfMonth()->setDay(rand(1, 5));
            $endDate = $startDate->copy()->addYear();

            $lease = Lease::create([
                'unit_id' => $unit->id,
                'tenant_id' => $tenant->id,
                'contract_number' => 'CONT-' . (1000 + $i),
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'monthly_amount' => rand(5000, 15000),
                'deposit_amount' => rand(3000, 10000),
                'due_day' => 5,
                'status' => 'active',
            ]);

            // Actualizar status de unidad
            $unit->update(['status' => 'rented']);

            // 5. Generar Pagos para cada contrato
            $this->generatePaymentsForLease($lease, $startDate, $endDate, $today);
        }
    }

    private function generatePaymentsForLease($lease, $startDate, $endDate, $today)
    {
        $cursor = $startDate->copy()->startOfMonth();
        $limitDate = $today->copy()->addMonths(3); // Generar hasta 3 meses a futuro

        while ($cursor->lte($endDate) && $cursor->lte($limitDate)) {
            $dueDate = $cursor->copy()->day($lease->due_day);
            if ($dueDate->lt($startDate)) $dueDate = $startDate->copy();

            $status = 'pending';
            $paidAt = null;
            $paidAmount = 0;
            $lateFee = 0;

            if ($dueDate->lt($today)) {
                // Pagos del pasado: mayoría pagados, algunos tarde, algunos parciales
                $chance = rand(1, 10);
                if ($chance <= 7) {
                    $status = 'paid';
                    $paidAt = $dueDate->copy()->addDays(rand(0, 5))->toDateString();
                    $paidAmount = $lease->monthly_amount;
                } elseif ($chance <= 9) {
                    $status = 'partial';
                    $paidAt = $dueDate->copy()->addDays(rand(0, 2))->toDateString();
                    $paidAmount = round($lease->monthly_amount * 0.5);
                } else {
                    $status = 'overdue';
                    $lateFee = 100;
                }
            } elseif ($dueDate->isSameMonth($today)) {
                // Mes actual: depende de la fecha
                if ($dueDate->lt($today)) {
                   $status = rand(1, 2) == 1 ? 'paid' : 'overdue';
                   if ($status == 'paid') {
                       $paidAt = $dueDate->toDateString();
                       $paidAmount = $lease->monthly_amount;
                   }
                } else {
                    $status = 'pending';
                }
            }

            Payment::create([
                'lease_id' => $lease->id,
                'period_label' => $cursor->locale('es')->isoFormat('MMMM YYYY'),
                'due_date' => $dueDate->toDateString(),
                'paid_at' => $paidAt,
                'amount' => $lease->monthly_amount,
                'paid_amount' => $paidAmount,
                'late_fee' => $lateFee,
                'status' => $status,
                'payment_method' => $paidAt ? 'Transferencia' : null,
            ]);

            $cursor->addMonth();
        }
    }
}
