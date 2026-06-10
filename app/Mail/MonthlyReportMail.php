<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MonthlyReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public array  $stats;
    public string $periodLabel;
    public int    $month;
    public int    $year;

    public function __construct(array $stats, string $periodLabel, int $month, int $year)
    {
        $this->stats       = $stats;
        $this->periodLabel = $periodLabel;
        $this->month       = $month;
        $this->year        = $year;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "📊 Resumen Mensual de Cobranza — {$this->periodLabel}",
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
                'appUrl'      => config('app.url'),
            ],
        );
    }
}
