<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Helpers\ConfiguracionNotaHelper;

class NotaJuridica extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'notas_juridicas';
    protected $primaryKey = 'idNotaJuridica';

    protected $fillable = [
        'numero',
        'anio',
        'titulo',
        'descripcion',
        'observacion',
        'fecha_creacion',
        'personal_id',
        'nota_referencia_id',
        'archivo_path',
        'logo_path',
        'leyenda_encabezado',
        'google_drive_file_id',
        'google_drive_link',
        'tipo',
        'estado',
        'es_plantilla',
        'nombre_plantilla',
        'creado_por',
        'configuracion',      // Nuevo campo JSON
        'plantilla_id',       // Referencia a plantilla usada
    ];

    protected $casts = [
        'fecha_creacion' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'es_plantilla' => 'boolean',
        'configuracion' => 'array',
    ];

    /**
     * Relación con Personal
     */
    public function personal()
    {
        return $this->belongsTo(Personal::class, 'personal_id', 'idEmpleado');
    }

    /**
     * Relación con nota de referencia (nota anterior)
     */
    public function notaReferencia()
    {
        return $this->belongsTo(NotaJuridica::class, 'nota_referencia_id', 'idNotaJuridica');
    }

    /**
     * Relación con usuario creador
     */
    public function creador()
    {
        return $this->belongsTo(Usuario::class, 'creado_por', 'IdUsuario');
    }

    /**
     * Accessor para obtener el número completo (formato: numero/anio)
     */
    public function getNumeroCompletoAttribute(): string
    {
        return "{$this->numero}/{$this->anio}";
    }

    /**
     * Accessor para el estado con clase de badge
     */
    public function getEstadoBadgeClassAttribute(): string
    {
        return match($this->estado) {
            'borrador' => 'badge-secondary',
            'finalizada' => 'badge-success',
            'enviada' => 'badge-primary',
            default => 'badge-secondary'
        };
    }

    /**
     * Accessor para el tipo con etiqueta
     */
    public function getTipoLabelAttribute(): string
    {
        return match($this->tipo) {
            'creada' => 'Creada',
            'adjunta' => 'Adjunta',
            default => 'N/A'
        };
    }

    /**
     * Obtener el próximo número disponible para un año
     */
    public static function obtenerProximoNumero(int $anio): int
    {
        $ultima = self::where('anio', $anio)
            ->withTrashed()
            ->orderBy('numero', 'desc')
            ->first();

        return $ultima ? $ultima->numero + 1 : 1;
    }

    /**
     * Scope para filtrar por año
     */
    public function scopePorAnio($query, int $anio)
    {
        return $query->where('anio', $anio);
    }

    /**
     * Scope para filtrar por estado
     */
    public function scopePorEstado($query, string $estado)
    {
        return $query->where('estado', $estado);
    }

    /**
     * Scope para filtrar por tipo
     */
    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope para obtener solo plantillas
     */
    public function scopePlantillas($query)
    {
        return $query->where('es_plantilla', true);
    }

    /**
     * Scope para obtener solo notas (no plantillas)
     */
    public function scopeNotas($query)
    {
        return $query->where('es_plantilla', false);
    }

    /**
     * Scope para búsqueda general
     */
    public function scopeBuscar($query, string $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('titulo', 'LIKE', "%{$termino}%")
              ->orWhere('descripcion', 'LIKE', "%{$termino}%")
              ->orWhere('observacion', 'LIKE', "%{$termino}%");
        });
    }

    /**
     * Verificar si tiene archivo adjunto
     */
    public function tieneArchivo(): bool
    {
        return !empty($this->archivo_path) || !empty($this->google_drive_file_id);
    }

    /**
     * Obtener URL del archivo (Drive o local)
     */
    public function getUrlArchivoAttribute(): ?string
    {
        if ($this->google_drive_link) {
            return $this->google_drive_link;
        }

        if ($this->archivo_path) {
            return asset($this->archivo_path);
        }

        return null;
    }

    /**
     * Relación con plantilla usada
     */
    public function plantilla()
    {
        return $this->belongsTo(PlantillaDocumento::class, 'plantilla_id', 'idPlantilla');
    }

    /**
     * Obtener la configuración completa (con valores por defecto)
     */
    public function getConfiguracionCompletaAttribute(): array
    {
        return ConfiguracionNotaHelper::decodificar(
            is_array($this->configuracion) ? json_encode($this->configuracion) : $this->configuracion
        );
    }

    /**
     * Obtener configuración preparada para PDF
     */
    public function getConfiguracionPdfAttribute(): object
    {
        return ConfiguracionNotaHelper::prepararParaPdf($this->configuracion_completa);
    }

    /**
     * Obtener configuración preparada para vista previa
     */
    public function getConfiguracionPreviewAttribute(): object
    {
        return ConfiguracionNotaHelper::prepararParaPreview($this->configuracion_completa);
    }

    /**
     * Guardar configuración desde array
     */
    public function setConfiguracionFromArray(array $config): void
    {
        $this->configuracion = $config;
    }

    /**
     * Crear nota desde plantilla
     */
    public static function crearDesdePlantilla(PlantillaDocumento $plantilla, array $datosAdicionales = []): self
    {
        $nota = new self();
        $nota->configuracion = $plantilla->configuracion;
        $nota->plantilla_id = $plantilla->idPlantilla;
        $nota->titulo = $datosAdicionales['titulo'] ?? $plantilla->nombre;
        $nota->tipo = $datosAdicionales['tipo'] ?? 'creada';
        $nota->estado = $datosAdicionales['estado'] ?? 'borrador';
        $nota->fecha_creacion = $datosAdicionales['fecha_creacion'] ?? now();
        $nota->personal_id = $datosAdicionales['personal_id'] ?? null;
        $nota->observacion = $datosAdicionales['observacion'] ?? null;
        $nota->nota_referencia_id = $datosAdicionales['nota_referencia_id'] ?? null;
        $nota->creado_por = $datosAdicionales['creado_por'] ?? session('usuario_id');

        return $nota;
    }
}
