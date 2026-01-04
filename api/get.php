<?php
header('Content-Type: application/json; charset=utf-8');

// POSTチェック
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'POST only']);
    exit;
}

// JSON受信
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['url']) || empty($input['url'])) {
    http_response_code(400);
    echo json_encode(['error' => 'url is required']);
    exit;
}

$url = escapeshellarg($input['url']);

// yt-dlp コマンド
// -f best : 最高品質
// -g : 直リンクのみ取得
$cmd = "yt-dlp -f best -g $url 2>&1";

// 実行
$output = shell_exec($cmd);

// エラーチェック
if ($output === null || trim($output) === '') {
    http_response_code(500);
    echo json_encode(['error' => 'yt-dlp failed']);
    exit;
}

// 複数行返る場合があるので最初のURLだけ取得
$lines = explode("\n", trim($output));
$directUrl = trim($lines[0]);

// URL妥当性チェック
if (!filter_var($directUrl, FILTER_VALIDATE_URL)) {
    http_response_code(500);
    echo json_encode([
        'error' => 'invalid direct url',
        'raw' => $output
    ]);
    exit;
}

// 成功レスポンス
echo json_encode([
    'direct_url' => $directUrl
], JSON_UNESCAPED_SLASHES);
