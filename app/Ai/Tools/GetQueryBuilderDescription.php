<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetQueryBuilderDescription implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return '
        Obtiene una descripción detallada de como utilizar la herramienta ExecuteQuery
        Utiliza esta herramienta para entender la estructura exacta del JSON que debes generar para la herramienta ExecuteQuery.
        Solo usa esta herramienta una vez para no desperdiciar tokens, memoriza la información que te entregue y úsala como referencia cada vez que necesites generar un JSON para ExecuteQuery.
        ';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        return'
            *** ESPECIFICACIÓN DEL LENGUAJE DE CONSULTA INTERMEDIO (DSL) ***

            Eres un agente encargado de traducir las intenciones de búsqueda del usuario a un esquema JSON estricto. NUNCA debes escribir consultas SQL. Tu única salida para esta herramienta debe ser un objeto JSON válido que cumpla con las siguientes reglas de estructura.

            REGLAS GENERALES:
            - Solo utiliza modelos, columnas y relaciones que hayas descubierto previamente usando la herramienta de metadatos.
            - Todo atributo omitido por el usuario (como "limit" o "sort") puede ser omitido en tu JSON o usar los valores por defecto (limit: 100).
            - El JSON tiene nodos principales: "model" (string, requerido), "select" (array de strings), "filters" (array de objetos), "relations" (array de objetos), "aggregations" (objeto), "sort" (array de objetos), "limit" (entero), "offset" (entero).

            1. EL BLOQUE DE FILTROS ("filters")
            Es un array donde cada objeto representa una condición. Todo objeto de filtro DEBE incluir el atributo "boolean" ("and" o "or") para determinar cómo se encadena con el filtro anterior.

            Tipos de filtros permitidos ("type"):
            - "where": Requiere "column", "operator" (ej. "=", ">", "<", ">=", "<=", "LIKE") y "value".
            - "whereIn" / "whereNotIn": Requiere "column" y "value" (que DEBE ser un array de valores).
            - "whereNull" / "whereNotNull": Requiere solo "column". NO incluyas "operator" ni "value".
            - "whereBetween": Requiere "column" y "value" (que DEBE ser un array de exactamente dos elementos: [inicio, fin]).
            - "whereNested": Agrupa condiciones con paréntesis. NO usa "column". Requiere un atributo "filters" que es un array de objetos de filtro (recursivo). Úsalo para lógicas como (A OR B).
            - "whereHas" / "whereDoesntHave": Filtra basándose en la existencia de una relación. Requiere el string "relation" (el nombre de la relación de Eloquent) y un atributo "filters" (array de objetos de filtro a aplicar dentro de esa relación). Este nodo es RECURSIVO, puedes anidar un "whereHas" dentro del "filters" de otro "whereHas".

            2. EL BLOQUE DE RELACIONES ("relations")
            Se utiliza para cargar tablas anexas (Eager Loading). Es un array de objetos.
            Cada objeto requiere:
            - "name": String con el nombre exacto de la relación.
            - "select": Array de strings con las columnas específicas a traer (IMPORTANTE: siempre debes incluir la llave primaria y la llave foránea para que la relación funcione).
            - "filters": Array de objetos de filtro (misma estructura que el bloque 1). Aplica filtros SOLO a los registros traídos en esta relación.
            - "relations": Array de objetos (RECURSIVO). Permite cargar relaciones de la relación actual (Deep Eager Loading).

            3. EL BLOQUE DE AGREGACIONES ("aggregations")
            Úsalo SOLO cuando el usuario pida cálculos, reportes estadísticos o resúmenes totales (no registros individuales).
            Requiere:
            - "type": "count", "sum", "avg", "max", o "min".
            - "column": La columna sobre la cual hacer el cálculo (para "count" suele ser "id").
            - "group_by": (Opcional) String con el nombre de la columna para agrupar los resultados.

            4. EL BLOQUE DE ORDENAMIENTO ("sort")
            Array de objetos para permitir múltiple ordenamiento.
            Cada objeto requiere:
            - "column": String con el nombre de la columna.
            - "direction": String, estrictamente "asc" o "desc".

            RESTRICCIÓN CRÍTICA:
            No puedes inventar tipos de filtros ("type") que no estén listados en la sección 1. NUNCA intentes enviar código PHP crudo, Raw SQL o funciones de base de datos directamente en los valores. Todo debe ser datos limpios y tipados.

            Json de ejemplo:

            {
                "model": "User",
                "select": ["id", "name", "email", "country_code", "created_at"],
                "filters": [
                    {
                    "type": "whereIn",
                    "column": "country_code",
                    "value": ["MX", "ES"],
                    "boolean": "and"
                    },
                    {
                    "type": "whereBetween",
                    "column": "created_at",
                    "value": ["2026-01-01 00:00:00", "2026-12-31 23:59:59"],
                    "boolean": "and"
                    },
                    {
                    "type": "whereHas",
                    "relation": "orders",
                    "boolean": "and",
                    "filters": [
                        {
                        "type": "where",
                        "column": "status",
                        "operator": "=",
                        "value": "completed",
                        "boolean": "and"
                        },
                        {
                        "type": "whereHas",
                        "relation": "items",
                        "boolean": "and",
                        "filters": [
                            {
                            "type": "where",
                            "column": "price",
                            "operator": ">",
                            "value": 500,
                            "boolean": "and"
                            }
                        ]
                        }
                    ]
                    }
                ],
                "relations": [
                    {
                    "name": "profile",
                    "select": ["id", "user_id", "avatar_url"],
                    "filters": [],
                    "relations": []
                    },
                    {
                    "name": "orders",
                    "select": ["id", "user_id", "total", "status"],
                    "filters": [
                        {
                        "type": "where",
                        "column": "status",
                        "operator": "=",
                        "value": "completed",
                        "boolean": "and"
                        }
                    ],
                    "relations": [
                        {
                        "name": "items",
                        "select": ["id", "order_id", "product_name", "price"],
                        "filters": [],
                        "relations": []
                        }
                    ]
                    }
                ],
                "sort": [
                    {
                    "column": "created_at",
                    "direction": "desc"
                    }
                ],
                "limit": 50,
                "offset": 0
                }
        ';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
