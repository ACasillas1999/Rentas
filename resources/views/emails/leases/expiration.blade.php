<x-mail::message>
# Notificación de Vigencia de Contrato

Se le informa cordialmente que el contrato de arrendamiento detallado a continuación está próximo a su fecha de término.

<x-mail::panel>
### Detalles del Arrendamiento:
| Concepto | Información |
| :--- | :--- |
| **Folio de Contrato** | #{{ $lease->contract_number }} |
| **Arrendatario** | {{ $lease->tenant->full_name }} |
| **Unidad / Local** | {{ $lease->unit->code }} |
| **Propiedad** | {{ $lease->unit->property->name ?? 'N/A' }} |
| **Vencimiento** | {{ $lease->end_date->format('d/m/Y') }} |
</x-mail::panel>

### Estatus de la Vigencia:
@if($daysRemaining == 0)
El contrato **ha cumplido su término el día de hoy.**
@else
Faltan **{{ $daysRemaining }} días** para el término de la vigencia.
@endif

Le sugerimos tomar las previsiones necesarias para el proceso de renovación o la entrega formal del inmueble.

<x-mail::button :url="config('app.url')">
Ir al Sistema
</x-mail::button>

Atentamente,  
**Grupo Ascencio — Arrendamientos**
</x-mail::message>
