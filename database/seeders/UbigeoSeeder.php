<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class UbigeoSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/UBIGEODISTRITOS.xlsx');

        if (! is_file($path)) {
            $this->command?->warn("No se encontro el archivo de ubigeos: {$path}");
            $this->command?->warn('Agrega el Excel o usa: php artisan ubigeos:import "{ruta_del_excel}"');

            return;
        }

        $spreadsheet = IOFactory::load($path);
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        if (count($rows) < 2) {
            $this->command?->warn('El archivo de ubigeos no tiene filas para importar.');

            return;
        }

        $headers = array_map(
            fn ($value) => $this->normalizeHeader((string) $value),
            array_shift($rows)
        );

        $codeColumn = $this->findColumn($headers, ['IDDIST', 'UBIGEO', 'CODIGO', 'CODE']);
        $departmentColumn = $this->findColumn($headers, ['NOMBDEP', 'DEPARTAMENTO', 'DEPARTMENT']);
        $provinceColumn = $this->findColumn($headers, ['NOMBPROV', 'PROVINCIA', 'PROVINCE']);
        $districtColumn = $this->findColumn($headers, ['NOMBDIST', 'DISTRITO', 'DISTRICT']);
        $capitalColumn = $this->findColumn($headers, ['NOM CAPITAL LEGAL', 'NOM_CAPITAL LEGAL', 'NOM_CAPITAL', 'CAPITAL']);

        if (! $codeColumn || ! $departmentColumn || ! $provinceColumn || ! $districtColumn) {
            $this->command?->error('El Excel de ubigeos no tiene las columnas requeridas: IDDIST, NOMBDEP, NOMBPROV y NOMBDIST.');

            return;
        }

        $now = now();
        $ubigeos = [];

        foreach ($rows as $row) {
            $code = preg_replace('/\D/', '', (string) ($row[$codeColumn] ?? ''));

            if ($code === '') {
                continue;
            }

            $ubigeos[] = [
                'code' => str_pad(substr($code, 0, 6), 6, '0', STR_PAD_LEFT),
                'department' => trim((string) ($row[$departmentColumn] ?? '')),
                'province' => trim((string) ($row[$provinceColumn] ?? '')),
                'district' => trim((string) ($row[$districtColumn] ?? '')),
                'legal_capital' => $capitalColumn ? $this->nullableText($row[$capitalColumn] ?? null) : null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($ubigeos, 500) as $chunk) {
            DB::table('ubigeos')->upsert(
                $chunk,
                ['code'],
                ['department', 'province', 'district', 'legal_capital', 'updated_at']
            );
        }

        $this->command?->info('Ubigeos importados/actualizados: '.count($ubigeos));
    }

    private function findColumn(array $headers, array $candidates): ?string
    {
        $normalizedCandidates = array_map(
            fn ($candidate) => $this->normalizeHeader($candidate),
            $candidates
        );

        foreach ($headers as $column => $header) {
            if (in_array($header, $normalizedCandidates, true)) {
                return $column;
            }
        }

        return null;
    }

    private function normalizeHeader(string $value): string
    {
        $value = str_replace(['_', '-', '(', ')'], ' ', strtoupper(trim($value)));

        return preg_replace('/\s+/', ' ', $value) ?: '';
    }

    private function nullableText(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
