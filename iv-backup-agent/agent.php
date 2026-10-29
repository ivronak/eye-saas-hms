<?php
/**
 * IV Backup Agent v1.0.11
 * Drop this folder on any PHP server. Protect config.php from public access.
 */

header('X-IV-Backup-Agent: 1.0.11');

$configFile = __DIR__ . '/config.php';
if (! file_exists($configFile)) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Missing config.php — copy config.sample.php']);
    exit;
}

$config = require $configFile;

// Read JSON body early (shared hosts often strip Authorization headers)
$rawBody = file_get_contents('php://input') ?: '';
$input = json_decode($rawBody, true);
if (! is_array($input)) {
    $input = [];
}

// Fallback: form-encoded / multipart
if ($input === [] && ! empty($_POST) && is_array($_POST)) {
    $input = $_POST;
}

$provided = '';
$auth = $_SERVER['HTTP_AUTHORIZATION']
    ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
    ?? '';

if (is_string($auth) && str_starts_with($auth, 'Bearer ')) {
    $provided = substr($auth, 7);
}

if ($provided === '') {
    $provided = (string) (
        $_SERVER['HTTP_X_IV_TOKEN']
        ?? $_SERVER['REDIRECT_HTTP_X_IV_TOKEN']
        ?? ($input['token'] ?? '')
        ?? ($_GET['token'] ?? '')
        ?? ''
    );
}

// Also check getallheaders() when available
if ($provided === '' && function_exists('getallheaders')) {
    foreach (getallheaders() as $name => $value) {
        $key = strtolower((string) $name);
        if ($key === 'authorization' && str_starts_with((string) $value, 'Bearer ')) {
            $provided = substr((string) $value, 7);
            break;
        }
        if ($key === 'x-iv-token') {
            $provided = (string) $value;
            break;
        }
    }
}

$expectedTokens = [];
if (! empty($config['tokens']) && is_array($config['tokens'])) {
    foreach ($config['tokens'] as $t) {
        $t = trim((string) $t);
        if ($t !== '') {
            $expectedTokens[] = $t;
        }
    }
}
$single = trim((string) ($config['token'] ?? ''));
if ($single !== '') {
    $expectedTokens[] = $single;
}
$expectedTokens = array_values(array_unique($expectedTokens));

$provided = trim((string) $provided);
$authorized = false;
if ($provided !== '' && $expectedTokens !== []) {
    foreach ($expectedTokens as $expected) {
        if (hash_equals($expected, $provided)) {
            $authorized = true;
            break;
        }
    }
}

if (! $authorized) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode([
        'ok' => false,
        'error' => 'Unauthorized',
        'hint' => 'Token mismatch. Common agent download (badha projects) upload karo, athva project Ready Agent.',
    ]);
    exit;
}

$tmpRoot = realpath(__DIR__ . '/storage/tmp') ?: (__DIR__ . '/storage/tmp');
if (! is_dir($tmpRoot)) {
    @mkdir($tmpRoot, 0755, true);
    $tmpRoot = realpath($tmpRoot) ?: $tmpRoot;
}

// Prefer query action — shared hosts / WAF sometimes strip JSON bodies with password fields
$action = (string) ($_GET['action'] ?? $input['action'] ?? '');
if ($action === '') {
    $action = 'ping';
}

$json = function (array $payload, int $code = 200) {
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }
    if (! headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($code);
    }
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
};

// Fatal / timeout pe empty 500 nathi — JSON error moklo
register_shutdown_function(function () use (&$json) {
    $err = error_get_last();
    if (! $err) {
        return;
    }
    $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
    if (! in_array($err['type'], $fatalTypes, true)) {
        return;
    }
    if (headers_sent()) {
        return;
    }
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok' => false,
        'error' => 'Agent PHP fatal/timeout',
        'detail' => ($err['message'] ?? 'unknown').' in '.basename((string) ($err['file'] ?? '')).':'.($err['line'] ?? 0),
        'hint' => 'Hostinger timeout/memory. DB host localhost try karo; heavy folders exclude karo.',
    ], JSON_UNESCAPED_SLASHES);
});

