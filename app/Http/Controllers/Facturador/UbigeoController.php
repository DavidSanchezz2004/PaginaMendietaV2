<?php

namespace App\Http\Controllers\Facturador;

use App\Http\Controllers\Controller;
use App\Models\Ubigeo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UbigeoController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json(['results' => []]);
        }

        $like = '%' . str_replace(['%', '_'], ['\%', '\_'], mb_strtoupper($term)) . '%';

        $results = Ubigeo::query()
            ->where(function ($query) use ($like): void {
                $query->where('code', 'like', $like)
                    ->orWhereRaw('UPPER(department) LIKE ?', [$like])
                    ->orWhereRaw('UPPER(province) LIKE ?', [$like])
                    ->orWhereRaw('UPPER(district) LIKE ?', [$like])
                    ->orWhereRaw('UPPER(legal_capital) LIKE ?', [$like]);
            })
            ->orderByRaw('CASE WHEN code LIKE ? THEN 0 ELSE 1 END', [rtrim($like, '%') . '%'])
            ->orderBy('department')
            ->orderBy('province')
            ->orderBy('district')
            ->limit(20)
            ->get()
            ->map(fn (Ubigeo $ubigeo): array => [
                'code' => $ubigeo->code,
                'label' => "{$ubigeo->code} - {$ubigeo->department} / {$ubigeo->province} / {$ubigeo->district}",
                'department' => $ubigeo->department,
                'province' => $ubigeo->province,
                'district' => $ubigeo->district,
                'legal_capital' => $ubigeo->legal_capital,
            ]);

        return response()->json(['results' => $results]);
    }
}
