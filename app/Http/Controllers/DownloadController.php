<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;

class DownloadController extends Controller
{
    public function download(Request $request)
    {
        if (!$request->has('file')) {
            abort(400, 'Archivo no especificado.');
        }

        try {
            $path = Crypt::decryptString($request->query('file'));

            // Si el path viene serializado de PHP (formato s:XX:"..."), lo limpiamos
            if (is_string($path) && (str_starts_with($path, 's:') || str_contains($path, '";'))) {
                try {
                    $decoded = @unserialize($path);
                    if ($decoded !== false) {
                        $path = $decoded;
                    }
                } catch (\Exception $e) { }
            }
        } catch (\Exception $e) {
            abort(403, 'Enlace no válido o corrupto.');
        }

        // 1. Buscar en disco 'local' (storage/app/private)
        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->download($path);
        }

        // 2. Buscar en disco 'public' (storage/app/public)
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->download($path);
        }

        // 3. Buscar en el storage raíz por si acaso
        if (Storage::exists($path)) {
            return Storage::download($path);
        }

        abort(404, "El archivo no se encuentra físicamente en el servidor. (Ruta: $path)");
    }
}
