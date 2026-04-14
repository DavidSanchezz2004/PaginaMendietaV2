<?php

namespace Database\Seeders;

use App\Models\SpotDetraccion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Catálogo oficial de bienes y servicios sujetos a detracción SPOT.
 * Fuente: SUNAT - Catálogo N° 54 vigente.
 *
 * Ejecutar: php artisan db:seed --class=SpotDetraccionSeeder
 * Limpia la tabla completa y reinserta el catálogo correcto.
 */
class SpotDetraccionSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar tabla completamente antes de insertar el catálogo correcto
        DB::table('spot_detracciones')->truncate();

        $registros = [
            // ── Bienes ────────────────────────────────────────────────────
            ['codigo' => '001', 'descripcion' => 'Azúcar y melaza de caña',                                                                   'porcentaje' => 10.00],
            ['codigo' => '002', 'descripcion' => 'Arroz',                                                                                      'porcentaje' =>  3.00],
            ['codigo' => '003', 'descripcion' => 'Alcohol etílico',                                                                            'porcentaje' =>  4.00],
            ['codigo' => '004', 'descripcion' => 'Recursos hidrobiológicos',                                                                   'porcentaje' =>  4.00],
            ['codigo' => '005', 'descripcion' => 'Maíz amarillo duro',                                                                         'porcentaje' =>  4.00],
            ['codigo' => '007', 'descripcion' => 'Caña de azúcar',                                                                             'porcentaje' => 10.00],
            ['codigo' => '008', 'descripcion' => 'Madera',                                                                                     'porcentaje' =>  4.00],
            ['codigo' => '009', 'descripcion' => 'Arena y piedra',                                                                             'porcentaje' => 10.00],
            ['codigo' => '010', 'descripcion' => 'Residuos, subproductos, desechos, recortes y desperdicios',                                  'porcentaje' => 15.00],
            ['codigo' => '011', 'descripcion' => 'Bienes gravados con el IGV, o renuncia a la exoneración',                                    'porcentaje' => 10.00],
            ['codigo' => '013', 'descripcion' => 'Animales vivos',                                                                             'porcentaje' =>  3.00],
            ['codigo' => '014', 'descripcion' => 'Carnes y despojos comestibles',                                                              'porcentaje' =>  4.00],
            ['codigo' => '015', 'descripcion' => 'Abonos, cueros y pieles de origen animal',                                                   'porcentaje' =>  3.00],
            ['codigo' => '016', 'descripcion' => 'Aceite de pescado',                                                                          'porcentaje' => 10.00],
            ['codigo' => '017', 'descripcion' => 'Harina, polvo y "pellets" de pescado, crustáceos, moluscos y demás invertebrados acuáticos', 'porcentaje' =>  4.00],
            ['codigo' => '023', 'descripcion' => 'Leche',                                                                                      'porcentaje' =>  4.00],
            ['codigo' => '031', 'descripcion' => 'Oro gravado con el IGV',                                                                     'porcentaje' => 10.00],
            ['codigo' => '032', 'descripcion' => 'Paprika y otros frutos de los géneros Capsicum o Pimienta',                                  'porcentaje' => 10.00],
            ['codigo' => '034', 'descripcion' => 'Minerales metálicos no auríferos',                                                           'porcentaje' => 10.00],
            ['codigo' => '035', 'descripcion' => 'Bienes exonerados del IGV',                                                                  'porcentaje' =>  1.50],
            ['codigo' => '036', 'descripcion' => 'Oro y demás minerales metálicos exonerados del IGV',                                         'porcentaje' =>  1.50],
            ['codigo' => '039', 'descripcion' => 'Minerales no metálicos',                                                                     'porcentaje' => 10.00],
            ['codigo' => '040', 'descripcion' => 'Bien inmueble gravado con IGV',                                                              'porcentaje' =>  4.00],
            ['codigo' => '041', 'descripcion' => 'Plomo',                                                                                      'porcentaje' => 15.00],
            // ── Servicios ─────────────────────────────────────────────────
            ['codigo' => '012', 'descripcion' => 'Intermediación laboral y tercerización',                                                     'porcentaje' => 12.00],
            ['codigo' => '019', 'descripcion' => 'Arrendamiento de bienes muebles',                                                            'porcentaje' => 10.00],
            ['codigo' => '020', 'descripcion' => 'Mantenimiento y reparación de bienes muebles',                                               'porcentaje' => 12.00],
            ['codigo' => '021', 'descripcion' => 'Movimiento de carga',                                                                        'porcentaje' => 10.00],
            ['codigo' => '022', 'descripcion' => 'Otros servicios empresariales',                                                              'porcentaje' => 12.00],
            ['codigo' => '024', 'descripcion' => 'Comisión mercantil',                                                                         'porcentaje' => 10.00],
            ['codigo' => '025', 'descripcion' => 'Fabricación de bienes por encargo',                                                          'porcentaje' => 10.00],
            ['codigo' => '026', 'descripcion' => 'Servicio de transporte de personas',                                                         'porcentaje' => 10.00],
            ['codigo' => '027', 'descripcion' => 'Servicio de transporte de carga',                                                            'porcentaje' =>  3.00],
            ['codigo' => '028', 'descripcion' => 'Transporte de pasajeros',                                                                    'porcentaje' =>  3.00],
            ['codigo' => '030', 'descripcion' => 'Contratos de construcción',                                                                  'porcentaje' =>  4.00],
            ['codigo' => '037', 'descripcion' => 'Demás servicios gravados con el IGV',                                                        'porcentaje' => 12.00],
            // ── Especial ──────────────────────────────────────────────────
            ['codigo' => '099', 'descripcion' => 'Ley 30737',                                                                                  'porcentaje' =>  3.00],
        ];

        SpotDetraccion::insert(array_map(fn ($r) => array_merge($r, [
            'activo'     => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]), $registros));
    }
}