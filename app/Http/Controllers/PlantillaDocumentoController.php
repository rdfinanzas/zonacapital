<?php

namespace App\Http\Controllers;

use App\Models\PlantillaDocumento;
use App\Models\Modulo;
use App\Helpers\ConfiguracionNotaHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlantillaDocumentoController extends Controller
{
    /**
     * Mostrar el gestor de plantillas
     */
    public function index()
    {
        $modulos = Modulo::where('Padre', 0)
            ->with('hijos')
            ->orderBy('Orden')
            ->get();

        return view('gestor-plantillas', compact('modulos'));
    }

    /**
     * Obtener plantillas paginadas y filtradas
     */
    public function filtrar(Request $request)
    {
        try {
            $query = PlantillaDocumento::with(['modulo', 'creador']);

            // Filtro por módulo
            if ($request->filled('modulo_id')) {
                $query->porModulo($request->modulo_id);
            }

            // Búsqueda general
            if ($request->filled('busqueda')) {
                $query->buscar($request->busqueda);
            }

            // Ordenamiento
            $query->orderBy('created_at', 'desc');

            // Paginación
            $perPage = $request->get('per_page', 15);
            $plantillas = $query->paginate($perPage);

            // Agregar configuración decodificada a cada plantilla
            $plantillas->getCollection()->transform(function ($plantilla) {
                $plantilla->configuracion_decodificada = $plantilla->configuracion_completa;
                return $plantilla;
            });

            return response()->json($plantillas);

        } catch (\Exception $e) {
            Log::error('Error al filtrar plantillas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las plantillas'
            ], 500);
        }
    }

    /**
     * Obtener plantillas por módulo (para selector en otros módulos)
     */
    public function porModulo(Request $request)
    {
        try {
            $moduloId = $request->get('modulo_id');
            $moduloUrl = $request->get('modulo_url');

            // Buscar módulo por ID o URL
            if ($moduloId) {
                $modulo = Modulo::find($moduloId);
            } elseif ($moduloUrl) {
                $modulo = Modulo::where('Url', $moduloUrl)->first();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Se requiere modulo_id o modulo_url'
                ], 400);
            }

            if (!$modulo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Módulo no encontrado'
                ], 404);
            }

            $plantillas = PlantillaDocumento::porModulo($modulo->IdModulo)
                ->orderBy('nombre')
                ->get(['idPlantilla', 'nombre', 'descripcion']);

            return response()->json([
                'success' => true,
                'modulo_id' => $modulo->IdModulo,
                'data' => $plantillas
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener plantillas por módulo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las plantillas'
            ], 500);
        }
    }

    /**
     * Obtener una plantilla específica
     */
    public function show($id)
    {
        try {
            $plantilla = PlantillaDocumento::with(['modulo', 'creador'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $plantilla,
                'configuracion' => $plantilla->configuracion_completa
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener plantilla: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Plantilla no encontrada'
            ], 404);
        }
    }

    /**
     * Crear nueva plantilla
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string|max:500',
                'modulo_id' => 'nullable|integer|exists:modulos,IdModulo',
                'configuracion' => 'nullable|array',
            ]);

            // Si viene configuración directa, usarla; si no, construirla desde campos individuales
            if (isset($validated['configuracion'])) {
                $configuracion = $validated['configuracion'];
            } else {
                $configuracion = ConfiguracionNotaHelper::desdeRequest($request->all());
            }

            // Validar configuración
            $errores = ConfiguracionNotaHelper::validar($configuracion);
            if (!empty($errores)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $errores
                ], 422);
            }

            $plantilla = PlantillaDocumento::create([
                'ModuloId' => $validated['modulo_id'] ?? null,
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'] ?? null,
                'configuracion' => $configuracion,
                'creado_por' => session('usuario_id') ?? auth()->id() ?? 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Plantilla creada exitosamente',
                'data' => $plantilla
            ]);

        } catch (\Exception $e) {
            Log::error('Error al crear plantilla: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la plantilla: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar plantilla existente
     */
    public function update(Request $request, $id)
    {
        try {
            $plantilla = PlantillaDocumento::findOrFail($id);

            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string|max:500',
                'modulo_id' => 'nullable|integer|exists:modulos,IdModulo',
                'configuracion' => 'nullable|array',
            ]);

            // Si viene configuración directa, usarla
            if (isset($validated['configuracion'])) {
                $configuracion = $validated['configuracion'];
            } else {
                $configuracion = ConfiguracionNotaHelper::desdeRequest($request->all());
            }

            // Validar configuración
            $errores = ConfiguracionNotaHelper::validar($configuracion);
            if (!empty($errores)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $errores
                ], 422);
            }

            $plantilla->update([
                'ModuloId' => $validated['modulo_id'] ?? $plantilla->ModuloId,
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'] ?? null,
                'configuracion' => $configuracion,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Plantilla actualizada exitosamente',
                'data' => $plantilla
            ]);

        } catch (\Exception $e) {
            Log::error('Error al actualizar plantilla: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la plantilla: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar plantilla
     */
    public function destroy($id)
    {
        try {
            $plantilla = PlantillaDocumento::findOrFail($id);
            $plantilla->delete();

            return response()->json([
                'success' => true,
                'message' => 'Plantilla eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar plantilla: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la plantilla'
            ], 500);
        }
    }

    /**
     * Duplicar plantilla
     */
    public function duplicar($id)
    {
        try {
            $plantilla = PlantillaDocumento::findOrFail($id);
            $nueva = $plantilla->duplicar($plantilla->nombre . ' (copia)');

            return response()->json([
                'success' => true,
                'message' => 'Plantilla duplicada exitosamente',
                'data' => $nueva
            ]);

        } catch (\Exception $e) {
            Log::error('Error al duplicar plantilla: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al duplicar la plantilla'
            ], 500);
        }
    }

    /**
     * Obtener valores por defecto para nueva plantilla/nota
     */
    public function defaults()
    {
        return response()->json([
            'success' => true,
            'data' => ConfiguracionNotaHelper::defaults(),
            'tamanos_pagina' => ConfiguracionNotaHelper::tamanosPagina(),
            'orientaciones' => ConfiguracionNotaHelper::orientaciones(),
        ]);
    }
}
