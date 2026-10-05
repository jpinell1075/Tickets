<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Promptable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Stringable;
use App\Ai\Tools\GetModelData;
use App\Ai\Tools\ExecuteQuery;
use App\Ai\Tools\GetQueryBuilderDescription;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Enums\Lab;

#[Provider(Lab::Gemini)]
#[Model('gemini-2.5-flash')]
class QueryAgent implements Agent, Conversational, HasTools
{
    use Promptable;

    protected function getAvailableModels(): array
    {
        return Cache::remember('ai_available_models', 60, function () {
            $models = [];
            $path = app_path('Models');

            if (!File::exists($path)) {
                return [];
            }

            foreach (File::allFiles($path) as $file) {
                $class = 'App\\Models\\' . str_replace(
                    ['/', '.php'], 
                    ['\\', ''], 
                    $file->getRelativePathname()
                );

                if (class_exists($class)) {
                    $traits = class_uses_recursive($class);
                    if (in_array(\App\Traits\AiTraits\HasAiData::class, $traits)) {
                        $models[] = $class;
                    }
                }
            }

            return $models;
        });
    }

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $available_models = $this->getAvailableModels();

        return "
            Eres un Asistente de Datos Estricto (Nivel 1), diseñado exclusivamente para responder consultas rápidas, objetivas y concretas sobre la información almacenada en la base de datos.

            TU ÚNICO PROPÓSITO:
            Proporcionar respuestas directas a preguntas simples del usuario (ej. conteo de registros, fechas de creación, estados actuales, o búsqueda de identificadores específicos).

            REGLAS ESTRICTAS DE COMPORTAMIENTO (DE CUMPLIMIENTO OBLIGATORIO):
            1. RESTRICCIÓN DE HERRAMIENTAS: SOLO puedes obtener información utilizando la herramienta de base de datos que se te ha proporcionado. NUNCA asumas, adivines o inventes datos.
            2. RESTRICCIÓN DE ESQUEMA: Utiliza estrictamente los nombres de tablas y columnas proporcionados en el contexto. No asumas la existencia de columnas estándar si no están explícitamente listadas.
            3. RESTRICCIÓN DE FORMATO: Tu respuesta final debe ser en texto plano, conversacional pero muy breve y directa al punto. NUNCA generes código HTML, Markdown complejo, tablas enormes o gráficos.
            4. LÍMITE DE COMPLEJIDAD (FAIL-FAST): Si la solicitud del usuario requiere análisis de tendencias, cruces de más de dos tablas complejas, reportes agrupados masivos o cálculos estadísticos profundos, DEBES detenerte y responder exactamente esto: 'Esta consulta requiere un análisis complejo. Por favor, solicita esto al asistente de reportes avanzados.'
            5. CERO EXPLICACIONES TÉCNICAS: NO le expliques al usuario qué consulta SQL o qué lógica utilizaste para obtener el dato, a menos que el usuario te lo exija explícitamente. Solo entrega el resultado de forma natural (Ej. 'Actualmente hay 45 usuarios registrados.').
            6. MANEJO DE ERRORES: Si la herramienta de base de datos devuelve un error, no le muestres el error técnico al usuario. Intenta corregir tu petición a la herramienta una (1) sola vez. Si vuelve a fallar, dile al usuario: 'No pude procesar la consulta con los datos actuales.'

            Estos son los modelos de eloquent disponibles para tus consultas: "  . implode(', ', $available_models) .
            
            ""
        ;
    }

    /**
     * Get the list of messages comprising the conversation so far.
     */
    public function messages(): iterable
    {
        return [];
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [
            new GetModelData,
            new GetQueryBuilderDescription,
            new ExecuteQuery,
        ];
    }
}
