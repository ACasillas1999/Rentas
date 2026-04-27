<?php

namespace App\Mail;

use App\Models\Lease;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContractExpirationNotice extends Mailable
{
    use Queueable, SerializesModels;

    public Lease $lease;
    public int $daysRemaining;

    /**
     * Create a new message instance.
     */
    public function __construct(Lease $lease, int $daysRemaining)
    {
        $this->lease = $lease;
        $this->daysRemaining = $daysRemaining;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = "Aviso de Vencimiento de Contrato - Folio #{$this->lease->contract_number}";
        
        if ($this->daysRemaining === 0) {
            $subject = "Notificación de Finalización de Vigencia - Contrato #{$this->lease->contract_number}";
        }

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.leases.expiration',
            with: [
                'lease' => $this->lease,
                'daysRemaining' => $this->daysRemaining,
            ],
        );
    }
}