if ($action === 'ping') {
    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? (string) $_SERVER['DOCUMENT_ROOT'] : '';
    $suggested = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
    $parent = dirname(__DIR__);
    $laravelSibling = is_file($parent.'/artisan') || is_dir($parent.'/vendor/laravel');

    $json([
        'ok' => true,
        'agent' => 'iv-backup-agent',
        'version' => '1.0.11',
        'php' => PHP_VERSION,
        'zip' => class_exists('ZipArchive'),
        'mysqldump' => trim((string) @shell_exec('command -v mysqldump 2>/dev/null') ?: '') !== '',
        'time' => date('c'),
        'tmp_writable' => is_writable($tmpRoot),
        'document_root' => $docRoot,
        'agent_dir' => __DIR__,
        'script' => (string) ($_SERVER['SCRIPT_NAME'] ?? ''),
        'suggested_folder' => $suggested,
        'scenario' => [
            'laravel_sibling' => $laravelSibling,
            'entry' => basename((string) ($_SERVER['SCRIPT_FILENAME'] ?? __FILE__)),
        ],
        'hint' => 'Folder AUTO = agent parent. Laravel 404 on agent.php → use same-folder index.php URL.',
    ]);
}

if ($action === 'download') {
    // Prefer short basename via query (hosts often strip long absolute paths in JSON body)
    $file = (string) ($_GET['file'] ?? $input['file'] ?? '');
    $path = (string) ($input['path'] ?? $_GET['path'] ?? '');

    $tmpReal = realpath($tmpRoot);
    $candidate = null;

    if ($file !== '') {
        $base = basename(str_replace(['\\', "\0"], ['/', ''], $file));
        if ($base !== '' && $base !== '.' && $base !== '..') {
            $candidate = ($tmpReal ?: $tmpRoot) . DIRECTORY_SEPARATOR . $base;
        }
    } elseif ($path !== '') {
        // Backward compat: full path, or treat as basename
        $candidate = $path;
        if (! is_file($candidate)) {
            $candidate = ($tmpReal ?: $tmpRoot) . DIRECTORY_SEPARATOR . basename($path);
        }
    }

    $real = $candidate ? realpath($candidate) : false;
    if (! $real || ! $tmpReal || ! str_starts_with(str_replace('\\', '/', $real), str_replace('\\', '/', $tmpReal)) || ! is_file($real)) {
        $json([
            'ok' => false,
            'error' => 'File not found or outside temp folder',
            'hint' => 'Re-upload agent v1.0.5+. Download uses ?file=basename',
            'file' => $file,
            'tmp' => $tmpReal,
        ], 404);
    }

    while (ob_get_level() > 0) {
        @ob_end_clean();
    }
    header('Content-Type: application/octet-stream');
    header('Content-Length: ' . (string) filesize($real));
    header('Content-Disposition: attachment; filename="' . basename($real) . '"');
    // Stream in chunks — large backups must not load fully into memory
    $fh = fopen($real, 'rb');
    if ($fh) {
        while (! feof($fh)) {
            echo fread($fh, 1024 * 1024);
            flush();
        }
        fclose($fh);
    } else {
        readfile($real);
    }
    exit;
}

if ($action === 'cleanup') {
    $paths = $input['paths'] ?? ($_GET['files'] ?? []);
    if (is_string($paths)) {
        $paths = array_filter(explode(',', $paths));
    }
    $tmpReal = realpath($tmpRoot);
    $deleted = [];

    foreach ((array) $paths as $path) {
        $base = basename(str_replace(['\\', "\0"], ['/', ''], (string) $path));
        if ($base === '' || $base === '.' || $base === '..' || ! $tmpReal) {
            continue;
        }
        $candidate = $tmpReal . DIRECTORY_SEPARATOR . $base;
        $real = realpath($candidate);
        if ($real && str_starts_with(str_replace('\\', '/', $real), str_replace('\\', '/', $tmpReal)) && is_file($real)) {
            @unlink($real);
            $deleted[] = $base;
        }
    }

    $json(['ok' => true, 'deleted' => $deleted]);
}

