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

    /**
     * ESTADOS DISPONIBLES - IDs numéricos
     * Modificar solo aquí para agregar/quitar/cambiar estados
     * Formato: ID => ['texto' => 'NOMBRE', 'badge' => 'clase-bootstrap']
     */
    public const ESTADOS = [
        1 => ['texto' => 'PENDIENTE', 'badge' => 'bg-warning'],
        2 => ['texto' => 'CEDULA ENVIADA', 'badge' => 'bg-info'],
        3 => ['texto' => 'CON DESCARGO', 'badge' => 'bg-success'],
        4 => ['texto' => 'SIN DESCARGO', 'badge' => 'bg-danger'],
        5 => ['texto' => 'DICTAMEN', 'badge' => 'bg-primary'],
        6 => ['texto' => 'DISPOSICION', 'badge' => 'bg-secondary'],
        7 => ['texto' => 'ELEVACION MSP', 'badge' => 'bg-dark'],
        8 => ['texto' => 'ARCHIVO', 'badge' => 'bg-light text-dark'],
    ];

    /**
     * Validación de estados para request (usar IDs numéricos)
     */
    public static function getEstadosValidacion(): string
    {
        return 'in:' . implode(',', array_keys(self::ESTADOS));
    }

    /**
     * Obtener texto del estado
     */
    public function getEstadoTextoAttribute(): string
    {
        return self::ESTADOS[$this->estado]['texto'] ?? 'DESCONOCIDO';
    }

    /**
     * Obtener clase de badge según estado
     */
    public function getEstadoBadgeClass(): string
    {
        return self::ESTADOS[$this->estado]['badge'] ?? 'bg-secondary';
    }

    /**
     * Accesor para devolver el estado con su texto en JSON
     */
    public function getEstadoConTextoAttribute(): array
    {
        return [
            'id' => $this->estado,
            'texto' => $this->estado_texto,
            'badge' => $this->getEstadoBadgeClass()
        ];
    }

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
        'google_doc_id',
        'google_doc_link',
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
     * Relación con historial de novedades
     */
    public function historial()
    {
        return $this->hasMany(NotaJuridicaHistorial::class, 'nota_juridica_id', 'idNotaJuridica')->orderBy('created_at', 'desc');
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
     * Accessor para el tipo con etiqueta (ahora puede ser combinado)
     */
    public function getTipoLabelAttribute(): string
    {
        $tieneGoogleDoc = !empty($this->google_doc_id) || !empty($this->google_doc_link);
        $tieneArchivo = !empty($this->archivo_path) || !empty($this->google_drive_file_id);

        if ($tieneGoogleDoc && $tieneArchivo) {
            return 'Completa';
        } elseif ($tieneGoogleDoc) {
            return 'Creada';
        } elseif ($tieneArchivo) {
            return 'Adjunta';
        }
        return 'N/A';
    }

    /**
     * Verificar si tiene Google Doc
     */
    public function tieneGoogleDoc(): bool
    {
        return !empty($this->google_doc_id) || !empty($this->google_doc_link);
    }

    /**
     * Verificar si tiene archivo adjunto
     */
    public function tieneArchivoAdjunto(): bool
    {
        return !empty($this->archivo_path) || !empty($this->google_drive_file_id);
    }

    /**
     * Obtener iconos según lo que tenga la nota
     */
    public function getIconosAttribute(): string
    {
        $iconos = [];
        if ($this->tieneGoogleDoc()) {
            $iconos[] = '<i class="fas fa-edit text-primary" title="Con Google Doc"></i>';
        }
        if ($this->tieneArchivoAdjunto()) {
            $iconos[] = '<i class="fas fa-paperclip text-success" title="Con archivo adjunto"></i>';
        }
        return implode(' ', $iconos);
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
     * Scope para filtrar por estado (ID numérico)
     */
    public function scopePorEstado($query, int $estado)
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
