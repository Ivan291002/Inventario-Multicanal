<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $productos = [
            ['nombre' => 'Mouse Gamer', 'precio' => 18500, 'stock' => 20],
            ['nombre' => 'Teclado Mecánico', 'precio' => 45000, 'stock' => 15],
            ['nombre' => 'Monitor LED 24 pulgadas', 'precio' => 185000, 'stock' => 8],
            ['nombre' => 'Notebook Lenovo', 'precio' => 950000, 'stock' => 5],
            ['nombre' => 'Disco SSD 480GB', 'precio' => 42000, 'stock' => 30],
            ['nombre' => 'Memoria RAM 16GB', 'precio' => 55000, 'stock' => 25],
            ['nombre' => 'Pendrive 64GB', 'precio' => 12000, 'stock' => 50],
            ['nombre' => 'Auriculares Bluetooth', 'precio' => 38000, 'stock' => 18],
            ['nombre' => 'Webcam Full HD', 'precio' => 32000, 'stock' => 12],
            ['nombre' => 'Router WiFi', 'precio' => 68000, 'stock' => 10],
            ['nombre' => 'Switch 8 Puertos', 'precio' => 48000, 'stock' => 7],
            ['nombre' => 'Cable HDMI 2 metros', 'precio' => 8500, 'stock' => 40],
            ['nombre' => 'Impresora Láser', 'precio' => 320000, 'stock' => 4],
            ['nombre' => 'Placa de Video RTX 4060', 'precio' => 580000, 'stock' => 3],
            ['nombre' => 'Fuente 650W', 'precio' => 75000, 'stock' => 9],
        ];

        foreach ($productos as $producto) {
            Producto::updateOrCreate(
                ['nombre' => $producto['nombre']],
                [
                    'precio' => $producto['precio'],
                    'stock' => $producto['stock']
                ]
            );
        }
    }
}