if (! function_exists('iv_resolve_folder')) {
    /**
     * Resolve absolute/relative paths for Hostinger, cPanel, VPS.
     */
    function iv_resolve_folder(string $folder, string $agentDir): array
    {
        $folder = trim(str_replace('\\', '/', $folder));
        $agentDir = rtrim(str_replace('\\', '/', $agentDir), '/');
        $docRoot = isset($_SERVER['DOCUMENT_ROOT'])
            ? rtrim(str_replace('\\', '/', (string) $_SERVER['DOCUMENT_ROOT']), '/')
            : '';
        $home = isset($_SERVER['HOME'])
            ? rtrim(str_replace('\\', '/', (string) $_SERVER['HOME']), '/')
            : '';
        $agentParent = dirname($agentDir);

        $candidates = [];
        $add = function (string $path) use (&$candidates) {
            $path = rtrim(str_replace('\\', '/', $path), '/');
            if ($path !== '' && ! in_array($path, $candidates, true)) {
                $candidates[] = $path;
            }
        };

        if ($folder !== '') {
            $add($folder);
        }

        if ($docRoot !== '') {
            $add($docRoot.'/'.ltrim($folder, '/'));
            if (str_starts_with($folder, 'public_html/')) {
                $rest = substr($folder, strlen('public_html/'));
                $add($docRoot.'/'.$rest);
                $add(dirname($docRoot).'/'.$folder);
            }
            $add($docRoot.'/'.basename($folder));
        }

        if ($home !== '') {
            $add($home.'/'.ltrim($folder, '/'));
            $stripped = preg_replace('#^public_html/#', '', $folder) ?: $folder;
            $add($home.'/public_html/'.ltrim((string) $stripped, '/'));
        }

        $add($agentParent.'/'.ltrim($folder, '/'));
        $add(dirname($agentParent).'/'.ltrim($folder, '/'));

        // Use agent parent (.../ikon) only when it matches the requested folder name
        $norm = (string) preg_replace('#^public_html/#', '', $folder);
        if (
            $folder !== ''
            && (
                basename($agentParent) === basename($folder)
                || str_ends_with($agentParent, '/'.$folder)
                || str_ends_with($agentParent, '/'.$norm)
            )
        ) {
            $add($agentParent);
        }

        $tried = [];
        foreach ($candidates as $c) {
            $tried[] = $c;
            $real = realpath($c);
            if ($real !== false && is_dir($real)) {
                return ['path' => $real, 'tried' => $tried];
            }
        }

        return ['path' => null, 'tried' => $tried];
    }
}

if (! function_exists('iv_parse_db_config')) {
    function iv_parse_db_config(array $input): array
    {
        $db = [];

        $headerCfg = (string) (
            $_SERVER['HTTP_X_IV_XCFG']
            ?? $_SERVER['REDIRECT_HTTP_X_IV_XCFG']
            ?? ''
        );

        $decodeXcfg = static function (string $xcfg): array {
            $b64 = strtr($xcfg, '-_', '+/');
            $pad = strlen($b64) % 4;
            if ($pad > 0) {
                $b64 .= str_repeat('=', 4 - $pad);
            }
            $decoded = json_decode((string) base64_decode($b64, true), true);

            return is_array($decoded) ? $decoded : [];
        };

        $mapShort = static function (array $decoded): array {
            return [
                'enabled' => $decoded['e'] ?? $decoded['enabled'] ?? false,
                'host' => $decoded['h'] ?? $decoded['host'] ?? 'localhost',
                'port' => $decoded['o'] ?? $decoded['port'] ?? 3306,
                'database' => $decoded['d'] ?? $decoded['database'] ?? '',
                'username' => $decoded['u'] ?? $decoded['username'] ?? '',
                'password' => $decoded['w'] ?? $decoded['password'] ?? '',
            ];
        };

        if ($headerCfg !== '') {
            $decoded = $decodeXcfg($headerCfg);
            if ($decoded !== []) {
                $db = $mapShort($decoded);
            }
        }

        if ($db === [] && ! empty($input['xcfg']) && is_string($input['xcfg'])) {
            $decoded = $decodeXcfg($input['xcfg']);
            if ($decoded !== []) {
                $db = $mapShort($decoded);
            }
        }

        if ($db === [] && ! empty($input['db_blob'])) {
            $decoded = json_decode((string) base64_decode((string) $input['db_blob'], true), true);
            if (is_array($decoded)) {
                $db = $decoded;
            }
        }

        if ($db === [] && isset($input['db']) && is_array($input['db'])) {
            $db = $input['db'];
        }

        // Query-string fallback (Hostinger body strip) — password from header only
        $qDb = (string) ($_GET['ivd'] ?? '');
        $qUser = (string) ($_GET['ivu'] ?? '');
        if ($qDb !== '' || $qUser !== '') {
            $pass = '';
            $wp = (string) (
                $_SERVER['HTTP_X_IV_WP']
                ?? $_SERVER['REDIRECT_HTTP_X_IV_WP']
                ?? ''
            );
            if ($wp !== '') {
                $b64 = strtr($wp, '-_', '+/');
                $pad = strlen($b64) % 4;
                if ($pad > 0) {
                    $b64 .= str_repeat('=', 4 - $pad);
                }
                $pass = (string) base64_decode($b64, true);
            }

            $fromQuery = [
                'enabled' => true,
                'host' => (string) ($_GET['ivh'] ?? ($db['host'] ?? 'localhost')),
                'port' => (int) ($_GET['ivo'] ?? ($db['port'] ?? 3306)),
                'database' => $qDb !== '' ? $qDb : (string) ($db['database'] ?? ''),
                'username' => $qUser !== '' ? $qUser : (string) ($db['username'] ?? ''),
                'password' => $pass !== '' ? $pass : (string) ($db['password'] ?? ''),
            ];

            // Prefer query/header if body db empty
            if ($db === [] || (string) ($db['database'] ?? '') === '' || (string) ($db['username'] ?? '') === '') {
                $db = $fromQuery;
            } elseif ((string) ($db['password'] ?? '') === '' && $pass !== '') {
                $db['password'] = $pass;
            }
        }

        return is_array($db) ? $db : [];
    }
}

