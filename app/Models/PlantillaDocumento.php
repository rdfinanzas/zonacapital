<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Helpers\ConfiguracionNotaHelper;

class PlantillaDocumento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'plantillas_documentos';
    protected $primaryKey = 'idPlantilla';

    protected $fillable = [
        'ModuloId',
        'nombre',
        'descripcion',
        'configuracion',
        'creado_por',
    ];

    protected $casts = [
        'configuracion' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con el módulo
     */
    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'ModuloId', 'IdModulo');
    }

    /**
     * Relación con el usuario creador
     */
    public function creador()
    {
        return $this->belongsTo(Usuario::class, 'creado_por', 'IdUsuario');
    }

    /**
     * Scope para filtrar por módulo
     */
    public function scopePorModulo($query, $moduloId)
    {
        return $query->where('ModuloId', $moduloId);
    }

    /**
     * Scope para búsqueda por nombre
     */
    public function scopeBuscar($query, string $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('nombre', 'LIKE', "%{$termino}%")
                ->orWhere('descripcion', 'LIKE', "%{$termino}%");
        });
    }

    /**
     * Obtener la configuración decodificada con valores por defecto
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
     * Crear plantilla desde una configuración dada
     */
    public static function crearDesdeConfiguracion(array $data): self
    {
        $configuracion = ConfiguracionNotaHelper::desdeRequest($data);

        return self::create([
            'ModuloId' => $data['modulo_id'] ?? null,
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'configuracion' => $configuracion,
            'creado_por' => $data['creado_por'] ?? session('usuario_id'),
        ]);
    }

    /**
     * Duplicar plantilla
     */
    public function duplicar(string $nuevoNombre): self
    {
        return self::create([
            'ModuloId' => $this->ModuloId,
            'nombre' => $nuevoNombre,
            'descripcion' => $this->descripcion,
            'configuracion' => $this->configuracion,
            'creado_por' => session('usuario_id'),
        ]);
    }
}
