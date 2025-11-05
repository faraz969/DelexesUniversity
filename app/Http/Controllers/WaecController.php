<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WaecController extends Controller
{
    public function searchForm()
    {
        return view('staff.waec');
    }

    public function fetchResults(Request $request)
    {
        $validated = $request->validate([
            'cindex' => 'required|string',
            'examyear' => 'required|string',
            'examtype' => 'required|integer',
            'reqref' => 'nullable|string',
        ]);

        $payload = [
            'cindex' => $validated['cindex'],
            'examyear' => $validated['examyear'],
            'examtype' => (int) $validated['examtype'],
            'reqref' => $validated['reqref'] ?? Str::uuid()->toString(),
        ];

        try {
            $response = Http::withBasicAuth('exsrlsdx', 'j3RQASh5zE')
                ->timeout(30)
                ->acceptJson()
                ->asJson()
                ->post('https://verify.waecgh.org/api/resultsreq/v3', $payload);

            if (!$response->ok()) {
                return response()->json([
                    'error' => true,
                    'message' => 'Verification service unavailable',
                    'status' => $response->status(),
                    'body' => $response->json(),
                ], 502);
            }

            return response()->json($response->json());
        } catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => 'Request failed',
                'detail' => $e->getMessage(),
            ], 500);
        }
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $request->validate([
            'cindex' => 'required|string',
            'examyear' => 'required|string',
            'examtype' => 'required|integer',
        ]);

        // Reuse fetch to get fresh data
        $payload = [
            'cindex' => $request->input('cindex'),
            'examyear' => $request->input('examyear'),
            'examtype' => (int) $request->input('examtype'),
            'reqref' => Str::uuid()->toString(),
        ];

        $response = Http::withBasicAuth('exsrlsdx', 'j3RQASh5zE')
            ->timeout(30)
            ->acceptJson()
            ->asJson()
            ->post('https://verify.waecgh.org/api/resultsreq/v3', $payload);

        $data = $response->ok() ? $response->json() : null;
        $details = ($data && isset($data['resultdetails']) && is_array($data['resultdetails'])) ? $data['resultdetails'] : [];
        $candidate = $data['candidate'] ?? [];

        $filename = 'waec_' . $payload['cindex'] . '_' . $payload['examyear'] . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($details, $candidate, $payload) {
            $handle = fopen('php://output', 'w');
            // Header info rows
            fputcsv($handle, ['Index', $payload['cindex']]);
            fputcsv($handle, ['Year', $payload['examyear']]);
            fputcsv($handle, ['Exam Type', $payload['examtype']]);
            if (!empty($candidate)) {
                fputcsv($handle, ['Candidate Name', $candidate['cname'] ?? '']);
                fputcsv($handle, ['DOB', $candidate['dob'] ?? '']);
            }
            // Blank line then table
            fputcsv($handle, []);
            fputcsv($handle, ['Subject Code', 'Subject', 'Grade (Letter)']);
            foreach ($details as $row) {
                fputcsv($handle, [
                    $row['subjectcode'] ?? '',
                    $row['subject'] ?? '',
                    $row['grade'] ?? '',
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }
}


