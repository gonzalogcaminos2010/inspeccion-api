<?php

namespace Database\Seeders;

use App\Models\InspectionTemplate;
use Illuminate\Database\Seeder;

class InspectionTemplateSeeder extends Seeder
{
    public function run(): void
    {
        if (InspectionTemplate::where('code', 'INSP-4X4-MIN')->exists()) {
            return;
        }

        $template = InspectionTemplate::create([
            'name' => 'Inspección Camioneta 4x4 - Minería',
            'code' => 'INSP-4X4-MIN',
            'description' => 'Formulario de inspección pre-uso para camionetas 4x4 en operaciones mineras',
            'vehicle_type' => 'camioneta_4x4',
            'is_active' => true,
            'version' => 1,
        ]);

        $sections = [
            [
                'name' => 'Documentación del Vehículo',
                'order' => 1,
                'questions' => [
                    ['text' => 'Tarjeta de propiedad vigente', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'SOAT vigente', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Revisión técnica vigente', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Póliza de seguro vigente', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Certificado de inspección técnica vehicular', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Manual del propietario disponible', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                ],
            ],
            [
                'name' => 'Estado Exterior',
                'order' => 2,
                'questions' => [
                    ['text' => 'Carrocería sin daños visibles', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Pintura en buen estado', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Parabrisas sin fisuras', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Espejos retrovisores completos y funcionales', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Plumillas en buen estado', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Estado general exterior', 'type' => 'select', 'is_required' => true, 'options' => ['Bueno', 'Regular', 'Malo'], 'fail_values' => ['Malo']],
                ],
            ],
            [
                'name' => 'Neumáticos y Suspensión',
                'order' => 3,
                'questions' => [
                    ['text' => 'Neumático delantero izquierdo - profundidad labrado OK', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Neumático delantero derecho - profundidad labrado OK', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Neumático trasero izquierdo - profundidad labrado OK', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Neumático trasero derecho - profundidad labrado OK', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Neumático de repuesto presente y en condiciones', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Presión de neumáticos correcta', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Suspensión sin ruidos anormales', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                ],
            ],
            [
                'name' => 'Sistema de Iluminación',
                'order' => 4,
                'questions' => [
                    ['text' => 'Luces delanteras bajas', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Luces delanteras altas', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Luces direccionales delanteras', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Luces direccionales traseras', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Luces de freno', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Luces de retroceso', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Luz de placa', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Luces neblineras', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Circulina/baliza operativa', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                ],
            ],
            [
                'name' => 'Motor y Mecánica',
                'order' => 5,
                'questions' => [
                    ['text' => 'Nivel de aceite motor', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Nivel de refrigerante', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Nivel de líquido de frenos', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Estado de correas', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Fugas visibles', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['1']],
                    ['text' => 'Estado de batería', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Kilometraje actual', 'type' => 'number', 'is_required' => true, 'fail_values' => null],
                ],
            ],
            [
                'name' => 'Sistema de Frenos',
                'order' => 6,
                'questions' => [
                    ['text' => 'Freno de servicio operativo', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Freno de estacionamiento operativo', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Pedal de freno firme', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'ABS funcional', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                ],
            ],
            [
                'name' => 'Interior del Vehículo',
                'order' => 7,
                'questions' => [
                    ['text' => 'Cinturones de seguridad operativos', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Asientos en buen estado', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Tablero e instrumentos funcionales', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Aire acondicionado funcional', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Claxon operativo', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Limpieza interior general', 'type' => 'select', 'is_required' => true, 'options' => ['Bueno', 'Regular', 'Malo'], 'fail_values' => ['Malo']],
                ],
            ],
            [
                'name' => 'Equipamiento de Seguridad',
                'order' => 8,
                'questions' => [
                    ['text' => 'Extintor vigente y cargado', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Botiquín de primeros auxilios', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Triángulos de seguridad (2 unidades)', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Conos de seguridad', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Chaleco reflectivo', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Linterna operativa', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Kit antiderrame', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Cable de remolque', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                ],
            ],
            [
                'name' => 'Equipamiento Minero Específico',
                'order' => 9,
                'questions' => [
                    ['text' => 'Pértiga con banderín', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Radio de comunicación operativa', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'GPS operativo', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Calcomanías reflectivas laterales', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Calcomanías reflectivas traseras', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Letrero de empresa visible', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Número de unidad visible', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                ],
            ],
            [
                'name' => 'Observaciones Generales',
                'order' => 10,
                'questions' => [
                    ['text' => 'Observaciones adicionales', 'type' => 'text', 'is_required' => false, 'fail_values' => null],
                    ['text' => 'Foto general del vehículo', 'type' => 'photo', 'is_required' => false, 'fail_values' => null],
                    ['text' => 'Evaluación general del inspector', 'type' => 'select', 'is_required' => true, 'options' => ['Aprobado', 'Aprobado con observaciones', 'Rechazado'], 'fail_values' => ['Rechazado']],
                ],
            ],
        ];

        foreach ($sections as $sectionData) {
            $questions = $sectionData['questions'];
            unset($sectionData['questions']);

            $section = $template->sections()->create($sectionData);

            foreach ($questions as $index => $questionData) {
                $questionData['order'] = $index + 1;
                $section->questions()->create($questionData);
            }
        }
    }
}
