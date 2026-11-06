# 📦 Sistema de Inventario Multicanal

Sistema backend de gestión de inventario desarrollado en **Laravel 12** que implementa y compara cuatro paradigmas de comunicación y arquitecturas de API sobre un mismo modelo de datos:

1. **REST API**: CRUD estándar con serialización JSON sobre HTTP.
2. **GraphQL**: Consultas y mutaciones tipadas y sin sobrecarga de datos con Nuwave Lighthouse.
3. **WebSockets (Event-Driven)**: Notificaciones reactivas y sincronización de stock en tiempo real con Laravel Reverb y Laravel Echo.
4. **gRPC**: Comunicación binaria de alto rendimiento y bajo overhead mediante Protocol Buffers y un microservicio en Node.js.

El proyecto incluye una bitácora detallada de resolución de errores en [RESUMEN_TRABAJO_PRACTICO.md](RESUMEN_TRABAJO_PRACTICO.md).

---

## 🏛️ Arquitectura del Sistema

```
                      ┌────────────────────────────────────────┐
                      │          Clientes / Consumidores       │
                      └───────┬─────────┬─────────┬────────────┘
                              │         │         │
                   REST (JSON)│  GraphQL│         │ WebSockets
                              │         │         │ (Reverb)
                              ▼         ▼         ▼
                      ┌────────────────────────────────────────┐
                      │             Laravel 12                 │
                      │  - Controllers (Api/ProductoController)│
                      │  - Lighthouse Schema & Mutators        │
                      │  - ProductoService (Lógica de Negocio) │
                      │  - Eventos (ProductoActualizado)       │
                      │  - Base de Datos (SQLite + Eloquent)   │
                      └──────────────────▲─────────────────────┘
                                         │ HTTP Interno
                                         │ (X-Internal-Key)
                                         ▼
                      ┌────────────────────────────────────────┐
                      │         Servicio gRPC (Node.js)        │
                      │  - Puerto 50051 (HTTP/2)               │
                      │  - Protocol Buffers (producto.proto)   │
                      └──────────────────▲─────────────────────┘
                                         │ gRPC (Binario)
                              ┌──────────┴──────────┐
                              │  Cliente gRPC CLI   │
                              └─────────────────────┘
```

---

## 🚀 Requisitos Previos

- **PHP** >= 8.2 (con extensiones `pdo_sqlite`, `curl`, `mbstring`)
- **Composer**
- **Node.js** >= 18.x y **npm**
- **Git**

---

## 🛠️ Instalación y Configuración

### 1. Clonar el repositorio
```bash
git clone https://github.com/Ivan291002/Inventario-Multicanal.git
cd Inventario-Multicanal
```

### 2. Dependencias de PHP y entorno
```bash
composer install
cp .env.example .env
php artisan key:generate
```

### 3. Base de datos y datos de prueba
Crear la base de datos SQLite y ejecutar las migraciones con sus seeders:
```bash
# En Windows PowerShell:
New-Item -ItemType File -Path database/database.sqlite -Force

# Ejecutar migraciones y seeders
php artisan migrate --seed
```

### 4. Dependencias de Frontend y WebSockets
```bash
npm install
npm run build
```

### 5. Dependencias del Servicio gRPC
```bash
cd grpc
npm install
cd ..
```

---

## ⚙️ Puesta en Marcha

Para tener los cuatro canales operativos, abre diferentes terminales:

### Terminal 1: Servidor Web de Laravel
```bash
php artisan serve
# Disponible en: http://127.0.0.1:8000
```

### Terminal 2: Servidor WebSocket (Laravel Reverb)
```bash
php artisan reverb:start
```

### Terminal 3: Cola de eventos (Queue Worker)
```bash
php artisan queue:work
```

### Terminal 4: Servidor gRPC (Node.js)
```bash
cd grpc
node server.cjs
# Escuchando en 0.0.0.0:50051
```

---

## 🧪 Pruebas de Cada Canal

### 1. REST API (`/api/productos`)

- **Listar productos (GET):**
  ```powershell
  Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/productos"
  ```
- **Obtener producto por ID (GET):**
  ```powershell
  Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/productos/2"
  ```
- **Crear producto (POST):**
  ```powershell
  $body = @{ nombre = "Webcam HD"; precio = 45.50; stock = 15 } | ConvertTo-Json
  Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/productos" -Method Post -Body $body -ContentType "application/json"
  ```
- **Actualizar stock (PATCH/PUT):**
  ```powershell
  $body = @{ stock = 25 } | ConvertTo-Json
  Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/productos/2" -Method Put -Body $body -ContentType "application/json"
  ```

---

### 2. GraphQL (`/graphql`)

Accede a la interfaz GraphQL en `http://127.0.0.1:8000/graphql` o mediante Postman:

- **Consulta (Query):**
  ```graphql
  query {
    productos {
      id
      nombre
      precio
      stock
    }
  }
  ```

- **Mutación para actualizar stock (Mutation):**
  ```graphql
  mutation {
    actualizarStock(id: 2, stock: 40) {
      id
      nombre
      stock
    }
  }
  ```

---

### 3. WebSockets en Tiempo Real (`/inventario`)

1. Abre el navegador en: `http://127.0.0.1:8000/inventario`.
2. Verás el tablero de control de inventario en tiempo real.
3. Modifica el stock de cualquier producto mediante la REST API, GraphQL o gRPC.
4. Verás cómo el cambio impacta instantáneamente en la interfaz web sin recargar la página gracias al evento `ProductoActualizado` distribuido por Laravel Reverb y escuchado con Laravel Echo.

---

### 4. gRPC (Servicio Binario)

Con el servidor gRPC corriendo (`node grpc/server.cjs`), ejecuta el cliente de prueba en otra terminal:

```bash
cd grpc
node client.cjs
```

El cliente consultará un producto mediante RPC binario tipado por `grpc/producto.proto` y mostrará la respuesta serializada.

---

## 📊 Comparativa de Protocolos

| Característica | REST | GraphQL | WebSockets | gRPC |
| :--- | :--- | :--- | :--- | :--- |
| **Protocolo de transporte** | HTTP/1.1 o HTTP/2 | HTTP/1.1 o HTTP/2 | WebSocket (TCP duplex) | HTTP/2 |
| **Formato de datos** | JSON (Texto) | JSON (Texto) | JSON (Texto) | Protocol Buffers (Binario) |
| **Modelo de comunicación** | Petición / Respuesta | Petición / Respuesta | Bidireccional / Eventos | RPC (Unario o Streaming) |
| **Eficiencia de payload** | Sufre de over/underfetching | Óptima (payload exacto pedido) | Óptima para notificaciones | Máxima (binario comprimido) |
| **Caso de uso óptimo** | APIs públicas y CRUD general | Frontends complejos y dashboards | Interfaces reactivas en vivo | Microservicios de alto rendimiento |

---

## 📚 Documentación Adicional

- [RESUMEN_TRABAJO_PRACTICO.md](RESUMEN_TRABAJO_PRACTICO.md): Bitácora completa de los 9 errores encontrados durante el desarrollo, causas profundas y cómo se resolvieron.
