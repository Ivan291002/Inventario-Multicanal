# 📦 Sistema de Inventario Multicanal: Resumen del Trabajo Práctico

Este documento recopila la arquitectura del proyecto, el paso a paso realizado, y una **bitácora detallada de los errores encontrados y sus soluciones** para consolidar el aprendizaje.

---

## 🎯 Objetivo del Proyecto

Construir un backend moderno en **Laravel** capaz de ofrecer múltiples interfaces de comunicación para un sistema de inventario:
1. **REST API**: Acceso CRUD estándar vía JSON.
2. **GraphQL**: Consultas flexibles y mutaciones tipadas.
3. **WebSockets (Event-Driven)**: Notificaciones reactivas en tiempo real hacia el cliente web.
4. **gRPC**: Comunicación binaria de alto rendimiento entre servicios usando Protocol Buffers.

---

## 🏗️ 1. Arquitectura y Componentes Implementados

### A. Capa de Datos y Dominio (Laravel + Eloquent)
- **Modelo**: `app/Models/Producto.php` define los campos `nombre`, `precio` y `stock`.
- **Migración**: `database/migrations/*_create_productos_table.php` genera la tabla en la base de datos SQLite.
- **Seeder**: `database/seeders/ProductoSeeder.php` pobla el inventario inicial con productos tecnológicos (Mouse, Teclado, Monitor, etc.).
- **Servicio (`ProductoService.php`)**: Aplica el patrón *Service Layer* para separar la lógica de negocio y las validaciones de los controladores o mutadores.

### B. REST API
- **Controlador**: `app/Http/Controllers/Api/ProductoController.php`.
- **Rutas**: En `routes/api.php` mediante `Route::apiResource('productos', ProductoController::class)`.
- Métodos soportados: `GET` (listar y ver por ID), `POST` (crear), `PUT/PATCH` (actualizar), `DELETE` (eliminar).

### C. GraphQL (con Nuwave Lighthouse)
- **Esquema SDL**: `graphql/schema.graphql` define el tipo `Producto`, las queries (`productos`, `producto(id)`) y las mutaciones (`crearProducto`, `actualizarStock`).
- **Mutador**: `app/GraphQL/Mutations/ProductoMutator.php` conecta las mutaciones de GraphQL directamente con el `ProductoService`.

### D. WebSockets (Laravel Reverb + Echo)
- **Evento**: `app/Events/ProductoActualizado.php` implementa `ShouldBroadcast` y emite al canal público `inventario` con el alias `producto.actualizado`.
- **Backend**: Servidor Reverb (`php artisan reverb:start`) y cola de eventos (`php artisan queue:work`).
- **Frontend**: Vista Blade (`resources/views/inventario.blade.php`) + Vite y Laravel Echo (`resources/js/app.js`), que escucha los cambios en tiempo real y refresca el DOM sin recargar la página.

### E. gRPC (Node.js + Laravel)
- **Contrato Protobuf**: `grpc/producto.proto` define el servicio `ProductoService` y los mensajes binarios (`ProductoRequest`, `ProductoResponse`).
- **Endpoint interno protegido**: En `routes/api.php`, la ruta `/api/interno/productos/{producto}` exige la cabecera `X-Internal-Key` con una clave secreta.
- **Servidor gRPC**: `grpc/server.cjs` escucha en el puerto `50051`, recibe peticiones gRPC binarias, consulta a Laravel internamente y responde con Protobuf.
- **Cliente gRPC**: `grpc/client.cjs` solicita el producto al servidor y muestra la respuesta serializada.

---

## 🐞 2. Bitácora de Errores y Cómo se Resolvieron

A lo largo de la práctica surgieron problemas típicos del desarrollo real. A continuación se detallan sus causas y soluciones:

### Error 1: `DriveNotFoundException: No se encuentra la unidad 'http'` y `-d : El término '-d' no se reconoce`
- **Causa**: Al copiar comandos de Linux/Bash con barras invertidas (`\`) en PowerShell, el comando se cortó. Además, en PowerShell `curl` es un alias de `Invoke-WebRequest`, el cual no acepta los argumentos de curl estándar.
- **Solución**:
  - Usar el binario real de Windows: `curl.exe` (no solo `curl`).
  - O mejor aún, usar el comando nativo de PowerShell:
    ```powershell
    Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/productos"
    ```

---

### Error 2: `ParseError: syntax error, unexpected token "public"` en `ProductoController.php` y `ProductoService.php`
- **Causa**: Se cerraron llaves `{}` antes de tiempo al pegar el código, dejando código suelto fuera de los métodos o clases:
  - En `ProductoController.php`: el método `store` tenía las llaves vacías y el código afuera.
  - En `ProductoService.php`: la clase se cerraba en la línea 46, dejando `cambiarStock` y `eliminar` fuera de la clase.
- **Solución**: Reorganizar las llaves `{ ... }` para que todos los métodos pertenezcan a su clase respectiva.

---

### Error 3: `422 Unprocessable Content: The nombre field is required` en llamadas curl
- **Causa**: Al pasar JSON con comillas simples `'{"nombre": "Teclado"}'` en PowerShell, este no parsea bien las comillas internas y el servidor recibe cadenas vacías.
- **Solución**:
  - Escapar con comillas triples en PowerShell: `"{\"""nombre\""": \"""Teclado\"""}"`.
  - O usar `Invoke-RestMethod` con `-Body '{"nombre": "Teclado", ...}'`.

---

### Error 4: `SQLSTATE[HY000]: General error: 1 no such table: productos (500 Internal Server Error)`
- **Causa**: Existía el modelo `Producto.php`, pero no se había creado ni corrido la migración de la base de datos para la tabla `productos`.
- **Solución**:
  1. Crear la migración: `php artisan make:migration create_productos_table`.
  2. Definir las columnas: `id()`, `nombre`, `precio`, `stock`, `timestamps()`.
  3. Ejecutar: `php artisan migrate`.

---

### Error 5: `404 Not Found: No query results for model [App\Models\Producto] 1`
- **Causa**: Al consultar `/api/productos/1` o en el cliente gRPC `{ id: 1 }`, el producto con `id = 1` no existía porque la base de datos se pobló a partir del ID 2 en adelante.
- **Solución**: Realizar las pruebas utilizando un ID existente (por ejemplo, el ID `2` correspondiente al *Mouse Gamer*).

---

### Error 6: Postman `Syntax Error: Expected Name, found {` en GraphQL
- **Causa**: En la pestaña **GraphQL** de Postman se escribió `{ query: { productos { ... } } }`. La sintaxis `{ query: ... }` solo se utiliza en modo `raw (JSON)`.
- **Solución**: Escribir directamente la consulta limpia en GraphQL:
  ```graphql
  {
    productos {
      id
      nombre
      precio
      stock
    }
  }
  ```

---

### Error 7: Pantalla roja en Vite `Failed to resolve import "./bootstrap"` y texto `JavaScript`
- **Causa**: Al copiar del PDF de la materia se colaron los encabezados de bloque de código (`JavaScript`, `HTML`) dentro del archivo `app.js` e `inventario.blade.php`. Además, en Laravel moderno con Reverb se usa `import './echo'` en vez de `./bootstrap`.
- **Solución**: Limpiar `resources/js/app.js` dejando solo:
  ```javascript
  import './echo';

  window.Echo.channel('inventario')
      .listen('.producto.actualizado', (evento) => {
          window.dispatchEvent(new CustomEvent('inventario-cambio', { detail: evento }));
      });
  ```

---

### Error 8: `Error: illegal token '<' (producto.proto, line 15)`
- **Causa**: En el archivo de Protocol Buffers (`producto.proto`) se pegó código PHP (`<?php use App\Models\...`).
- **Solución**: Un archivo `.proto` solo debe tener definiciones Protobuf (`syntax`, `package`, `service`, `message`). El código PHP pertenece a `routes/api.php`.

---

### Error 9: `Error: 13 INTERNAL: Fallo del backend Laravel: 403` en el cliente gRPC
- **Causa**: El servidor gRPC no tenía definida la variable de entorno `GRPC_INTERNAL_KEY` al llamar al endpoint interno `/api/interno/productos/{id}`, por lo que Laravel rechazaba la petición por seguridad.
- **Solución**: Proveer la clave de fallback en `grpc/server.cjs`:
  ```javascript
  'X-Internal-Key': process.env.GRPC_INTERNAL_KEY || 'clave-local-larga-de-prueba'
  ```

---

## 📊 3. Cuadro Comparativo de Protocolos

| Característica | REST | GraphQL | WebSockets | gRPC |
| :--- | :--- | :--- | :--- | :--- |
| **Protocolo de red** | HTTP/1.1 o HTTP/2 | HTTP/1.1 o HTTP/2 | WebSocket (TCP duplex) | HTTP/2 |
| **Formato de datos** | JSON | JSON | JSON | Protocol Buffers (Binario) |
| **Modelo de comunicación**| Petición / Respuesta | Petición / Respuesta | Bidireccional / Eventos | RPC (Unario o Streaming) |
| **Overfetching / Underfetching**| Frecuente (payload fijo) | Eliminado (el cliente pide lo exacto) | Depende del evento | Eliminado por contrato estricto |
| **Caso de uso ideal** | APIs públicas y CRUD web estándar | Frontends con interfaces complejas | Chats, tableros y stock en vivo | Comunicación rápida entre microservicios |
