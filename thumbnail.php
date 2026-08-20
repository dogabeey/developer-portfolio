<?php
declare(strict_types=1);

function is_http_url(string $url): bool
{
    return (bool) filter_var($url, FILTER_VALIDATE_URL)
        && in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true);
}

function has_public_host(string $url): bool
{
    $host = parse_url($url, PHP_URL_HOST);
    if (!$host || strtolower($host) === 'localhost') {
        return false;
    }
    $addresses = gethostbynamel($host);
    if (!$addresses) {
        return false;
    }
    foreach ($addresses as $address) {
        if (!filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return false;
        }
    }
    return true;
}

function resolve_thumbnail_url(string $imageUrl, string $pageUrl): ?string
{
    $imageUrl = html_entity_decode(trim($imageUrl), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if (str_starts_with($imageUrl, '//')) {
        $imageUrl = parse_url($pageUrl, PHP_URL_SCHEME) . ':' . $imageUrl;
    } elseif (!is_http_url($imageUrl)) {
        $parts = parse_url($pageUrl);
        if (!$parts || !isset($parts['scheme'], $parts['host'])) {
            return null;
        }
        $origin = $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
        if (str_starts_with($imageUrl, '/')) {
            $imageUrl = $origin . $imageUrl;
        } else {
            $path = $parts['path'] ?? '/';
            $imageUrl = $origin . rtrim(str_replace('\\', '/', dirname($path)), '/') . '/' . $imageUrl;
        }
    }
    return is_http_url($imageUrl) ? $imageUrl : null;
}

function app_store_lookup(string $pageUrl): ?array
{
    static $lookupCache = [];
    if (array_key_exists($pageUrl, $lookupCache)) {
        return $lookupCache[$pageUrl];
    }
    $host = strtolower((string) parse_url($pageUrl, PHP_URL_HOST));
    if (!in_array($host, ['apps.apple.com', 'itunes.apple.com'], true) || !preg_match('/\/id(\d+)/', $pageUrl, $idMatch)) {
        return $lookupCache[$pageUrl] = null;
    }
    $country = preg_match('#apps\.apple\.com/([a-z]{2})/#i', $pageUrl, $countryMatch) ? strtolower($countryMatch[1]) : 'us';
    $lookup = fetch_project_page_html('https://itunes.apple.com/lookup?id=' . $idMatch[1] . '&country=' . $country);
    $data = $lookup ? json_decode($lookup, true) : null;
    return $lookupCache[$pageUrl] = ($data['results'][0] ?? null);
}

function decode_chunked_body(string $body): string
{
    $decoded = '';
    $offset = 0;
    while ($offset < strlen($body)) {
        $lineEnd = strpos($body, "\r\n", $offset);
        if ($lineEnd === false) {
            return $body;
        }
        $length = hexdec(trim(explode(';', substr($body, $offset, $lineEnd - $offset), 2)[0]));
        $offset = $lineEnd + 2;
        if ($length === 0) {
            break;
        }
        $chunk = substr($body, $offset, $length);
        if (strlen($chunk) !== $length) {
            return $body;
        }
        $decoded .= $chunk;
        $offset += $length + 2;
    }
    return $decoded;
}

function fetch_project_page_html(string $pageUrl): ?string
{
    static $pageCache = [];
    if (array_key_exists($pageUrl, $pageCache)) {
        return $pageCache[$pageUrl];
    }
    if (!is_http_url($pageUrl) || !has_public_host($pageUrl)) {
        return $pageCache[$pageUrl] = null;
    }

    $html = false;
    if (function_exists('curl_init')) {
        $curl = curl_init($pageUrl);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT_MS => 1500,
            CURLOPT_TIMEOUT_MS => 4000,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_USERAGENT => 'GameDevPortfolioThumbnailBot/1.0',
        ]);
        $html = curl_exec($curl);
    } elseif (filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN)) {
        $context = stream_context_create(['http' => ['timeout' => 6, 'user_agent' => 'GameDevPortfolioThumbnailBot/1.0', 'follow_location' => 0]]);
        $html = @file_get_contents($pageUrl, false, $context, 0, 1_000_000);
    }

    // Windows PHP ZIP installs may have both cURL and allow_url_fopen disabled.
    // Use a small, direct HTTP request as a final fallback for public pages.
    if (!is_string($html) || $html === '') {
        $parts = parse_url($pageUrl);
        $host = $parts['host'] ?? '';
        $scheme = $parts['scheme'] ?? '';
        $port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);
        $path = ($parts['path'] ?? '/') . (isset($parts['query']) ? '?' . $parts['query'] : '');
        $transport = $scheme === 'https' ? 'ssl://' : 'tcp://';
        $context = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'peer_name' => $host]]);
        $socket = @stream_socket_client($transport . $host . ':' . $port, $errorNumber, $errorMessage, 6, STREAM_CLIENT_CONNECT, $context);
        if ($socket) {
            stream_set_timeout($socket, 6);
            fwrite($socket, "GET {$path} HTTP/1.1\r\nHost: {$host}\r\nUser-Agent: GameDevPortfolioThumbnailBot/1.0\r\nAccept: text/html\r\nAccept-Encoding: identity\r\nConnection: close\r\n\r\n");
            $response = stream_get_contents($socket, 2_000_000);
            fclose($socket);
            [$headers, $body] = array_pad(explode("\r\n\r\n", $response, 2), 2, '');
            $html = stripos($headers, 'Transfer-Encoding: chunked') !== false ? decode_chunked_body($body) : $body;
        }
    }

    if (!is_string($html) || $html === '') {
        return $pageCache[$pageUrl] = null;
    }

    return $pageCache[$pageUrl] = $html;
}

