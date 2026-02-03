<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Monitoring Domain dan Subdomain</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 30px;
            font-size: 10pt;
            line-height: 1.5;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .header h2 {
            font-size: 12pt;
            font-weight: normal;
            margin-top: 5px;
            color: #555;
        }
        .header .meta {
            font-size: 9pt;
            color: #666;
            margin-top: 10px;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
            color: #2563eb;
        }
        .summary-grid {
            width: 100%;
            margin-bottom: 20px;
        }
        .summary-grid td {
            width: 25%;
            padding: 10px;
            text-align: center;
            vertical-align: top;
        }
        .summary-box {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            padding: 15px;
        }
        .summary-box .label {
            font-size: 9pt;
            color: #666;
            margin-bottom: 5px;
        }
        .summary-box .value {
            font-size: 14pt;
            font-weight: bold;
            color: #333;
        }
        .summary-box .value.red {
            color: #dc2626;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table.data-table th,
        table.data-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table.data-table th {
            background-color: #f3f4f6;
            font-weight: bold;
            font-size: 9pt;
        }
        table.data-table td {
            font-size: 9pt;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .progress-bar {
            background-color: #e5e7eb;
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
            display: inline-block;
            width: 80px;
            vertical-align: middle;
        }
        .progress-fill {
            height: 100%;
            border-radius: 4px;
        }
        .progress-fill.blue {
            background-color: #3b82f6;
        }
        .progress-fill.green {
            background-color: #22c55e;
        }
        .progress-fill.red {
            background-color: #ef4444;
        }
        .two-column {
            width: 100%;
        }
        .two-column td {
            width: 50%;
            vertical-align: top;
            padding-right: 15px;
        }
        .two-column td:last-child {
            padding-right: 0;
            padding-left: 15px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 8pt;
            color: #666;
            text-align: center;
        }
        .page-break {
            page-break-before: always;
        }
        .insight-box {
            background-color: #f8f9fa;
            border-left: 4px solid #3b82f6;
            padding: 12px;
            margin-bottom: 10px;
        }
        .insight-box.success {
            border-left-color: #22c55e;
        }
        .insight-box.warning {
            border-left-color: #f59e0b;
        }
        .insight-box.danger {
            border-left-color: #ef4444;
        }
        .insight-box.info {
            border-left-color: #3b82f6;
        }
        .insight-title {
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 5px;
            color: #333;
        }
        .insight-message {
            font-size: 9pt;
            color: #555;
            margin-bottom: 5px;
        }
        .insight-recommendation {
            font-size: 9pt;
            background-color: #eef2ff;
            padding: 8px;
            margin-top: 5px;
            border-radius: 3px;
        }
        .insight-recommendation strong {
            color: #4338ca;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Monitoring Domain dan Subdomain</h1>
        <h2>{{ $zoneName }}</h2>
        <div class="meta">
            Periode: {{ $monthName }}<br>
            Dokumen ini digenerate secara otomatis dari sistem E-Layanan DKISP (layanan.diskominfo.kaltaraprov.go.id) - Pemerintah Provinsi Kalimantan Utara<br>
            Digenerate: {{ $generatedAt }}
        </div>
    </div>

    <!-- Summary Section -->
    <div class="section">
        <div class="section-title">Ringkasan Traffic</div>
        <table class="summary-grid">
            <tr>
                <td>
                    <div class="summary-box">
                        <div class="label">Total Requests</div>
                        <div class="value">{{ number_format($trafficData['summary']['total_requests']) }}</div>
                    </div>
                </td>
                <td>
                    <div class="summary-box">
                        <div class="label">Total Bandwidth</div>
                        <div class="value">{{ formatBytes($trafficData['summary']['total_bandwidth']) }}</div>
                    </div>
                </td>
                <td>
                    <div class="summary-box">
                        <div class="label">Unique Visitors</div>
                        <div class="value">{{ number_format($trafficData['summary']['unique_visitors']) }}</div>
                    </div>
                </td>
                <td>
                    <div class="summary-box">
                        <div class="label">Security Events</div>
                        <div class="value red">{{ number_format($trafficData['security']['total_events']) }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Technical Information: Summary Metrics -->
        <div style="margin-top: 15px; padding: 12px; background-color: #f8f9fa; border-left: 4px solid #6366f1; border-radius: 3px;">
            <div style="font-weight: bold; font-size: 10pt; margin-bottom: 8px; color: #4338ca;">Informasi Teknis: Metrik Traffic</div>
            <table style="width: 100%; font-size: 9pt; color: #374151;">
                <tr>
                    <td style="padding: 4px 0; vertical-align: top; width: 150px;"><strong>Total Requests:</strong></td>
                    <td style="padding: 4px 0;">Jumlah total request HTTP/HTTPS yang diterima server melalui Cloudflare</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; vertical-align: top;"><strong>Total Bandwidth:</strong></td>
                    <td style="padding: 4px 0;">Total data yang ditransfer dari server ke pengunjung (download)</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; vertical-align: top;"><strong>Unique Visitors:</strong></td>
                    <td style="padding: 4px 0;">Jumlah pengunjung unik berdasarkan IP address yang berbeda</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; vertical-align: top;"><strong>Security Events:</strong></td>
                    <td style="padding: 4px 0;">Total kejadian keamanan yang terdeteksi oleh Cloudflare Firewall (termasuk block, challenge, allow, skip, dan log)</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Cache Statistics -->
    <div class="section">
        <div class="section-title">Statistik Cache</div>
        <table class="data-table">
            <tr>
                <th>Metrik</th>
                <th class="text-right">Nilai</th>
                <th class="text-right">Total</th>
                <th class="text-center">Rasio</th>
            </tr>
            <tr>
                <td>Cached Requests</td>
                <td class="text-right">{{ number_format($trafficData['summary']['cached_requests']) }}</td>
                <td class="text-right">{{ number_format($trafficData['summary']['total_requests']) }}</td>
                <td class="text-center">
                    @php
                        $cacheRatio = $trafficData['summary']['total_requests'] > 0
                            ? ($trafficData['summary']['cached_requests'] / $trafficData['summary']['total_requests']) * 100
                            : 0;
                    @endphp
                    {{ number_format($cacheRatio, 1) }}%
                </td>
            </tr>
            <tr>
                <td>Cached Bandwidth</td>
                <td class="text-right">{{ formatBytes($trafficData['summary']['cached_bandwidth']) }}</td>
                <td class="text-right">{{ formatBytes($trafficData['summary']['total_bandwidth']) }}</td>
                <td class="text-center">
                    @php
                        $bandwidthCacheRatio = $trafficData['summary']['total_bandwidth'] > 0
                            ? ($trafficData['summary']['cached_bandwidth'] / $trafficData['summary']['total_bandwidth']) * 100
                            : 0;
                    @endphp
                    {{ number_format($bandwidthCacheRatio, 1) }}%
                </td>
            </tr>
        </table>

        <!-- Technical Information: Cache -->
        <div style="margin-top: 15px; padding: 12px; background-color: #f8f9fa; border-left: 4px solid #22c55e; border-radius: 3px;">
            <div style="font-weight: bold; font-size: 10pt; margin-bottom: 8px; color: #15803d;">Informasi Teknis: Cache</div>
            <table style="width: 100%; font-size: 9pt; color: #374151;">
                <tr>
                    <td style="padding: 4px 0; vertical-align: top; width: 150px;"><strong>Cached Requests:</strong></td>
                    <td style="padding: 4px 0;">Request yang dilayani dari cache Cloudflare tanpa menghubungi server origin</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; vertical-align: top;"><strong>Cache Hit Ratio:</strong></td>
                    <td style="padding: 4px 0;">Persentase request yang berhasil dilayani dari cache. Semakin tinggi semakin baik (menghemat bandwidth dan mempercepat loading)</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; vertical-align: top;"><strong>Cached Bandwidth:</strong></td>
                    <td style="padding: 4px 0;">Total bandwidth yang dihemat karena konten dilayani dari cache CDN Cloudflare</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Security Events Summary -->
    @if($trafficData['security']['total_events'] > 0)
    <div class="section page-break">
        <div class="section-title">Ringkasan Security Events</div>
        <table class="two-column">
            <tr>
                <td>
                    <table class="data-table">
                        <tr>
                            <th colspan="2">Events by Action</th>
                        </tr>
                        @foreach(array_slice($trafficData['security']['by_action'], 0, 10, true) as $action => $count)
                        <tr>
                            <td>{{ ucfirst($action) }}</td>
                            <td class="text-right">{{ number_format($count) }}</td>
                        </tr>
                        @endforeach
                        <tr style="font-weight: bold; background-color: #f3f4f6;">
                            <td>Total</td>
                            <td class="text-right">{{ number_format($trafficData['security']['total_events']) }}</td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table class="data-table">
                        <tr>
                            <th colspan="2">Top Countries</th>
                        </tr>
                        @php
                            $countryNames = [
                                'ID' => 'Indonesia', 'US' => 'United States', 'CN' => 'China', 'IN' => 'India',
                                'SG' => 'Singapore', 'MY' => 'Malaysia', 'TH' => 'Thailand', 'VN' => 'Vietnam',
                                'PH' => 'Philippines', 'JP' => 'Japan', 'KR' => 'South Korea', 'AU' => 'Australia',
                                'RU' => 'Russia', 'DE' => 'Germany', 'GB' => 'United Kingdom', 'FR' => 'France',
                                'BR' => 'Brazil', 'CA' => 'Canada', 'NL' => 'Netherlands', 'IT' => 'Italy',
                                'ES' => 'Spain', 'TR' => 'Turkey', 'PL' => 'Poland', 'UA' => 'Ukraine',
                                'BD' => 'Bangladesh', 'PK' => 'Pakistan', 'XX' => 'Unknown'
                            ];
                        @endphp
                        @foreach(array_slice($trafficData['security']['by_country'], 0, 10, true) as $country => $count)
                        <tr>
                            <td>{{ $countryNames[$country] ?? $country }} ({{ $country }})</td>
                            <td class="text-right">{{ number_format($count) }}</td>
                        </tr>
                        @endforeach
                    </table>
                </td>
            </tr>
        </table>

        <!-- Technical Information: Security Events -->
        <div style="margin-top: 15px; padding: 12px; background-color: #f8f9fa; border-left: 4px solid #3b82f6; border-radius: 3px;">
            <div style="font-weight: bold; font-size: 10pt; margin-bottom: 8px; color: #1e40af;">Informasi Teknis: Security Events</div>
            <table style="width: 100%; font-size: 9pt; color: #374151;">
                <tr>
                    <td style="padding: 4px 0; vertical-align: top; width: 150px;"><strong>Block:</strong></td>
                    <td style="padding: 4px 0;">Request diblokir sepenuhnya karena terdeteksi sebagai ancaman</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; vertical-align: top;"><strong>Challenge:</strong></td>
                    <td style="padding: 4px 0;">User diminta verifikasi CAPTCHA untuk membuktikan bukan bot</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; vertical-align: top;"><strong>JS Challenge:</strong></td>
                    <td style="padding: 4px 0;">User diminta verifikasi JavaScript (otomatis di browser)</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; vertical-align: top;"><strong>Managed Challenge:</strong></td>
                    <td style="padding: 4px 0;">Cloudflare menentukan jenis tantangan secara otomatis</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; vertical-align: top;"><strong>Skip:</strong></td>
                    <td style="padding: 4px 0;">Request diizinkan melewati firewall berdasarkan rule</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; vertical-align: top;"><strong>Allow:</strong></td>
                    <td style="padding: 4px 0;">Request diizinkan secara eksplisit melalui whitelist</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; vertical-align: top;"><strong>Log:</strong></td>
                    <td style="padding: 4px 0;">Event dicatat untuk monitoring tapi tidak diblokir</td>
                </tr>
            </table>
        </div>
    </div>
    @endif

    <!-- Traffic per Subdomain -->
    @if(!empty($trafficData['hostnames']))
    <div class="section page-break">
        <div class="section-title">Traffic per Subdomain</div>
        <table class="data-table">
            <tr>
                <th style="width: 5%;">No</th>
                <th>Subdomain</th>
                <th class="text-right" style="width: 15%;">Requests</th>
                <th class="text-center" style="width: 15%;">Persentase</th>
            </tr>
            @php $totalHostnameRequests = array_sum($trafficData['hostnames']); @endphp
            @foreach(array_slice($trafficData['hostnames'], 0, 50, true) as $hostname => $requests)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $hostname }}</td>
                <td class="text-right">{{ number_format($requests) }}</td>
                <td class="text-center">
                    @php $percentage = $totalHostnameRequests > 0 ? ($requests / $totalHostnameRequests) * 100 : 0; @endphp
                    {{ number_format($percentage, 1) }}%
                </td>
            </tr>
            @endforeach
            @if(count($trafficData['hostnames']) > 50)
            <tr>
                <td colspan="4" class="text-center" style="font-style: italic; color: #666;">
                    ... dan {{ count($trafficData['hostnames']) - 50 }} subdomain lainnya
                </td>
            </tr>
            @endif
        </table>
    </div>
    @endif

    <!-- Daily Traffic Summary -->
    @if(!empty($trafficData['daily']))
    <div class="section">
        <div class="section-title">Traffic Harian</div>
        <table class="data-table">
            <tr>
                <th>Tanggal</th>
                <th class="text-right">Requests</th>
                <th class="text-right">Bandwidth</th>
                <th class="text-right">Page Views</th>
                <th class="text-right">Unique Visitors</th>
            </tr>
            @foreach($trafficData['daily'] as $date => $data)
            <tr>
                <td>{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</td>
                <td class="text-right">{{ number_format($data['requests']) }}</td>
                <td class="text-right">{{ formatBytes($data['bandwidth']) }}</td>
                <td class="text-right">{{ number_format($data['page_views']) }}</td>
                <td class="text-right">{{ number_format($data['unique_visitors']) }}</td>
            </tr>
            @endforeach
        </table>
    </div>
    @endif

    <!-- Insights & Recommendations -->
    @php
        $insightsList = $trafficData['insights']['insights'] ?? [];
        $recommendationsList = $trafficData['insights']['recommendations'] ?? [];
    @endphp
    @if(!empty($insightsList))
    <div class="section page-break">
        <div class="section-title">Analisis & Rekomendasi</div>
        @foreach($insightsList as $insight)
        @php $insightType = $insight['type'] ?? 'info'; @endphp
        <div class="insight-box {{ $insightType }}">
            <div class="insight-title">{{ $insight['title'] }}</div>
            <div class="insight-message">{{ $insight['message'] }}</div>
        </div>
        @endforeach

        @if(!empty($recommendationsList))
        <div style="margin-top: 15px; padding: 12px; background-color: #eef2ff; border-radius: 5px;">
            <div style="font-weight: bold; font-size: 10pt; margin-bottom: 8px; color: #4338ca;">Rekomendasi Tindakan:</div>
            <ul style="margin: 0; padding-left: 20px; font-size: 9pt; color: #333;">
                @foreach($recommendationsList as $recommendation)
                <li style="margin-bottom: 5px;">{{ $recommendation }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <p style="font-size: 8pt; color: #666; margin-top: 15px; font-style: italic;">
            * Analisis ini digenerate secara otomatis berdasarkan data traffic periode {{ $monthName }}.
        </p>
    </div>
    @endif

    <div class="footer">
        <p>Dokumen ini digenerate secara otomatis dari sistem E-Layanan DKISP (layanan.diskominfo.kaltaraprov.go.id) - Pemerintah Provinsi Kalimantan Utara</p>
        <p>Data bersumber dari Cloudflare Analytics API</p>
    </div>
</body>
</html>

@php
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}
@endphp
