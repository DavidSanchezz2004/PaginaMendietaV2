<?php

namespace Database\Seeders;

use App\Models\SpotDetraccion;
use Illuminate\Database\Seeder;

/**
 * Catálogo de bienes y servicios sujetos a detracción SPOT.
 * Fuente: SUNAT - Anexo 2 RS 183-2004/SUNAT y modificatorias vigentes.
 *
 * Lista no exhaustiva — contiene los códigos de mayor uso en estudios contables.
 */
class SpotDetraccionSeeder extends Seeder
{
    public function run(): void
    {
        $registros = [
            // ── Bienes ────────────────────────────────────────────────────
            ['codigo' => '001', 'descripcion' => 'Azúcar',                                            'porcentaje' => 10.00],
            ['codigo' => '003', 'descripcion' => 'Alcohol etílico',                                   'porcentaje' => 10.00],
            ['codigo' => '004', 'descripcion' => 'Recursos hidrobiológicos',                          'porcentaje' =>  4.00],
            ['codigo' => '005', 'descripcion' => 'Maíz amarillo duro',                                'porcentaje' =>  4.00],
            ['codigo' => '006', 'descripcion' => 'Algodón',                                           'porcentaje' => 10.00],
            ['codigo' => '007', 'descripcion' => 'Caña de azúcar',                                   'porcentaje' => 10.00],
            ['codigo' => '008', 'descripcion' => 'Madera',                                            'porcentaje' =>  4.00],
            ['codigo' => '009', 'descripcion' => 'Arena y piedra',                                   'porcentaje' => 10.00],
            ['codigo' => '010', 'descripcion' => 'Residuos, subproductos, desechos, recortes y desperdicios', 'porcentaje' => 15.00],
            ['codigo' => '011', 'descripcion' => 'Bienes del inciso A) del Apéndice I de la Ley del IGV', 'porcentaje' => 4.00],
            ['codigo' => '016', 'descripcion' => 'Aceite de pescado',                                 'porcentaje' =>  9.00],
            ['codigo' => '017', 'descripcion' => 'Harina, polvo y "pellets" de pescado, crustáceos, moluscos y demás invertebrados acuáticos', 'porcentaje' => 9.00],
            ['codigo' => '020', 'descripcion' => 'Legumbres, hortalizas, frutas y semillas comestibles', 'porcentaje' => 4.00],
            ['codigo' => '021', 'descripcion' => 'Oro gravado con el IGV',                            'porcentaje' => 12.00],
            ['codigo' => '022', 'descripcion' => 'Paprika y otros frutos de los géneros Capsicum o Pimienta en estado fresco o refrigerado', 'porcentaje' => 4.00],
            ['codigo' => '023', 'descripcion' => 'Leche cruda entera',                                'porcentaje' =>  4.00],
            ['codigo' => '024', 'descripcion' => 'Maíz gigante del Cuzco',                            'porcentaje' =>  4.00],
            ['codigo' => '025', 'descripcion' => 'Minerales metálicos no auríferos (cobre y sus concentrados, etc.)', 'porcentaje' => 10.00],
            ['codigo' => '026', 'descripcion' => 'Bienes exonerados del IGV - Apéndice I',            'porcentaje' =>  1.50],
            ['codigo' => '027', 'descripcion' => 'Plomo',                                             'porcentaje' => 10.00],
            ['codigo' => '034', 'descripcion' => 'Minerales no metálicos',                            'porcentaje' => 10.00],
            // ── Servicios ─────────────────────────────────────────────────
            ['codigo' => '012', 'descripcion' => 'Intermediación laboral y tercerización',            'porcentaje' => 12.00],
            ['codigo' => '013', 'descripcion' => 'Arrendamiento de bienes',                           'porcentaje' =>  9.00],
            ['codigo' => '014', 'descripcion' => 'Mantenimiento y reparación de bienes muebles',      'porcentaje' =>  9.00],
            ['codigo' => '015', 'descripcion' => 'Movimiento de carga',                               'porcentaje' =>  9.00],
            ['codigo' => '016', 'descripcion' => 'Otros servicios empresariales',                     'porcentaje' =>  9.00],
            ['codigo' => '019', 'descripcion' => 'Servicio de transporte de personas',                'porcentaje' =>  9.00],
            ['codigo' => '028', 'descripcion' => 'Transporte de carga - Primera categoría',           'porcentaje' =>  4.00],
            ['codigo' => '029', 'descripcion' => 'Servicio de transporte terrestre de bienes realizados por vía terrestre', 'porcentaje' => 4.00],
            ['codigo' => '030', 'descripcion' => 'Contratos de construcción',                         'porcentaje' =>  4.00],
            ['codigo' => '031', 'descripcion' => 'Fabricación de bienes por encargo',                 'porcentaje' =>  9.00],
            ['codigo' => '032', 'descripcion' => 'Servicio de revisión técnica vehicular',            'porcentaje' =>  9.00],
            ['codigo' => '033', 'descripcion' => 'Espectáculos públicos',                             'porcentaje' =>  9.00],
            ['codigo' => '037', 'descripcion' => 'Demás servicios gravados con el IGV',               'porcentaje' =>  9.00],
            ['codigo' => '040', 'descripcion' => 'Resto de servicios gravados con el IGV (monto < S/700)', 'porcentaje' => 9.00],
        ];

        foreach ($registros as $r) {
            SpotDetraccion::updateOrCreate(
                ['codigo' => $r['codigo']],
                [
                    'descripcion' => $r['descripcion'],
                    'porcentaje'  => $r['porcentaje'],
                    'activo'      => true,
                ]
            );
        }
    }
}
