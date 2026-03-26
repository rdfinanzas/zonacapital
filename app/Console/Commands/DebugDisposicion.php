<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Disposicion;
use Illuminate\Support\Facades\DB;

class DebugDisposicion extends Command
{
    protected $signature = 'disposicion:debug {anio=2025}';
    protected $description = 'Debug del sistema de numeración de disposiciones';

    public function handle()
    {
        $anio = $this->argument('anio');

        $this->info("=== DEBUG DISPOSICIONES PARA AÑO {$anio} ===");

        // Obtener el máximo número (método anterior - alfabético)
        $maxNumeroAlfabetico = Disposicion::where('AnioDisp', $anio)
            ->whereNull('FechaEliminacion')
            ->max('NumDisp');

        // Obtener el máximo número (método corregido - numérico)
        $maxNumeroNumerico = Disposicion::where('AnioDisp', $anio)
            ->whereNull('FechaEliminacion')
            ->orderByRaw('CAST(NumDisp AS UNSIGNED) DESC')
            ->value('NumDisp');

        $this->info("Máximo alfabético: " . ($maxNumeroAlfabetico ?: 'NULL'));
        $this->info("Máximo numérico: " . ($maxNumeroNumerico ?: 'NULL'));

        // Obtener próximo número
        $proximoNumero = Disposicion::getProximoNumero($anio);
        $this->info("Próximo número sugerido: {$proximoNumero}");

        // Total de disposiciones
        $total = Disposicion::activas()->where('AnioDisp', $anio)->count();
        $this->info("Total disposiciones activas: {$total}");

        // Mostrar los últimos 10 números
        $ultimas = Disposicion::activas()
            ->where('AnioDisp', $anio)
            ->orderBy('NumDisp', 'desc')
            ->limit(10)
            ->get(['NumDisp', 'Descripcion']);

        $this->info("\n=== ÚLTIMAS 10 DISPOSICIONES ===");
        foreach ($ultimas as $disp) {
            $this->line("Nº {$disp->NumDisp}: " . substr($disp->Descripcion, 0, 50) . "...");
        }

        // Buscar disposiciones con números altos (ordenamiento numérico)
        $this->info("\n=== TOP 5 NÚMEROS MÁS ALTOS (NUMÉRICO) ===");
        $altasNumericas = Disposicion::activas()
            ->where('AnioDisp', $anio)
            ->orderByRaw('CAST(NumDisp AS UNSIGNED) DESC')
            ->limit(5)
            ->get(['NumDisp', 'Descripcion']);

        foreach ($altasNumericas as $disp) {
            $this->line("Nº {$disp->NumDisp}: " . substr($disp->Descripcion, 0, 40) . "...");
        }

        // Buscar el registro específico 15282
        $this->info("\n=== INVESTIGANDO NÚMERO 15282 ===");
        $registro15282 = Disposicion::activas()
            ->where('AnioDisp', $anio)
            ->where('NumDisp', '15282')
            ->first(['NumDisp', 'Descripcion', 'FechaCreacion']);

        if ($registro15282) {
            $this->error("ENCONTRADO: Nº {$registro15282->NumDisp}");
            $this->line("Descripción: " . $registro15282->Descripcion);
            $this->line("Fecha: " . $registro15282->FechaCreacion);
        } else {
            $this->info("No se encontró el registro 15282");
        }

        $this->info("\n=== RESUMEN ===");
        $proximoCorregido = Disposicion::getProximoNumero($anio);
        if ($proximoCorregido <= 10000) {
            $this->info("✅ Función corregida correctamente");
            $this->info("✅ Próximo número sugerido: {$proximoCorregido}");
        } else {
            $this->error("⚠️  Aún hay problemas con la numeración");
        }

        return 0;
    }
}
