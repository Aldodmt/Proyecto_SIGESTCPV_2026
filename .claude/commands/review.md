---
description: Revisión de código de los cambios actuales según las reglas de Sysweb (seguridad, RBAC, convenciones)
argument-hint: "[archivo, carpeta o rango git; por defecto los cambios sin commitear]"
---

Revisa: $ARGUMENTS (si está vacío, `git diff` + archivos nuevos sin seguimiento).

Usa el subagente `sysweb-reviewer` para la revisión y después:
1. Muestra los hallazgos ordenados por gravedad (bloqueante → importante → sugerencia), cada uno con
   `archivo:línea`, el problema concreto y la corrección propuesta.
2. Separa lo que viene del diff de lo que ya existía en código heredado (lo segundo, solo si es grave).
3. No apliques cambios hasta que te lo confirme.
