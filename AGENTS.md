# AGENTS - Pro Roller Inventory

## Objetivo

Este archivo define reglas para agentes que implementan cambios en este proyecto Laravel.
La meta es mantener consistencia tecnica y funcional en nuevas features, fixes y refactors.

## Alcance del sistema

- Dominio principal: inventario y ventas.
- Modulos criticos: productos, ventas, movimientos, conteos fisicos, informes, clientes, proveedores, usuarios.
- Prioridad funcional: integridad de stock, trazabilidad de movimientos y UX operativa clara.

## Convenciones backend (Laravel)

- Mantener controladores delgados. Si una accion crece en complejidad, extraer logica a servicios/metodos dedicados.
- Usar `FormRequest` para validaciones no triviales o reutilizables.
- Mantener convencion REST en `Route::resource` y usar rutas explicitas para acciones de negocio especiales.
- Reusar nombres de rutas existentes cuando se extiende una feature relacionada.
- Evitar N+1 queries. Cargar relaciones con `with()` cuando la vista lo requiera.

## Reglas criticas de inventario

- Toda operacion que altere stock o cree movimientos debe ejecutarse dentro de transaccion (`DB::transaction`).
- Cada cambio de stock debe dejar trazabilidad en `MovimientoInventario` con tipo, cantidad, motivo, usuario y fecha.
- Evitar borrado fisico de entidades historicas sensibles (por ejemplo productos con historial); preferir desactivar/reactivar.
- No introducir flujos que permitan stock negativo salvo regla explicita y aprobada.
- Validar conflictos de codigos (principal y variantes) antes de persistir.

## Seguridad y permisos

- Mantener middleware `auth` en rutas privadas y controladores.
- Aplicar politicas/roles en acciones administrativas o sensibles.
- No exponer acciones destructivas sin confirmacion y sin autorizacion.
- Nunca hardcodear secretos, tokens o credenciales en codigo.

## Convenciones de UI (Blade + Bootstrap)

- Mantener textos y mensajes al usuario en espanol claro.
- Reusar patrones visuales existentes: cards, tablas, badges, botones y alertas Bootstrap.
- En formularios: mostrar validaciones de forma explicita y preservar `old()` cuando aplique.
- En acciones sensibles: incluir confirmacion y feedback posterior (success/warning/error).
- Mantener coherencia en titulos, labels y acciones entre index/create/edit/show.

## Calidad y validacion de cambios

- Para cambios PHP, ejecutar formato con `./vendor/bin/pint` (o equivalente del entorno).
- Para logica de negocio sensible, agregar o actualizar tests (especialmente stock, ventas, cancelaciones, ajustes y permisos).
- Si no hay tests automaticos de la parte tocada, documentar pasos manuales de verificacion funcional.
- Evitar cambios destructivos en schema/data sin plan de migracion seguro.

## Checklist antes de cerrar una tarea

- Se respeta integridad de inventario y trazabilidad de movimientos.
- Validaciones cubren entradas invalidas y casos limite.
- Permisos/middleware/politicas quedan correctos para la feature.
- Vistas y mensajes son consistentes con el resto del sistema.
- Se verifico impacto en reportes e informes relacionados.

## Fuera de alcance por defecto

- Cambios masivos de arquitectura sin requerimiento explicito.
- Renombrados globales de rutas/simbolos sin necesidad funcional.
- Migraciones irreversibles o eliminacion de historico de negocio.
