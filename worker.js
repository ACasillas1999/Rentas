const { exec } = require('child_process');

console.log("-----------------------------------------");
console.log("🚀 Iniciando despachador de tareas Laravel");
console.log("-----------------------------------------");

// Ejecuta el comando 'php artisan schedule:run' cada 60 segundos
setInterval(() => {
    const now = new Date().toLocaleTimeString();
    exec('php artisan schedule:run', (err, stdout, stderr) => {
        if (err) {
            console.error(`[${now}] ❌ Error: ${err.message}`);
            return;
        }
        if (stderr) {
            console.warn(`[${now}] ⚠️ Advertencia: ${stderr}`);
        }
        console.log(`[${now}] ✅ Resultado: ${stdout || 'Nada que procesar.'}`);
    });
}, 60000);
