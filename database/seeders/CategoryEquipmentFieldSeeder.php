<?php

namespace Database\Seeders;

use App\Models\CategoryEquipmentField;
use App\Models\TemplateCategory;
use Illuminate\Database\Seeder;

/**
 * Seed the identification-field schemas per category.
 *
 * Identification fields = the per-category data the inspector fills during the
 * inspection ("Datos del Equipo" tab) which then sync to equipment.metadata
 * at submit time. `is_mutable=false` = identity attributes locked after first
 * capture (chassis, plate, brand…). `is_mutable=true` = changes per inspection
 * (próxima inspección, observaciones, kms).
 *
 * Idempotent: matches on (template_category_id, key_name) via firstOrCreate.
 */
class CategoryEquipmentFieldSeeder extends Seeder
{
    public function run(): void
    {
        $schemas = [
            'vehiculo_liviano' => $this->vehicleFields(),
            'camioneta_4x4' => $this->vehicleFields(),
            'perforadora_diamantina' => $this->drillFields(),
            'equipo_pesado_mineria' => $this->heavyFields(),
            'grua_izaje' => $this->craneFields(),
            'compresor' => $this->genericFields(),
            'generador' => $this->genericFields(),
            'instalacion_electrica' => $this->installationFields(),
            'instalacion_industrial' => $this->installationFields(),
            'otro' => $this->genericFields(),
            'sin_clasificar' => $this->genericFields(),
        ];

        foreach ($schemas as $code => $fields) {
            $category = TemplateCategory::where('code', $code)->first();
            if (! $category) {
                continue;
            }

            foreach ($fields as $order => $field) {
                CategoryEquipmentField::firstOrCreate(
                    [
                        'template_category_id' => $category->id,
                        'key_name' => $field['key_name'],
                    ],
                    array_merge($field, ['sort_order' => $order + 1])
                );
            }
        }
    }

    private function vehicleFields(): array
    {
        return [
            ['key_name' => 'marca', 'label' => 'Marca', 'type' => 'text', 'is_required' => true, 'is_mutable' => false],
            ['key_name' => 'modelo', 'label' => 'Modelo', 'type' => 'text', 'is_required' => true, 'is_mutable' => false],
            ['key_name' => 'transmision', 'label' => 'Transmisión (at/m)', 'type' => 'select', 'options' => ['AT', 'MT'], 'is_required' => false, 'is_mutable' => false],
            ['key_name' => 'dominio', 'label' => 'Dominio Nº', 'type' => 'text', 'is_required' => true, 'is_mutable' => false],
            ['key_name' => 'anio_fabricacion', 'label' => 'Año de Fabricación', 'type' => 'number', 'is_required' => true, 'is_mutable' => false],
            ['key_name' => 'num_chasis', 'label' => 'Nº de Chasis', 'type' => 'text', 'is_required' => true, 'is_mutable' => false],
            ['key_name' => 'carga_max', 'label' => 'Carga Máxima', 'type' => 'number', 'unit' => 'kg', 'is_required' => false, 'is_mutable' => false],
            ['key_name' => 'tara', 'label' => 'Tara', 'type' => 'number', 'unit' => 'kg', 'is_required' => false, 'is_mutable' => false],
            ['key_name' => 'clave', 'label' => 'Clave de Identificación', 'type' => 'text', 'is_required' => false, 'is_mutable' => true],
            ['key_name' => 'proxima_inspeccion', 'label' => 'Próxima Inspección', 'type' => 'date', 'is_required' => false, 'is_mutable' => true],
            ['key_name' => 'observaciones', 'label' => 'Observaciones', 'type' => 'text', 'is_required' => false, 'is_mutable' => true],
        ];
    }

    private function drillFields(): array
    {
        return [
            ['key_name' => 'marca', 'label' => 'Marca', 'type' => 'text', 'is_required' => true, 'is_mutable' => false],
            ['key_name' => 'modelo', 'label' => 'Modelo', 'type' => 'text', 'is_required' => true, 'is_mutable' => false],
            ['key_name' => 'num_serie', 'label' => 'Nº de Serie', 'type' => 'text', 'is_required' => true, 'is_mutable' => false],
            ['key_name' => 'anio_fabricacion', 'label' => 'Año de Fabricación', 'type' => 'number', 'is_required' => true, 'is_mutable' => false],
            ['key_name' => 'profundidad_max', 'label' => 'Profundidad Máxima', 'type' => 'number', 'unit' => 'm', 'is_required' => false, 'is_mutable' => false],
            ['key_name' => 'tipo_torre', 'label' => 'Tipo de Torre', 'type' => 'text', 'is_required' => false, 'is_mutable' => false],
            ['key_name' => 'horas_motor', 'label' => 'Horas de Motor', 'type' => 'number', 'unit' => 'h', 'is_required' => false, 'is_mutable' => true],
            ['key_name' => 'proxima_inspeccion', 'label' => 'Próxima Inspección', 'type' => 'date', 'is_required' => false, 'is_mutable' => true],
            ['key_name' => 'observaciones', 'label' => 'Observaciones', 'type' => 'text', 'is_required' => false, 'is_mutable' => true],
        ];
    }