function fetch_project_thumbnail(string $pageUrl): ?string
{
    $html = fetch_project_page_html($pageUrl);
    if ($html === null) {
        return null;
    }

    preg_match_all('/<meta\b[^>]*>/i', $html, $tags);
    foreach ($tags[0] as $tag) {
        preg_match('/\b(?:property|name)\s*=\s*["\']?([^"\'\s>]+)/i', $tag, $keyMatch);
        $key = strtolower($keyMatch[1] ?? '');
        if (!in_array($key, ['og:image', 'og:image:secure_url', 'twitter:image', 'twitter:image:src'], true)) {
            continue;
        }
        preg_match('/\bcontent\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))/i', $tag, $contentMatch);
        $imageUrl = $contentMatch[1] ?? $contentMatch[2] ?? $contentMatch[3] ?? '';
        $resolved = resolve_thumbnail_url($imageUrl, $pageUrl);
        if ($resolved) {
            return $resolved;
        }
    }
    return null;
}

function image_attribute(string $tag, string $attribute): string
{
    preg_match('/\b' . preg_quote($attribute, '/') . '\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))/i', $tag, $match);
    return html_entity_decode($match[1] ?? $match[2] ?? $match[3] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function storefront_description(string $pageUrl): ?string
{
    $host = strtolower((string) parse_url($pageUrl, PHP_URL_HOST));
    if (!in_array($host, ['apps.apple.com', 'itunes.apple.com', 'play.google.com'], true)) {
        return null;
    }

    // Apple's public lookup endpoint returns the actual developer-written app
    // description, unlike the generic social metadata on product pages.
    if (in_array($host, ['apps.apple.com', 'itunes.apple.com'], true)) {
        $description = trim((string) (app_store_lookup($pageUrl)['description'] ?? ''));
        if ($description !== '') {
            return $description;
        }
    }

    $html = fetch_project_page_html($pageUrl);
    if ($html === null) {
        return null;
    }
    preg_match_all('/<meta\b[^>]*>/i', $html, $tags);
    $preferredKeys = in_array($host, ['apps.apple.com', 'itunes.apple.com'], true)
        ? ['apple:description', 'og:description', 'description']
        : ['og:description', 'description'];
    foreach ($preferredKeys as $preferredKey) {
        foreach ($tags[0] as $tag) {
            preg_match('/\b(?:property|name)\s*=\s*["\']?([^"\'\s>]+)/i', $tag, $keyMatch);
            if (strtolower($keyMatch[1] ?? '') !== $preferredKey) {
                continue;
            }
            $description = trim(image_attribute($tag, 'content'));
            if ($description !== '') {
                return $description;
            }
        }
    }
    return null;
}

function storefront_title(string $pageUrl): ?string
{
    $host = strtolower((string) parse_url($pageUrl, PHP_URL_HOST));
    if (!in_array($host, ['apps.apple.com', 'itunes.apple.com', 'play.google.com'], true)) {
        return null;
    }
    if (in_array($host, ['apps.apple.com', 'itunes.apple.com'], true)) {
        $title = trim((string) (app_store_lookup($pageUrl)['trackName'] ?? ''));
        if ($title !== '') {
            return $title;
        }
    }
    $html = fetch_project_page_html($pageUrl);
    if ($html === null) {
        return null;
    }
    if (preg_match('/<h1\b[^>]*>(.*?)<\/h1>/is', $html, $match)) {
        $title = trim(html_entity_decode(strip_tags($match[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($title !== '') {
            return $title;
        }
    }
    return null;
}

function fetch_project_screenshots(string $pageUrl): array
{
    $host = strtolower((string) parse_url($pageUrl, PHP_URL_HOST));
    if (!in_array($host, ['apps.apple.com', 'itunes.apple.com', 'play.google.com'], true)) {
        return [];
    }
    $html = fetch_project_page_html($pageUrl);
    if ($html === null) {
        return [];
    }

    $images = [];
    if (in_array($host, ['apps.apple.com', 'itunes.apple.com'], true)) {
        // Prefer Apple’s structured iPhone-only list. iPad screenshots are a
        // separate field and intentionally ignored.
        foreach ((app_store_lookup($pageUrl)['screenshotUrls'] ?? []) as $url) {
            if (is_string($url) && is_http_url($url)) {
                $images[$url] = $url;
            }
        }
        if ($images) {
            return array_slice(array_values($images), 0, 6);
        }

        // Some legacy listings omit screenshotUrls from lookup; fall back to
        // the iPhone shelf on their product page.
        // Restrict extraction to App Store's iPhone shelf, excluding iPad previews.
        $iphoneShelfStart = strpos($html, 'id="product_media_phone_');
        if ($iphoneShelfStart === false) {
            return [];
        }
        $nextMediaShelf = strpos($html, 'id="product_media_', $iphoneShelfStart + 1);
        $iphoneShelf = substr($html, $iphoneShelfStart, $nextMediaShelf === false ? null : $nextMediaShelf - $iphoneShelfStart);
        // App Store screenshot source URLs include `_screen_` in their media shelf assets.
        preg_match_all('/https:\/\/[^"\'\s,]+_screen_[^"\'\s,]+/i', $iphoneShelf, $matches);
        foreach ($matches[0] as $url) {
            $url = rtrim(html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8'), ');');
            if (str_contains($url, '{')) {
                continue;
            }
            $key = preg_replace('#/(?:\d+x\d+)[^/]*\.(?:webp|jpe?g|png)$#i', '', $url);
            if (is_http_url($url) && !isset($images[$key])) {
                $images[$key] = $url;
            }
        }
    } else {
        // Google Play can use src, data-src, or srcset depending on the page version.
        preg_match_all('/<img\b[^>]*>/i', $html, $tags);
        foreach ($tags[0] as $tag) {
            if (stripos($tag, 'Screenshot image') === false && stripos($tag, 'data-screenshot') === false) {
                continue;
            }
            $url = image_attribute($tag, 'src') ?: image_attribute($tag, 'data-src') ?: image_attribute($tag, 'data-original');
            if (!$url) {
                $srcset = image_attribute($tag, 'srcset') ?: image_attribute($tag, 'data-srcset');
                $url = trim(explode(',', $srcset)[0] ?? '');
                $url = preg_split('/\s+/', $url)[0] ?? '';
            }
            if (is_http_url($url)) {
                $images[$url] = $url;
            }
        }
    }
    return array_slice(array_values($images), 0, 6);
}
