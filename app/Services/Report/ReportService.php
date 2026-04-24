<?php

namespace App\Services\Report;

use App\Models\Company;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ReportService
{
    public function createReport(Company $company, User $uploader, array $data, ?UploadedFile $file = null): Report
    {
        if (in_array($data['format'], ['pdf', 'excel']) && $file) {
            $data['file_path'] = $file->store("reports/{$company->id}", 'local');
            $data['external_url'] = null;
        } elseif ($data['format'] === 'powerbi') {
            $data['file_path'] = null;
        }

        $report = new Report($data);
        $report->company_id = $company->id;
        $report->uploaded_by = $uploader->id;
        $report->save();

        return $report;
    }

    public function updateReport(Report $report, array $data, ?UploadedFile $file = null): Report
    {
        if (in_array($data['format'], ['pdf', 'excel'])) {
            if ($file) {
                if ($report->file_path) {
                    Storage::disk('local')->delete($report->file_path);
                }
                $data['file_path'] = $file->store("reports/{$report->company_id}", 'local');
            }
            $data['external_url'] = null;
        } elseif ($data['format'] === 'powerbi') {
            if ($report->file_path) {
                Storage::disk('local')->delete($report->file_path);
            }
            $data['file_path'] = null;
        }

        $report->update($data);

        return $report;
    }

    public function deleteReport(Report $report): void
    {
        if ($report->file_path) {
            Storage::disk('local')->delete($report->file_path);
        }
        $report->delete();
    }

    public function publishReport(Report $report): void
    {
        $report->update(['status' => 'published']);
    }

    public function unpublishReport(Report $report): void
    {
        $report->update(['status' => 'draft']);
    }

    public function markAsRead(Report $report, User $user): void
    {
        $status = $report->reportUserStatuses()->firstOrCreate(
            ['user_id' => $user->id]
        );

        if (! $status->read_at) {
            $status->update(['read_at' => now()]);
        }
    }

    public function markAsValued(Report $report, User $user): void
    {
        $status = $report->reportUserStatuses()->firstOrCreate(
            ['user_id' => $user->id]
        );

        if (! $status->valued_at) {
            $status->update(['valued_at' => now()]);
        }
    }
}