    private function heavyFields(): array
    {
        return [
            ['key_name' => 'marca', 'label' => 'Marca', 'type' => 'text', 'is_required' => true, 'is_mutable' => false],
            ['key_name' => 'modelo', 'label' => 'Modelo', 'type' => 'text', 'is_required' => true, 'is_mutable' => false],
            ['key_name' => 'num_serie', 'label' => 'Nº de Serie', 'type' => 'text', 'is_required' => true, 'is_mutable' => false],
            ['key_name' => 'anio_fabricacion', 'label' => 'Año de Fabricación', 'type' => 'number', 'is_required' => true, 'is_mutable' => false],
            ['key_name' => 'capacidad_operacion', 'label' => 'Capacidad de Operación', 'type' => 'text', 'is_required' => false, 'is_mutable' => false],
            ['key_name' => 'horas_motor', 'label' => 'Horas de Motor', 'type' => 'number', 'unit' => 'h', 'is_required' => false, 'is_mutable' => true],
            ['key_name' => 'proxima_inspeccion', 'label' => 'Próxima Inspección', 'type' => 'date', 'is_required' => false, 'is_mutable' => true],
            ['key_name' => 'observaciones', 'label' => 'Observaciones', 'type' => 'text', 'is_required' => false, 'is_mutable' => true],
        ];
    }

    private function craneFields(): array
    {
        return [
            ['key_name' => 'marca', 'label' => 'Marca', 'type' => 'text', 'is_required' => true, 'is_mutable' => false],
            ['key_name' => 'modelo', 'label' => 'Modelo', 'type' => 'text', 'is_required' => true, 'is_mutable' => false],
            ['key_name' => 'num_serie', 'label' => 'Nº de Serie', 'type' => 'text', 'is_required' => true, 'is_mutable' => false],
            ['key_name' => 'anio_fabricacion', 'label' => 'Año de Fabricación', 'type' => 'number', 'is_required' => true, 'is_mutable' => false],
            ['key_name' => 'capacidad_max', 'label' => 'Capacidad Máxima', 'type' => 'number', 'unit' => 'kg', 'is_required' => true, 'is_mutable' => false],
            ['key_name' => 'alcance_max', 'label' => 'Alcance Máximo', 'type' => 'number', 'unit' => 'm', 'is_required' => false, 'is_mutable' => false],
            ['key_name' => 'proxima_inspeccion', 'label' => 'Próxima Inspección', 'type' => 'date', 'is_required' => false, 'is_mutable' => true],
            ['key_name' => 'observaciones', 'label' => 'Observaciones', 'type' => 'text', 'is_required' => false, 'is_mutable' => true],
        ];
    }

    private function genericFields(): array
    {
        return [
            ['key_name' => 'marca', 'label' => 'Marca', 'type' => 'text', 'is_required' => false, 'is_mutable' => false],
            ['key_name' => 'modelo', 'label' => 'Modelo', 'type' => 'text', 'is_required' => false, 'is_mutable' => false],
            ['key_name' => 'num_serie', 'label' => 'Nº de Serie', 'type' => 'text', 'is_required' => false, 'is_mutable' => false],
            ['key_name' => 'anio_fabricacion', 'label' => 'Año de Fabricación', 'type' => 'number', 'is_required' => false, 'is_mutable' => false],
            ['key_name' => 'proxima_inspeccion', 'label' => 'Próxima Inspección', 'type' => 'date', 'is_required' => false, 'is_mutable' => true],
            ['key_name' => 'observaciones', 'label' => 'Observaciones', 'type' => 'text', 'is_required' => false, 'is_mutable' => true],
        ];
    }

    private function installationFields(): array
    {
        return [
            ['key_name' => 'tipo_instalacion', 'label' => 'Tipo de Instalación', 'type' => 'text', 'is_required' => false, 'is_mutable' => false],
            ['key_name' => 'ubicacion', 'label' => 'Ubicación', 'type' => 'text', 'is_required' => false, 'is_mutable' => false],
            ['key_name' => 'potencia', 'label' => 'Potencia', 'type' => 'text', 'is_required' => false, 'is_mutable' => false],
            ['key_name' => 'proxima_inspeccion', 'label' => 'Próxima Inspección', 'type' => 'date', 'is_required' => false, 'is_mutable' => true],
            ['key_name' => 'observaciones', 'label' => 'Observaciones', 'type' => 'text', 'is_required' => false, 'is_mutable' => true],
        ];
    }
}
