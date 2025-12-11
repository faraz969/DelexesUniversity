<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applicant Declaration - {{ $application->application_number ?? '' }}</title>
    <style>
        @media print {
            @page {
                size: A4;
                margin: 20mm;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #000;
            background: #fff;
            padding: 20px;
        }
        
        .declaration-container {
            max-width: 210mm;
            margin: 0 auto;
            background: #fff;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            border-bottom: 3px solid #1a237e;
            padding-bottom: 15px;
        }
        
        go-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color:#1e3a8a;
        }
        
        .logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .university-name {
            color: #1a237e;
        }
        
        .university-name h1 {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
            letter-spacing: 1px;
        }
        
        .university-name h2 {
            font-size: 14px;
            font-weight: normal;
            color: #333;
        }
        
        .university-name .tagline {
            font-size: 11px;
            color: #666;
            font-style: italic;
        }
        
        .title-section {
            text-align: center;
            flex: 1;
        }
        
        .title-section h1 {
            font-size: 22px;
            font-weight: bold;
            color: #1a237e;
            margin-bottom: 5px;
        }
        
        .title-section h2 {
            font-size: 14px;
            font-weight: normal;
            color: #333;
        }
        
        .photo-section {
            width: 120px;
            text-align: center;
        }
        
        .applicant-photo {
            width: 100px;
            height: 120px;
            border: 2px solid #1a237e;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 5px;
            overflow: hidden;
        }
        
        .applicant-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .applicant-photo .placeholder {
            color: #999;
            font-size: 10px;
            text-align: center;
            padding: 10px;
        }
        
        .info-section {
            margin: 25px 0;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .info-item {
            margin-bottom: 12px;
        }
        
        .info-label {
            font-weight: bold;
            color: #1a237e;
            margin-bottom: 3px;
            font-size: 11pt;
        }
        
        .info-value {
            color: #000;
            font-size: 11pt;
            padding: 5px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .exam-table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            border: 1px solid #333;
        }
        
        .exam-table th {
            background: #1a237e;
            color: #fff;
            padding: 10px;
            text-align: left;
            font-weight: bold;
            font-size: 11pt;
            border: 1px solid #333;
        }
        
        .exam-table td {
            padding: 8px 10px;
            border: 1px solid #333;
            font-size: 11pt;
        }
        
        .exam-table tr:nth-child(even) {
            background: #f9f9f9;
        }
        
        .declaration-text {
            margin: 30px 0;
            padding: 20px;
            background: #f9f9f9;
            border-left: 4px solid #1a237e;
            font-size: 11pt;
            line-height: 1.8;
        }
        
        .declaration-text strong {
            color: #1a237e;
        }
        
        .notes-section {
            margin: 25px 0;
        }
        
        .notes-section h3 {
            font-size: 13pt;
            color: #1a237e;
            margin-bottom: 15px;
            font-weight: bold;
        }
        
        .notes-section ol {
            margin-left: 25px;
            line-height: 2;
        }
        
        .notes-section li {
            margin-bottom: 10px;
            font-size: 11pt;
        }
        
        .barcode {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            border-top: 2px solid #333;
        }
        
        .barcode-placeholder {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            letter-spacing: 2px;
            padding: 10px;
            background: #f5f5f5;
            display: inline-block;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: #1a237e;
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        .print-button:hover {
            background: #283593;
        }
        
        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
            padding-top: 20px;
            border-top: 1px solid #333;
        }
        
        .signature-box {
            width: 300px;
            text-align: center;
        }
        
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 60px;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">
        <i class="fas fa-print"></i> Print Declaration
    </button>

    <div class="declaration-container">
        <!-- Header Section -->
        <div class="header">
            <div class="logo-section">
                <div class="logo">
                    <img src="{{ asset('images/logo.png') }}" alt="Delexes University College Logo" style="max-width: 80px; max-height: 80px; object-fit: contain;">
                </div>
                <div class="university-name">
                    <h1>DELEXES UNIVERSITY COLLEGE</h1>
                    <h2>Scholarship with Professionalism</h2>
                </div>
            </div>
            <div class="title-section">
                <h1>APPLICANT DECLARATION</h1>
                <h2>Summary of Applicant's Information from the On-line Application for Admission Form</h2>
            </div>
            <div class="photo-section">
                <div class="applicant-photo">
                    @if(!empty($uploadedFiles['passport_picture']))
                        <img src="{{ asset('storage/'.$uploadedFiles['passport_picture']) }}" alt="Applicant Photo">
                    @else
                        <div class="placeholder">Passport<br/>Photo</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Applicant Information Section -->
        <div class="info-section">
            <div class="info-grid">
                <div>
                    <div class="info-item">
                        <div class="info-label">Name:</div>
                        <div class="info-value">{{ strtoupper($prefill['full_name'] ?? $user->name ?? '') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Programme:</div>
                        <div class="info-value">
                            @php
                                $programs = [];
                                if (!empty($prefill['prog_eng'])) $programs[] = $prefill['prog_eng'];
                                if (!empty($prefill['prog_focis'])) $programs[] = $prefill['prog_focis'];
                                if (!empty($prefill['prog_business'])) $programs[] = $prefill['prog_business'];
                                if (!empty($prefill['pref1'])) $programs[] = $prefill['pref1'];
                                echo !empty($programs) ? strtoupper(implode(' / ', $programs)) : 'NOT SPECIFIED';
                            @endphp
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Nationality:</div>
                        <div class="info-value">{{ strtoupper($prefill['nationality'] ?? 'NOT SPECIFIED') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Entry Qualification:</div>
                        <div class="info-value">
                            @php
                                $qualifications = [];
                                if (!empty($prefill['entry_wassce'])) $qualifications[] = 'WASSCE';
                                if (!empty($prefill['entry_sssce'])) $qualifications[] = 'SSSCE';
                                if (!empty($prefill['entry_ib'])) $qualifications[] = 'International Baccalaureate';
                                if (!empty($prefill['entry_transfer'])) $qualifications[] = 'Transfer';
                                if (!empty($prefill['entry_other'])) $qualifications[] = $prefill['other_entry_detail'] ?? 'Other';
                                echo !empty($qualifications) ? strtoupper(implode(' / ', $qualifications)) : 'NOT SPECIFIED';
                            @endphp
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Previous Institution:</div>
                        <div class="info-value">
                            @if(!empty($prefill['institutions']) && is_array($prefill['institutions']) && count($prefill['institutions']) > 0)
                                {{ strtoupper($prefill['institutions'][0]['name'] ?? 'NOT SPECIFIED') }}
                            @else
                                NOT SPECIFIED
                            @endif
                        </div>
                    </div>
                </div>
                <div>
                    <div class="info-item">
                        <div class="info-label">Applicant ID:</div>
                        <div class="info-value">{{ $application->application_number ?? 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Session:</div>
                        <div class="info-value">{{ strtoupper($prefill['preferred_session'] ?? 'NOT SPECIFIED') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Date of Birth:</div>
                        <div class="info-value">
                            @if(!empty($prefill['dob']))
                                {{ \Carbon\Carbon::parse($prefill['dob'])->format('jS F, Y') }}
                            @else
                                NOT SPECIFIED
                            @endif
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Intake:</div>
                        <div class="info-value">{{ strtoupper($prefill['intake_option'] ?? 'NOT SPECIFIED') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Campus:</div>
                        <div class="info-value">{{ strtoupper($prefill['preferred_campus'] ?? 'NOT SPECIFIED') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Educational Sittings Section -->
        @if(isset($examRecords) && $examRecords->count() > 0)
        <div class="info-section">
            <h3 style="color: #1a237e; margin-bottom: 15px; font-size: 13pt;">Educational Sittings</h3>
            <table class="exam-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 20%;">Sittings</th>
                        <th style="width: 20%;">Index Number</th>
                        <th style="width: 15%;">Exam Month</th>
                        <th style="width: 15%;">Exam Year</th>
                        <th style="width: 25%;">Country of Examination</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($examRecords as $index => $record)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $record->sitting_exam ?? 'N/A' }}</td>
                        <td>{{ $record->index_number ?? 'N/A' }}</td>
                        <td>
                            @if(!empty($record->sitting_exam))
                                @php
                                    $sitting = strtolower($record->sitting_exam);
                                    if (strpos($sitting, 'may') !== false || strpos($sitting, 'june') !== false) {
                                        echo 'May/June';
                                    } elseif (strpos($sitting, 'nov') !== false) {
                                        echo 'November';
                                    } else {
                                        echo ucfirst($record->sitting_exam);
                                    }
                                @endphp
                            @else
                                N/A
                            @endif
                        </td>
                        <td>{{ $record->year ?? 'N/A' }}</td>
                        <td>{{ strtoupper($prefill['nationality'] ?? 'GHANA') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Declaration Section -->
        <div class="declaration-text">
            <p>
                I, <strong>{{ strtoupper($prefill['full_name'] ?? $user->name ?? 'APPLICANT') }}</strong>, hereby declare that the information provided by me on the Online Application Form for admission including my bio-data and entry qualification(s) as summarised and reproduced above are authentic and reflect my true records. I further declare that I will bear any consequences for any invalid information provided.
            </p>
        </div>

        <!-- Important Notes Section -->
        <div class="notes-section">
            <h3>Take note of the following:</h3>
            <ol>
                <li>That the application will not be valid if the above declaration is not signed.</li>
                <li>That if the declaration proves to be false, the application shall be rejected and that if the false information is detected after admission, the student shall be dismissed.</li>
                <li>That the endorsed copy of this applicant's declaration slip must be submitted at the point of registration together with all related documents such as certified copies of certificate(s), transcripts, birth certificate, etc.</li>
            </ol>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">
                    <strong>Applicant's Signature</strong>
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-line">
                    <strong>Date</strong>
                </div>
            </div>
        </div>

        <!-- Barcode Section -->
        <div class="barcode">
            <div class="barcode-placeholder">
                {{ str_pad($application->application_number ?? '00000000', 8, '0', STR_PAD_LEFT) }}
            </div>
        </div>
    </div>

    <script>
        // Auto-print when page loads (optional - remove if you don't want auto-print)
        // window.onload = function() {
        //     window.print();
        // }
    </script>
</body>
</html>

