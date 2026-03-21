<?php

namespace Database\Seeders;

use App\Models\InspectionTemplate;
use Illuminate\Database\Seeder;

class LenorGruaArticuladaTemplateSeeder extends Seeder
{
    public function run(): void
    {
        if (InspectionTemplate::where('code', 'INSP-GRUA-ART')->exists()) {
            return;
        }

        $template = InspectionTemplate::create([
            'name' => 'Inspección Grúa de Pluma Articulada',
            'code' => 'INSP-GRUA-ART',
            'description' => 'Formulario de inspección para grúas de pluma articulada en operaciones mineras',
            'vehicle_type' => 'grua_pluma_articulada',
            'is_active' => true,
            'version' => 1,
        ]);

        $sections = [
            [
                'name' => 'VEHÍCULO PORTANTE',
                'order' => 1,
                'questions' => [
                    ['text' => 'Descripción del vehículo portante', 'type' => 'text', 'is_required' => true, 'fail_values' => null],
                ],
            ],
            [
                'name' => 'DOCUMENTACIÓN',
                'order' => 2,
                'questions' => [
                    ['text' => 'Manual del operador disponible', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Manual de mantenimiento disponible', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Tabla de carga disponible y legible', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Registro de inspecciones anteriores', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Documentación del vehículo portante', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'VTV vigente', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                ],
            ],
            [
                'name' => 'LETREROS E INDICADORES',
                'order' => 3,
                'questions' => [
                    ['text' => 'Placa de identificación del fabricante', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Gráficas de operación y advertencia', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Indicación de capacidad máxima', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Señalización de seguridad', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                ],
            ],
            [
                'name' => 'ESTRUCTURA',
                'order' => 4,
                'questions' => [
                    ['text' => 'Estado general del chasis', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Estabilizadores / apoyos', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Fijación de la grúa al chasis', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Protecciones y resguardos', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Soldaduras y uniones', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Pasadores y bulones', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                ],
            ],
            [
                'name' => 'CABINA - PUESTO DEL OPERADOR',
                'order' => 5,
                'questions' => [
                    ['text' => 'Estructura de cabina', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Butaca del operador', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Cinturón de seguridad', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Tablero de instrumentos y comandos', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Puertas y accesos', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Matafuego', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Limpia parabrisas', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Espejos retrovisores', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                ],
            ],
            [
                'name' => 'PLUMA',
                'order' => 6,
                'questions' => [
                    ['text' => 'Columna de giro', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Brazo principal', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Brazo articulado', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Extensiones telescópicas', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Pernos y articulaciones', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Estado de soldaduras', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                ],
            ],
            [
                'name' => 'SISTEMA DE GIRO',
                'order' => 7,
                'questions' => [
                    ['text' => 'Estado general del sistema de giro', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Rodamiento de giro', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Piñón de ataque', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Freno de giro', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                ],
            ],
            [
                'name' => 'MOTOR',
                'order' => 8,
                'questions' => [
                    ['text' => 'Niveles de fluidos', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Estado general del motor', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Sistema de escape', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                ],
            ],
            [
                'name' => 'SISTEMA DE COMBUSTIBLE',
                'order' => 9,
                'questions' => [
                    ['text' => 'Tanque de combustible', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Tapa del tanque', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Cañerías de combustible', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Mangueras de combustible', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                ],
            ],
            [
                'name' => 'SISTEMA DE TRASLACIÓN',
                'order' => 10,
                'questions' => [
                    ['text' => 'Diferencial', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Mandos finales', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Ruedas y neumáticos', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Sistema de dirección', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Frenos de servicio', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Freno de estacionamiento', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                ],
            ],
            [
                'name' => 'SISTEMA HIDRÁULICO',
                'order' => 11,
                'questions' => [
                    ['text' => 'Tanque hidráulico', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Visor de nivel', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Mangueras y conexiones', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Bomba hidráulica', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Válvulas de control', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Cilindros hidráulicos', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Filtros hidráulicos', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                ],
            ],
            [
                'name' => 'SISTEMA NEUMÁTICO',
                'order' => 12,
                'questions' => [
                    ['text' => 'Compresor', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Válvulas neumáticas', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Tanque de aire', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Mangueras neumáticas', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                ],
            ],
            [
                'name' => 'SISTEMA ELÉCTRICO',
                'order' => 13,
                'questions' => [
                    ['text' => 'Baterías', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Cableado general', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Interruptores y controles', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Luces de trabajo', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Luces de circulación', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Alarma de retroceso', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Bocina', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                ],
            ],
            [
                'name' => 'SISTEMA DE IZAJE - CABRESTANTE',
                'order' => 14,
                'questions' => [
                    ['text' => 'Reductor del cabrestante', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Tambor de enrollamiento', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Poleas', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Freno del cabrestante', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                ],
            ],
            [
                'name' => 'CABLE DE ACERO',
                'order' => 15,
                'questions' => [
                    ['text' => 'Diámetro medido (mm)', 'type' => 'number', 'is_required' => true, 'fail_values' => null],
                    ['text' => 'Estado general del cable', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Terminales y sujeciones', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Engrase del cable', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                ],
            ],
            [
                'name' => 'GANCHO',
                'order' => 16,
                'questions' => [
                    ['text' => 'Grilleta / molinete', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Indicación de capacidad', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Pestillo de seguridad', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Apertura de garganta dentro de tolerancia', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Desgaste y deformaciones', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                ],
            ],
            [
                'name' => 'DISPOSITIVOS DE SEGURIDAD',
                'order' => 17,
                'questions' => [
                    ['text' => 'Indicador de momento de carga', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Limitador de momento de carga', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Válvula de contrabalanceo', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Final de carrera de elevación', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Final de carrera de extensión', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Detector de ángulo', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                ],
            ],
            [
                'name' => 'MOVIMIENTOS - PRUEBA OPERATIVA',
                'order' => 18,
                'questions' => [
                    ['text' => 'Elevación de pluma', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Descenso de pluma', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Extensión de telescópicos', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Retracción de telescópicos', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Giro a derecha', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Giro a izquierda', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Traslación adelante', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Traslación reversa', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Frenos de servicio operativos', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                ],
            ],
            [
                'name' => 'PRUEBA CON CARGA',
                'order' => 19,
                'questions' => [
                    ['text' => 'Retención de carga en elevación', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Prueba operativa con carga', 'type' => 'yes_no', 'is_required' => true, 'fail_values' => ['0']],
                    ['text' => 'Valor de carga de prueba (kg)', 'type' => 'number', 'is_required' => true, 'fail_values' => null],
                    ['text' => 'Radio de prueba (m)', 'type' => 'number', 'is_required' => true, 'fail_values' => null],
                ],
            ],
            [
                'name' => 'INSTRUMENTOS UTILIZADOS',
                'order' => 20,
                'questions' => [
                    ['text' => 'Listado de instrumentos utilizados', 'type' => 'text', 'is_required' => true, 'fail_values' => null],
                    ['text' => 'Identificación de instrumentos', 'type' => 'text', 'is_required' => true, 'fail_values' => null],
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