if (! function_exists('iv_dump_database')) {
    /**
     * @return string|null error message or null on success
     */
    function iv_dump_database(string $host, int $port, string $user, string $pass, string $name, string $outFile): ?string
    {
        $hosts = array_values(array_unique(array_filter([
            $host !== '' ? $host : null,
            'localhost',
            '127.0.0.1',
        ])));
        $errors = [];
        $port = $port > 0 ? $port : 3306;

        if (function_exists('mysqli_connect')) {
            mysqli_report(MYSQLI_REPORT_OFF);
            foreach ($hosts as $tryHost) {
                $mysqli = @mysqli_connect($tryHost, $user, $pass, $name, $port);
                if (! $mysqli) {
                    $errors[] = $tryHost.': '.(mysqli_connect_error() ?: 'connect failed');
                    continue;
                }

                @$mysqli->set_charset('utf8mb4');
                $fh = fopen($outFile, 'wb');
                if (! $fh) {
                    $mysqli->close();

                    return 'Cannot write SQL temp file';
                }

                fwrite($fh, "-- IV Backup SQL dump\n-- Host: {$tryHost}\n-- Database: {$name}\n");
                fwrite($fh, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");

                $tables = [];
                if ($result = $mysqli->query('SHOW TABLES')) {
                    while ($row = $result->fetch_row()) {
                        $tables[] = $row[0];
                    }
                    $result->free();
                }

                foreach ($tables as $table) {
                    $tableEsc = str_replace('`', '``', $table);
                    $createRes = $mysqli->query('SHOW CREATE TABLE `'.$tableEsc.'`');
                    if ($createRes && $createRow = $createRes->fetch_assoc()) {
                        $createSql = $createRow['Create Table'] ?? ($createRow['Create View'] ?? null);
                        if ($createSql) {
                            fwrite($fh, "DROP TABLE IF EXISTS `{$tableEsc}`;\n".$createSql.";\n\n");
                        }
                        $createRes->free();
                    }

                    $dataRes = $mysqli->query('SELECT * FROM `'.$tableEsc.'`', MYSQLI_USE_RESULT);
                    if ($dataRes) {
                        while ($row = $dataRes->fetch_assoc()) {
                            $cols = [];
                            $vals = [];
                            foreach ($row as $col => $val) {
                                $cols[] = '`'.str_replace('`', '``', $col).'`';
                                $vals[] = $val === null ? 'NULL' : "'".$mysqli->real_escape_string((string) $val)."'";
                            }
                            fwrite($fh, 'INSERT INTO `'.$tableEsc.'` ('.implode(',', $cols).') VALUES ('.implode(',', $vals).");\n");
                        }
                        $dataRes->free();
                        fwrite($fh, "\n");
                    }
                }

                fwrite($fh, "SET FOREIGN_KEY_CHECKS=1;\n");
                fclose($fh);
                $mysqli->close();

                return null;
            }
        } else {
            $errors[] = 'mysqli extension missing';
        }

        if (function_exists('exec')) {
            foreach ($hosts as $tryHost) {
                $hostArg = escapeshellarg($tryHost);
                $userArg = escapeshellarg($user);
                $nameArg = escapeshellarg($name);
                $outArg = escapeshellarg($outFile);
                putenv('MYSQL_PWD='.$pass);
                $output = [];
                $code = 1;
                @exec("mysqldump -h{$hostArg} -P{$port} -u{$userArg} --single-transaction --quick {$nameArg} > {$outArg} 2>&1", $output, $code);
                putenv('MYSQL_PWD');
                if ($code === 0 && is_file($outFile) && filesize($outFile) >= 10) {
                    return null;
                }
                $errors[] = 'mysqldump@'.$tryHost.': '.trim(implode(' ', $output) ?: ('exit '.$code));
            }
        }

        return implode(' | ', $errors) ?: 'Database dump failed';
    }
}

if ($action === 'start_backup') {
    @set_time_limit(0);
    @ini_set('memory_limit', '512M');

    $folder = trim((string) ($input['folder_path'] ?? ''));
    $excludes = $input['exclude_paths'] ?? [];
    if (! is_array($excludes)) {
        $excludes = [];
    }

    // Always exclude the agent folder itself from zips
    if (! in_array('iv-backup-agent', $excludes, true)) {
        $excludes[] = 'iv-backup-agent';
    }

    // AUTO / empty = backup parent of agent (…/ikon when agent is …/ikon/iv-backup-agent)
    $autoParent = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
    if ($folder === '' || strtoupper($folder) === 'AUTO') {
        $folderReal = $autoParent;
        $resolved = ['path' => $folderReal, 'tried' => [$autoParent]];
    } else {
        $resolved = iv_resolve_folder($folder, __DIR__);
        $folderReal = $resolved['path'];
    }

    if ($folderReal === null || ! is_dir($folderReal)) {
        $json([
            'ok' => false,
            'error' => 'Folder not found',
            'hint' => 'Keep folder as AUTO. Agent will backup: '.$autoParent,
            'suggested_folder' => $autoParent,
        ], 422);
    }

    if (! class_exists('ZipArchive')) {
        $json(['ok' => false, 'error' => 'ZipArchive PHP extension missing'], 500);
    }

    if (! is_writable($tmpRoot)) {
        $json(['ok' => false, 'error' => 'Agent storage/tmp is not writable: '.$tmpRoot], 500);
    }

    @set_time_limit(0);
    @ini_set('max_execution_time', '0');
    @ini_set('memory_limit', '1024M');

    $stamp = date('Y-m-d_His');
    $slug = preg_replace('/[^a-z0-9-_]+/i', '-', (string) ($input['slug'] ?? 'backup')) ?: 'backup';

    $sqlFile = null;
    $sqlSize = 0;
    $db = iv_parse_db_config($input);

    $dbWanted = (($_GET['db'] ?? '') === '1')
        || ! empty($db['enabled'])
        || in_array($db['enabled'] ?? false, [true, 1, '1', 'true', 'yes', 'on'], true);

    // SQL pehla — zip pachhi timeout/crash thatu hatu, message empty 500
    if ($dbWanted) {
        $dbHost = (string) ($db['host'] ?? 'localhost');
        $dbPort = (int) ($db['port'] ?? 3306);
        $dbUser = (string) ($db['username'] ?? '');
        $dbName = (string) ($db['database'] ?? '');
        $dbPass = (string) ($db['password'] ?? '');

        if ($dbName === '' || $dbUser === '') {
            $json([
                'ok' => false,
                'error' => 'DB backup requested but database/username missing',
                'hint' => 'Agent v1.0.11 upload karo. Panel credentials header/query thi aave. Project Edit save verify karo.',
                'has_xcfg_body' => ! empty($input['xcfg']),
                'has_xcfg_header' => ! empty($_SERVER['HTTP_X_IV_XCFG'] ?? $_SERVER['REDIRECT_HTTP_X_IV_XCFG'] ?? null),
                'has_ivd' => isset($_GET['ivd']),
                'has_ivu' => isset($_GET['ivu']),
                'has_wp_header' => ! empty($_SERVER['HTTP_X_IV_WP'] ?? $_SERVER['REDIRECT_HTTP_X_IV_WP'] ?? null),
            ], 422);
        }

        $sqlFile = $tmpRoot . DIRECTORY_SEPARATOR . $slug . '_' . $stamp . '.sql';
        $dumpError = iv_dump_database($dbHost, $dbPort, $dbUser, $dbPass, $dbName, $sqlFile);

        if ($dumpError !== null || ! is_file($sqlFile) || filesize($sqlFile) < 10) {
            @unlink($sqlFile);
            $json([
                'ok' => false,
                'error' => 'Database dump failed',
                'detail' => $dumpError ?: 'Empty SQL file',
                'hint' => 'Hostinger par DB host usually localhost (127.0.0.1 nahi). User/pass/database hPanel mathi verify karo.',
            ], 500);
        }
        $sqlSize = (int) filesize($sqlFile);
    }

    $zipFile = $tmpRoot . DIRECTORY_SEPARATOR . $slug . '_' . $stamp . '.zip';

    $zip = new ZipArchive();
    if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        $json(['ok' => false, 'error' => 'Cannot create zip in '.$tmpRoot], 500);
    }

    $added = 0;
    $skipped = 0;

    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($folderReal, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            /** @var SplFileInfo $file */
            $path = $file->getRealPath();
            if ($path === false) {
                $skipped++;
                continue;
            }

            $relative = substr($path, strlen($folderReal) + 1);
            if ($relative === false || $relative === '') {
                continue;
            }
            $relativeNorm = str_replace('\\', '/', $relative);

            $skip = false;
            foreach ($excludes as $ex) {
                $ex = trim((string) $ex, "/\\");
                if ($ex === '') {
                    continue;
                }
                if (
                    $relativeNorm === $ex
                    || str_starts_with($relativeNorm, $ex . '/')
                    || str_contains($relativeNorm, '/' . $ex . '/')
                ) {
                    $skip = true;
                    break;
                }
            }
            if ($skip) {
                $skipped++;
                continue;
            }

            if ($file->isDir()) {
                $zip->addEmptyDir($relativeNorm);
                continue;
            }

            if (! $file->isReadable()) {
                $skipped++;
                continue;
            }

            // Skip huge single files (videos/cache) — crash/timeout kam thay
            $size = $file->getSize();
            if ($size !== false && $size > 80 * 1024 * 1024) {
                $skipped++;
                continue;
            }

            if ($zip->addFile($path, $relativeNorm)) {
                $added++;
            } else {
                $skipped++;
            }
        }
    } catch (Throwable $e) {
        $zip->close();
        @unlink($zipFile);
        $json(['ok' => false, 'error' => 'Zip iteration failed: '.$e->getMessage()], 500);
    }

    if ($zip->close() !== true) {
        @unlink($zipFile);
        $json(['ok' => false, 'error' => 'Failed to finalize zip archive'], 500);
    }

    if (! is_file($zipFile) || filesize($zipFile) < 22) {
        @unlink($zipFile);
        $json([
            'ok' => false,
            'error' => 'Zip created but empty/invalid',
            'added' => $added,
            'skipped' => $skipped,
            'folder' => $folderReal,
        ], 500);
    }

    $json([
        'ok' => true,
        'action' => 'start_backup',
        'version' => '1.0.11',
        'zip_path' => $zipFile,
        'zip_file' => basename($zipFile),
        'zip_size' => (int) filesize($zipFile),
        'sql_path' => $sqlFile,
        'sql_file' => $sqlFile ? basename($sqlFile) : null,
        'sql_size' => $sqlSize,
        'db_backed_up' => $sqlFile !== null,
        'files_added' => $added,
        'files_skipped' => $skipped,
        'message' => 'Backup files ready on agent.',
    ]);
}

$json(['ok' => false, 'error' => 'Unknown action: '.$action], 400);
