<?php

namespace App\Mail;

use App\Http\Controllers\MonthlyReportController;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class MonthlyReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public array  $stats;
    public string $periodLabel;
    public int    $month;
    public int    $year;
    public array  $matrixData;

    public function __construct(array $stats, string $periodLabel, int $month, int $year, ?array $matrixData = null)
    {
        $this->stats       = $stats;
        $this->periodLabel = $periodLabel;
        $this->month       = $month;
        $this->year        = $year;
        $this->matrixData  = $matrixData ?? MonthlyReportController::obtenerMatrizMensual($month, $year);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "📊 Resumen Mensual y Matriz de Cobranza — {$this->periodLabel}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reports.monthly',
            with: [
                'stats'       => $this->stats,
                'periodLabel' => $this->periodLabel,
                'month'       => $this->month,
                'year'        => $this->year,
                'matrixData'  => $this->matrixData,
                'appUrl'      => config('app.url'),
            ],
        );
    }

    public function attachments(): array
    {
        $excelHtml = view('emails.reports.monthly_matrix_excel', [
            'matrixData'  => $this->matrixData,
            'periodLabel' => $this->periodLabel,
            'year'        => $this->year,
            'month'       => $this->month,
        ])->render();

        $cleanPeriod = Str::slug($this->periodLabel, '_');
        $fileName    = "Matriz_Pormenorizada_{$cleanPeriod}.xls";

        return [
            Attachment::fromData(fn () => $excelHtml, $fileName)
                ->withMime('application/vnd.ms-excel'),
        ];
    }
}
