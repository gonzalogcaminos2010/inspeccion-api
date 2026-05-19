<?php

namespace Database\Seeders;

use App\Models\TemplateCategory;
use Illuminate\Database\Seeder;

class TemplateCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['code' => 'vehiculo_liviano', 'name' => 'Vehículo Liviano'],
            ['code' => 'camioneta_4x4', 'name' => 'Camioneta 4x4'],
            ['code' => 'perforadora_diamantina', 'name' => 'Perforadora Diamantina'],
            ['code' => 'equipo_pesado_mineria', 'name' => 'Equipo Pesado Minería'],
            ['code' => 'grua_izaje', 'name' => 'Grúa/Izaje'],
            ['code' => 'compresor', 'name' => 'Compresor'],
            ['code' => 'generador', 'name' => 'Generador'],
            ['code' => 'instalacion_electrica', 'name' => 'Instalación Eléctrica'],
            ['code' => 'instalacion_industrial', 'name' => 'Instalación Industrial'],
            ['code' => 'otro', 'name' => 'Otro'],
        ];

        foreach ($categories as $cat) {
            TemplateCategory::firstOrCreate(
                ['code' => $cat['code']],
                ['name' => $cat['name'], 'is_active' => true]
            );
        }
    }
}
