<?php
declare(strict_types=1);
/** Read the first sheet of an .xlsx file (or a .csv) into rows of strings. */
function read_table(string $path, string $name): array {
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if ($ext === 'csv' || $ext === 'txt') {
        $rows = []; $h = fopen($path, 'r');
        $first = fgets($h); rewind($h);
        $sep = substr_count((string)$first, ';') > substr_count((string)$first, ',') ? ';' : ',';
        while (($r = fgetcsv($h, 0, $sep, '"', '\\')) !== false) $rows[] = array_map(fn($v) => trim(preg_replace('/^\xEF\xBB\xBF/', '', (string)$v)), $r);
        fclose($h);
        return $rows;
    }
    if ($ext !== 'xlsx') throw new RuntimeException('Please upload an .xlsx or .csv file. Old .xls files: open in Excel and Save As .xlsx.');
    $z = new ZipArchive();
    if ($z->open($path) !== true) throw new RuntimeException('This Excel file could not be opened.');
    $shared = [];
    if (($x = $z->getFromName('xl/sharedStrings.xml')) !== false) {
        $sx = simplexml_load_string($x, 'SimpleXMLElement', LIBXML_NONET);
        foreach ($sx->si as $si) {
            $t = '';
            if (isset($si->t)) $t = (string)$si->t;
            foreach ($si->r as $r) $t .= (string)$r->t;
            $shared[] = $t;
        }
    }
    // first sheet in workbook order
    $sheet = 'xl/worksheets/sheet1.xml';
    $wb = $z->getFromName('xl/workbook.xml'); $rels = $z->getFromName('xl/_rels/workbook.xml.rels');
    if ($wb && $rels && preg_match('/<sheet [^>]*r:id="([^"]+)"/', $wb, $m) && preg_match('/Id="' . preg_quote($m[1], '/') . '"[^>]*Target="([^"]+)"|Target="([^"]+)"[^>]*Id="' . preg_quote($m[1], '/') . '"/', $rels, $t)) {
        $tg = ($t[1] ?? '') ?: ($t[2] ?? '');
        $sheet = str_starts_with($tg, '/') ? ltrim($tg, '/') : 'xl/' . $tg;
    }
    $x = $z->getFromName($sheet); $z->close();
    if ($x === false) throw new RuntimeException('The first sheet could not be read.');
    $sx = simplexml_load_string($x, 'SimpleXMLElement', LIBXML_NONET | LIBXML_COMPACT | LIBXML_PARSEHUGE);
    $rows = [];
    foreach ($sx->sheetData->row as $row) {
        $r = [];
        foreach ($row->c as $c) {
            $ref = (string)$c['r']; preg_match('/^([A-Z]+)/', $ref, $mm);
            $col = 0; foreach (str_split($mm[1] ?? 'A') as $ch) $col = $col * 26 + ord($ch) - 64;
            $type = (string)$c['t']; $v = isset($c->v) ? (string)$c->v : '';
            if ($type === 's') $v = $shared[(int)$v] ?? '';
            elseif ($type === 'inlineStr') $v = (string)($c->is->t ?? '');
            $r[$col - 1] = trim($v);
        }
        if ($r) { $max = max(array_keys($r)); $line = array_fill(0, $max + 1, ''); foreach ($r as $k => $v) $line[$k] = $v; $rows[(int)$row['r'] - 1] = $line; }
    }
    ksort($rows);
    return array_values($rows);
}
/** Find the header row and which column holds what. */
function map_columns(array $rows): array {
    $want = [
        'lrn' => '/\blrn\b|learner reference/i', 'last' => '/last\s*name|surname|apelyido|family name/i', 'first' => '/first\s*name|given\s*name|pangalan/i',
        'middle' => '/middle\s*name|m\.?i\.?$|gitnang/i', 'name' => '/name of learner|learner\'?s? name|full\s*name|^name$|^names?$/i',
        'sex' => '/^sex|gender|kasarian/i', 'birth' => '/birth|kapanganakan|dob/i', 'grade' => '/level|grade|program|enrol/i',
    ];
    for ($i = 0; $i < min(25, count($rows)); $i++) {
        $map = [];
        foreach ($rows[$i] as $c => $v) {
            $v = trim((string)$v); if ($v === '') continue;
            foreach ($want as $k => $re) if (!isset($map[$k]) && preg_match($re, $v)) { $map[$k] = $c; break; }
        }
        if (isset($map['last']) || isset($map['name'])) return [$i, $map];
    }
    return [-1, []];
}
function excel_date(string $v): ?string {
    $v = trim($v); if ($v === '') return null;
    if (is_numeric($v) && $v > 1000 && $v < 80000) return gmdate('Y-m-d', (int)(((float)$v - 25569) * 86400));
    foreach (['Y-m-d', 'm/d/Y', 'n/j/Y', 'd/m/Y', 'F j, Y', 'M j, Y', 'm-d-Y'] as $f) { $d = DateTime::createFromFormat('!' . $f, $v); if ($d) return $d->format('Y-m-d'); }
    $t = strtotime($v); return $t ? date('Y-m-d', $t) : null;
}
