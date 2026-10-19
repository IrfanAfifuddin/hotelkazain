<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class SalesReportExportService
{
    /**
     * Generate binary content of a valid OpenXML Excel (.xlsx) file.
     */
    public function generate(Collection $bookings, float|int $totalRevenue, ?string $startDate = null, ?string $endDate = null): string
    {
        Carbon::setLocale('id');

        $periodeLabel = 'Semua Periode';
        if ($startDate && $endDate) {
            $periodeLabel = Carbon::parse($startDate)->translatedFormat('d F Y') . ' – ' . Carbon::parse($endDate)->translatedFormat('d F Y');
        } elseif ($startDate) {
            $periodeLabel = 'Sejak ' . Carbon::parse($startDate)->translatedFormat('d F Y');
        } elseif ($endDate) {
            $periodeLabel = 'Sampai ' . Carbon::parse($endDate)->translatedFormat('d F Y');
        }

        $printedAt = now()->translatedFormat('d F Y H:i');

        $sheetXml = $this->buildSheetXml($bookings, (float) $totalRevenue, $periodeLabel, $printedAt);
        $stylesXml = $this->buildStylesXml();
        $workbookXml = $this->buildWorkbookXml();
        $wbRelsXml = $this->buildWorkbookRelsXml();
        $relsXml = $this->buildRelsXml();
        $contentTypesXml = $this->buildContentTypesXml();

        $files = [
            '[Content_Types].xml' => $contentTypesXml,
            '_rels/.rels' => $relsXml,
            'xl/_rels/workbook.xml.rels' => $wbRelsXml,
            'xl/workbook.xml' => $workbookXml,
            'xl/styles.xml' => $stylesXml,
            'xl/worksheets/sheet1.xml' => $sheetXml,
        ];

        return $this->createZip($files);
    }

    /**
     * Build worksheet XML content.
     */
    private function buildSheetXml(Collection $bookings, float $totalRevenue, string $periodeLabel, string $printedAt): string
    {
        $hasData = $bookings->isNotEmpty();
        $count = $bookings->count();

        $sheetData = '';

        // Row 1: Title
        $sheetData .= '<row r="1" ht="28" customHeight="1">';
        $sheetData .= '<c r="A1" s="1" t="inlineStr"><is><t>' . $this->escape('LAPORAN PENJUALAN KAZAIN HOTEL') . '</t></is></c>';
        $sheetData .= '</row>';

        // Row 2: Spacer
        $sheetData .= '<row r="2" ht="10" customHeight="1"/>';

        // Row 3: Periode
        $sheetData .= '<row r="3" ht="20" customHeight="1">';
        $sheetData .= '<c r="A3" s="2" t="inlineStr"><is><t>Periode Laporan</t></is></c>';
        $sheetData .= '<c r="B3" s="3" t="inlineStr"><is><t>: ' . $this->escape($periodeLabel) . '</t></is></c>';
        $sheetData .= '</row>';

        // Row 4: Jumlah Transaksi
        $sheetData .= '<row r="4" ht="20" customHeight="1">';
        $sheetData .= '<c r="A4" s="2" t="inlineStr"><is><t>Jumlah Transaksi Sukses</t></is></c>';
        $sheetData .= '<c r="B4" s="3" t="inlineStr"><is><t>: ' . number_format($count, 0, ',', '.') . ' transaksi</t></is></c>';
        $sheetData .= '</row>';

        // Row 5: Total Pendapatan
        $sheetData .= '<row r="5" ht="20" customHeight="1">';
        $sheetData .= '<c r="A5" s="2" t="inlineStr"><is><t>Total Pendapatan</t></is></c>';
        $sheetData .= '<c r="B5" s="3" t="inlineStr"><is><t>: Rp ' . number_format($totalRevenue, 0, ',', '.') . '</t></is></c>';
        $sheetData .= '</row>';

        // Row 6: Tanggal Unduh
        $sheetData .= '<row r="6" ht="20" customHeight="1">';
        $sheetData .= '<c r="A6" s="2" t="inlineStr"><is><t>Tanggal Unduh</t></is></c>';
        $sheetData .= '<c r="B6" s="3" t="inlineStr"><is><t>: ' . $this->escape($printedAt) . '</t></is></c>';
        $sheetData .= '</row>';

        // Row 7: Spacer
        $sheetData .= '<row r="7" ht="12" customHeight="1"/>';

        // Row 8: Table Header
        $sheetData .= '<row r="8" ht="24" customHeight="1">';
        $sheetData .= '<c r="A8" s="4" t="inlineStr"><is><t>No</t></is></c>';
        $sheetData .= '<c r="B8" s="4" t="inlineStr"><is><t>Booking Code</t></is></c>';
        $sheetData .= '<c r="C8" s="4" t="inlineStr"><is><t>Nama Customer</t></is></c>';
        $sheetData .= '<c r="D8" s="4" t="inlineStr"><is><t>Kamar</t></is></c>';
        $sheetData .= '<c r="E8" s="4" t="inlineStr"><is><t>Metode Bayar</t></is></c>';
        $sheetData .= '<c r="F8" s="4" t="inlineStr"><is><t>Waktu Transaksi</t></is></c>';
        $sheetData .= '<c r="G8" s="4" t="inlineStr"><is><t>Nominal</t></is></c>';
        $sheetData .= '</row>';

        $mergeCells = [
            '<mergeCell ref="A1:G1"/>',
        ];

        if ($hasData) {
            $rowNum = 9;
            foreach ($bookings as $index => $booking) {
                $no = $index + 1;
                $code = $booking->booking_code ?? '-';
                $customer = $booking->customer->customer_name ?? '-';
                $room = 'Kamar ' . ($booking->room->room_number ?? '-');
                $method = $booking->payment_method ?? 'Cash';

                $timeRaw = $booking->payment_time ?? $booking->created_at;
                $time = $timeRaw ? Carbon::parse($timeRaw)->translatedFormat('d M Y H:i') : '-';
                $nominal = (float) ($booking->total_price ?? 0);

                $sheetData .= '<row r="' . $rowNum . '" ht="20" customHeight="1">';
                $sheetData .= '<c r="A' . $rowNum . '" s="6" t="inlineStr"><is><t>' . $no . '</t></is></c>';
                $sheetData .= '<c r="B' . $rowNum . '" s="6" t="inlineStr"><is><t>' . $this->escape($code) . '</t></is></c>';
                $sheetData .= '<c r="C' . $rowNum . '" s="5" t="inlineStr"><is><t>' . $this->escape($customer) . '</t></is></c>';
                $sheetData .= '<c r="D' . $rowNum . '" s="6" t="inlineStr"><is><t>' . $this->escape($room) . '</t></is></c>';
                $sheetData .= '<c r="E' . $rowNum . '" s="6" t="inlineStr"><is><t>' . $this->escape($method) . '</t></is></c>';
                $sheetData .= '<c r="F' . $rowNum . '" s="6" t="inlineStr"><is><t>' . $this->escape($time) . '</t></is></c>';
                $sheetData .= '<c r="G' . $rowNum . '" s="7"><v>' . $nominal . '</v></c>';
                $sheetData .= '</row>';

                $rowNum++;
            }

            $totalRow = $rowNum;
            $dataEndRow = $rowNum - 1;

            $sheetData .= '<row r="' . $totalRow . '" ht="24" customHeight="1">';
            $sheetData .= '<c r="A' . $totalRow . '" s="8" t="inlineStr"><is><t>TOTAL PENDAPATAN</t></is></c>';
            $sheetData .= '<c r="B' . $totalRow . '" s="8"/>';
            $sheetData .= '<c r="C' . $totalRow . '" s="8"/>';
            $sheetData .= '<c r="D' . $totalRow . '" s="8"/>';
            $sheetData .= '<c r="E' . $totalRow . '" s="8"/>';
            $sheetData .= '<c r="F' . $totalRow . '" s="8"/>';
            $sheetData .= '<c r="G' . $totalRow . '" s="9"><f>SUM(G9:G' . $dataEndRow . ')</f><v>' . $totalRevenue . '</v></c>';
            $sheetData .= '</row>';

            $mergeCells[] = '<mergeCell ref="A' . $totalRow . ':F' . $totalRow . '"/>';
            $maxRow = $totalRow;
        } else {
            // Empty data row
            $sheetData .= '<row r="9" ht="24" customHeight="1">';
            $sheetData .= '<c r="A9" s="5" t="inlineStr"><is><t>Tidak ada data transaksi pada rentang tanggal tersebut.</t></is></c>';
            $sheetData .= '<c r="B9" s="5"/>';
            $sheetData .= '<c r="C9" s="5"/>';
            $sheetData .= '<c r="D9" s="5"/>';
            $sheetData .= '<c r="E9" s="5"/>';
            $sheetData .= '<c r="F9" s="5"/>';
            $sheetData .= '<c r="G9" s="5"/>';
            $sheetData .= '</row>';

            // Total row = 0
            $sheetData .= '<row r="10" ht="24" customHeight="1">';
            $sheetData .= '<c r="A10" s="8" t="inlineStr"><is><t>TOTAL PENDAPATAN</t></is></c>';
            $sheetData .= '<c r="B10" s="8"/>';
            $sheetData .= '<c r="C10" s="8"/>';
            $sheetData .= '<c r="D10" s="8"/>';
            $sheetData .= '<c r="E10" s="8"/>';
            $sheetData .= '<c r="F10" s="8"/>';
            $sheetData .= '<c r="G10" s="9"><v>0</v></c>';
            $sheetData .= '</row>';

            $mergeCells[] = '<mergeCell ref="A9:G9"/>';
            $mergeCells[] = '<mergeCell ref="A10:F10"/>';
            $maxRow = 10;
        }

        $mergeXml = '<mergeCells count="' . count($mergeCells) . '">' . implode('', $mergeCells) . '</mergeCells>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' .
            '<dimension ref="A1:G' . $maxRow . '"/>' .
            '<sheetViews>' .
            '<sheetView tabSelected="1" workbookViewId="0" showGridLines="1"/>' .
            '</sheetViews>' .
            '<sheetFormatPr defaultRowHeight="15"/>' .
            '<cols>' .
            '<col min="1" max="1" width="7" customWidth="1"/>' .
            '<col min="2" max="2" width="18" customWidth="1"/>' .
            '<col min="3" max="3" width="28" customWidth="1"/>' .
            '<col min="4" max="4" width="14" customWidth="1"/>' .
            '<col min="5" max="5" width="18" customWidth="1"/>' .
            '<col min="6" max="6" width="22" customWidth="1"/>' .
            '<col min="7" max="7" width="22" customWidth="1"/>' .
            '</cols>' .
            '<sheetData>' .
            $sheetData .
            '</sheetData>' .
            $mergeXml .
            '<pageMargins left="0.7" right="0.7" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>' .
            '</worksheet>';
    }

    /**
     * Build styles XML with typography, fills, borders, and number formats.
     */
    private function buildStylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' .
            '<numFmts count="1">' .
            '<numFmt numFmtId="164" formatCode="&quot;Rp &quot;#,##0"/>' .
            '</numFmts>' .
            '<fonts count="4">' .
            '<font><sz val="11"/><color rgb="FF1F2937"/><name val="Calibri"/></font>' .
            '<font><b/><sz val="16"/><color rgb="FF111827"/><name val="Calibri"/></font>' .
            '<font><b/><sz val="11"/><color rgb="FF111827"/><name val="Calibri"/></font>' .
            '<font><i/><sz val="10"/><color rgb="FF6B7280"/><name val="Calibri"/></font>' .
            '</fonts>' .
            '<fills count="4">' .
            '<fill><patternFill patternType="none"/></fill>' .
            '<fill><patternFill patternType="gray125"/></fill>' .
            '<fill><patternFill patternType="solid"><fgColor rgb="FFE5E7EB"/><bgColor indexed="64"/></patternFill></fill>' .
            '<fill><patternFill patternType="solid"><fgColor rgb="FFF3F4F6"/><bgColor indexed="64"/></patternFill></fill>' .
            '</fills>' .
            '<borders count="2">' .
            '<border><left/><right/><top/><bottom/><diagonal/></border>' .
            '<border>' .
            '<left style="thin"><color rgb="FFD1D5DB"/></left>' .
            '<right style="thin"><color rgb="FFD1D5DB"/></right>' .
            '<top style="thin"><color rgb="FFD1D5DB"/></top>' .
            '<bottom style="thin"><color rgb="FFD1D5DB"/></bottom>' .
            '<diagonal/>' .
            '</border>' .
            '</borders>' .
            '<cellStyleXfs count="1">' .
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>' .
            '</cellStyleXfs>' .
            '<cellXfs count="10">' .
            '<!-- 0: Default -->' .
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>' .
            '<!-- 1: Title (Bold 16pt) -->' .
            '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment vertical="center"/></xf>' .
            '<!-- 2: Meta Label (Bold 11pt) -->' .
            '<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment vertical="center"/></xf>' .
            '<!-- 3: Meta Value (Normal 11pt) -->' .
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment vertical="center"/></xf>' .
            '<!-- 4: Table Header (Bold 11pt, Fill Gray #E5E7EB, Border, Center) -->' .
            '<xf numFmtId="0" fontId="2" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>' .
            '<!-- 5: Table Cell Left (Normal 11pt, Border, Left) -->' .
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>' .
            '<!-- 6: Table Cell Center (Normal 11pt, Border, Center) -->' .
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' .
            '<!-- 7: Table Cell Currency (Normal 11pt, Border, Right, Rp #,##0) -->' .
            '<xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>' .
            '<!-- 8: Total Row Label (Bold 11pt, Fill Light Gray #F3F4F6, Border, Right) -->' .
            '<xf numFmtId="0" fontId="2" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>' .
            '<!-- 9: Total Row Currency (Bold 11pt, Fill Light Gray #F3F4F6, Border, Right, Rp #,##0) -->' .
            '<xf numFmtId="164" fontId="2" fillId="3" borderId="1" xfId="0" applyFont="1" applyNumberFormat="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>' .
            '</cellXfs>' .
            '</styleSheet>';
    }

    /**
     * Build workbook XML.
     */
    private function buildWorkbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' .
            '<sheets>' .
            '<sheet name="Laporan Penjualan" sheetId="1" r:id="rId1"/>' .
            '</sheets>' .
            '</workbook>';
    }

    /**
     * Build workbook relationships XML.
     */
    private function buildWorkbookRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>' .
            '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>' .
            '</Relationships>';
    }

    /**
     * Build package relationships XML.
     */
    private function buildRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' .
            '</Relationships>';
    }

    /**
     * Build content types XML.
     */
    private function buildContentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' .
            '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' .
            '<Default Extension="xml" ContentType="application/xml"/>' .
            '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' .
            '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' .
            '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' .
            '</Types>';
    }

    /**
     * Escape XML special characters.
     */
    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1, 'UTF-8');
    }

    /**
     * Create ZIP archive using native ZipArchive or fallback pure-PHP zip packager.
     *
     * @param array<string, string> $files [relative path => file content]
     */
    private function createZip(array $files): string
    {
        if (class_exists(\ZipArchive::class)) {
            $tempFile = tempnam(sys_get_temp_dir(), 'kazain_xlsx_');
            if ($tempFile !== false) {
                $zip = new \ZipArchive();
                if ($zip->open($tempFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                    foreach ($files as $path => $content) {
                        $zip->addFromString($path, $content);
                    }
                    $zip->close();
                    $data = file_get_contents($tempFile);
                    @unlink($tempFile);
                    if ($data !== false && strlen($data) > 0) {
                        return $data;
                    }
                }
            }
        }

        return $this->buildPureZip($files);
    }

    /**
     * Pure-PHP PKZIP 2.0 binary packager (deflate compressed).
     *
     * @param array<string, string> $files
     */
    private function buildPureZip(array $files): string
    {
        $data = '';
        $centralDir = '';
        $offset = 0;

        foreach ($files as $name => $content) {
            $uncompressedSize = strlen($content);
            $crc = crc32($content);
            $compressed = gzdeflate($content);
            $compressionMethod = 8; // Deflate
            $compressedSize = strlen($compressed);

            // Local file header (30 bytes + filename)
            $localHeader = "\x50\x4b\x03\x04" .
                "\x14\x00" .
                "\x00\x00" .
                pack('v', $compressionMethod) .
                "\x00\x00\x00\x00" .
                pack('V', $crc) .
                pack('V', $compressedSize) .
                pack('V', $uncompressedSize) .
                pack('v', strlen($name)) .
                "\x00\x00" .
                $name;

            $data .= $localHeader . $compressed;

            // Central directory record (46 bytes + filename)
            $cdEntry = "\x50\x4b\x01\x02" .
                "\x14\x00" .
                "\x14\x00" .
                "\x00\x00" .
                pack('v', $compressionMethod) .
                "\x00\x00\x00\x00" .
                pack('V', $crc) .
                pack('V', $compressedSize) .
                pack('V', $uncompressedSize) .
                pack('v', strlen($name)) .
                "\x00\x00" .
                "\x00\x00" .
                "\x00\x00" .
                "\x00\x00" .
                "\x00\x00\x00\x00" .
                pack('V', $offset) .
                $name;

            $centralDir .= $cdEntry;
            $offset = strlen($data);
        }

        $centralDirSize = strlen($centralDir);
        $centralDirOffset = strlen($data);
        $totalFiles = count($files);

        // End of central directory record (22 bytes)
        $eocd = "\x50\x4b\x05\x06" .
            "\x00\x00" .
            "\x00\x00" .
            pack('v', $totalFiles) .
            pack('v', $totalFiles) .
            pack('V', $centralDirSize) .
            pack('V', $centralDirOffset) .
            "\x00\x00";

        return $data . $centralDir . $eocd;
    }
}
