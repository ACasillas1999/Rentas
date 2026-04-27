<?php

namespace App\Console\Commands;

use App\Mail\ContractExpirationNotice;
use App\Models\LeaseNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckExpiringLeases extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'leases:notify';

    /**
     * The console command description.
     */
    protected $description = 'Revisa contratos próximos a vencer y envía notificaciones por correo.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        $notifications = LeaseNotification::whereHas('lease', function ($query) {
            $query->where('status', 'active');
        })->get();

        $this->info("Revisando {$notifications->count()} reglas de notificación...");

        foreach ($notifications as $notif) {
            $lease = $notif->lease;
            $endDate = Carbon::parse($lease->end_date);
            $daysRemaining = $today->diffInDays($endDate, false);

            // Caso 30 días
            if ($daysRemaining == 30 && $notif->notify_30_days && !$notif->sent_30_days_at) {
                $this->sendNotification($notif, 30);
            }

            // Caso 15 días
            if ($daysRemaining == 15 && $notif->notify_15_days && !$notif->sent_15_days_at) {
                $this->sendNotification($notif, 15);
            }

            // Caso Día Final
            if ($daysRemaining == 0 && $notif->notify_end_date && !$notif->sent_end_date_at) {
                $this->sendNotification($notif, 0);
            }
        }

        $this->info('Proceso de notificaciones finalizado.');
    }

    private function sendNotification(LeaseNotification $notif, int $daysRemaining)
    {
        try {
            Mail::to($notif->email)->send(new ContractExpirationNotice($notif->lease, $daysRemaining));
            
            // Marcar como enviado
            $column = match($daysRemaining) {
                30 => 'sent_30_days_at',
                15 => 'sent_15_days_at',
                0  => 'sent_end_date_at',
            };

            $notif->update([$column => now()]);
            
            $this->info("Notificación de {$daysRemaining} días enviada a: {$notif->email}");
        } catch (\Exception $e) {
            $this->error("Error enviando a {$notif->email}: " . $e->getMessage());
        }
    }
}
