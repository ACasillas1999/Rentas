<?php

namespace Tests\Feature;

use App\Http\Controllers\MonthlyReportController;
use App\Mail\MonthlyReportMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonthlyReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_obtener_matriz_mensual_returns_valid_structure(): void
    {
        $matrixData = MonthlyReportController::obtenerMatrizMensual(10, 2026);

        $this->assertIsArray($matrixData);
        $this->assertArrayHasKey('rows', $matrixData);
        $this->assertArrayHasKey('totals', $matrixData);
        $this->assertArrayHasKey('selectedPeriod', $matrixData);
        $this->assertArrayHasKey('rent_subtotal', $matrixData['totals']);
        $this->assertArrayHasKey('rent_tax', $matrixData['totals']);
        $this->assertArrayHasKey('rent_total', $matrixData['totals']);
        $this->assertArrayHasKey('maintenance_subtotal', $matrixData['totals']);
        $this->assertArrayHasKey('maintenance_tax', $matrixData['totals']);
        $this->assertArrayHasKey('maintenance_total', $matrixData['totals']);
        $this->assertArrayHasKey('grand_total', $matrixData['totals']);
    }

    public function test_monthly_report_mail_contains_matrix_table_and_attachment(): void
    {
        \App\Models\Unit::factory()->create();

        $controller = new MonthlyReportController();
        $stats = $controller->calcularStats(10, 2026);
        $matrixData = MonthlyReportController::obtenerMatrizMensual(10, 2026);

        $mail = new MonthlyReportMail($stats, 'Octubre 2026', 10, 2026, $matrixData);

        // Verifica render del HTML del correo
        $rendered = $mail->render();
        $this->assertStringContainsString('Matriz de Pagos', $rendered);
        $this->assertStringContainsString('CONCEPTO DE RENTA', $rendered);
        $this->assertStringContainsString('CONCEPTO DE MANTENIMIENTO', $rendered);
        $this->assertStringContainsString('GRAN TOTAL', $rendered);

        // Verifica que se adjunte el Excel
        $attachments = $mail->attachments();
        $this->assertCount(1, $attachments);
    }

    public function test_admin_can_see_matrix_in_monthly_preview(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        \App\Models\Unit::factory()->create();

        $response = $this->actingAs($admin)->get(route('reports.monthly.index', [
            'month' => 10,
            'year'  => 2026,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Matriz de Pagos — Reporte Pormenorizado del Mes');
        $response->assertSee('CONCEPTO DE RENTA');
        $response->assertSee('CONCEPTO DE MANTENIMIENTO');
    }
}
