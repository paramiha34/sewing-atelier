<?php
//Прокси к API Shikimori для блока «Костюм персонажа».
//api/characters.php?q=Frieren - поиск персонажей по имени
//api/characters.php?id=184947 - произведение и описание одного персонажа
//Запрос к shikimori.io выполняет сервер: браузеру не нужен CORS,
//а ответы на сутки сохраняются в папке временных файлов, чтобы не упираться в лимит API.
require_once __DIR__ . '/../includes/config.php';

const SHIKI = 'https://shikimori.io';
const CACHE_TTL = 86400;

header('Content-Type: application/json; charset=utf-8');

function reply(array $data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

//GET-запрос к Shikimori с кешем. Возвращает декодированный JSON или null при ошибке.
//Shikimori требует заголовок User-Agent, без него запрос отклоняется.
function shiki_get(string $path): ?array
{
    $dir = sys_get_temp_dir() . '/lekalo-shiki';
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
    $file = $dir . '/' . md5($path) . '.json';
    if (is_file($file) && time() - filemtime($file) < CACHE_TTL) {
        return json_decode(file_get_contents($file), true);
    }

    $url = SHIKI . $path;
    $agent = 'Lekalo atelier (study project UP 09.01)';
    $body = false;
    $code = 0;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_USERAGENT      => $agent,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        ]);
        $body = curl_exec($ch);
        //в XAMPP для Windows часто не настроен список корневых сертификатов (curl.cainfo),
        //тогда повторяем запрос без проверки сертификата - для учебного локального сервера это допустимо
        if ($body === false && curl_errno($ch) === 60) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $body = curl_exec($ch);
        }
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    } else {
        $ctx = stream_context_create(['http' => ['timeout' => 10, 'header' => "User-Agent: $agent\r\nAccept: application/json\r\n", 'ignore_errors' => true]]);
        $body = @file_get_contents($url, false, $ctx);
        if (isset($http_response_header[0]) && preg_match('~\s(\d{3})\s~', $http_response_header[0], $m)) {
            $code = (int)$m[1];
        }
    }

    if ($body === false || $code !== 200) {
        return null;
    }
    $data = json_decode($body, true);
    if (!is_array($data)) {
        return null;
    }
    @file_put_contents($file, $body);
    return $data;
}

//В описаниях Shikimori встречаются теги вида [character=1]Имя[/character] и [spoiler]...[/spoiler]
function clean_description(?string $text): string
{
    if (!$text) {
        return '';
    }
    $text = preg_replace('~\[spoiler[^\]]*\].*?\[/spoiler\]~su', '', $text);
    $text = preg_replace('~\[/?[a-z_]+(=[^\]]*)?\]~iu', '', $text);
    return trim(preg_replace('~\s+~u', ' ', $text));
}

function absolute(string $path): string
{
    return str_starts_with($path, 'http') ? $path : SHIKI . $path;
}

if (isset($_GET['q'])) {
    $q = trim((string)$_GET['q']);
    $len = mb_strlen($q);
    if ($len < 3 || $len > 60) {
        reply(['error' => 'Введите имя персонажа: от 3 до 60 символов.'], 400);
    }
    $list = shiki_get('/api/characters/search?search=' . rawurlencode($q));
    if ($list === null) {
        reply(['error' => 'Сервис Shikimori сейчас не отвечает. Попробуйте через минуту.'], 502);
    }
    $items = [];
    foreach (array_slice($list, 0, 8) as $c) {
        $items[] = [
            'id'      => (int)$c['id'],
            'name'    => $c['name'] ?? '',
            'russian' => $c['russian'] ?? '',
            'image'   => absolute($c['image']['original'] ?? $c['image']['preview'] ?? ''),
            'url'     => absolute($c['url'] ?? ('/characters/' . (int)$c['id'])),
        ];
    }
    reply(['items' => $items]);
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id <= 0) {
        reply(['error' => 'Неверный номер персонажа.'], 400);
    }
    $c = shiki_get('/api/characters/' . $id);
    if ($c === null) {
        reply(['error' => 'Не удалось получить данные персонажа.'], 502);
    }
    //сначала ищем аниме, если его нет - мангу или ранобэ
    $works = array_merge($c['animes'] ?? [], $c['mangas'] ?? []);
    $source = '';
    if ($works) {
        $source = $works[0]['russian'] ?: $works[0]['name'];
    }
    reply([
        'id'          => $id,
        'source'      => $source,
        'description' => clean_description($c['description'] ?? ''),
        'url'         => absolute($c['url'] ?? ('/characters/' . $id)),
    ]);
}

reply(['error' => 'Укажите параметр q или id.'], 400);
