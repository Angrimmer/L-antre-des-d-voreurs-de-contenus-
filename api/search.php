<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';

$query = trim($_GET['q'] ?? '');
$cat   = $_GET['cat'] ?? 'anime';

if (strlen($query) < 2) {
    echo json_encode(['results' => []]);
    exit;
}

$results = match($cat) {
    'film'  => searchTMDB($query, 'movie'),
    'serie' => searchTMDB($query, 'tv'),
    'anime' => searchAniList($query),
    'jeu'   => searchRAWG($query),
    default => [],
};

echo json_encode(['results' => $results]);

/* =============================================
   TMDB — Films & Séries
   ============================================= */
function searchTMDB(string $query, string $type): array {
    $key = TMDB_API_KEY;
    $url = "https://api.themoviedb.org/3/search/{$type}?api_key={$key}&query="
         . urlencode($query) . "&language=fr-FR&include_adult=false";

    $data = fetchJSON($url);
    if (!isset($data['results'])) return [];

    return array_map(function($item) use ($type) {
        $title = $type === 'movie' ? ($item['title'] ?? '') : ($item['name'] ?? '');
        $date  = $type === 'movie' ? ($item['release_date'] ?? '') : ($item['first_air_date'] ?? '');
        $cover = $item['poster_path']
            ? 'https://image.tmdb.org/t/p/w300' . $item['poster_path']
            : null;

        return [
            'external_id' => (string) $item['id'],
            'title'       => $title,
            'year'        => $date ? substr($date, 0, 4) : null,
            'cover_url'   => $cover,
            'synopsis'    => $item['overview'] ?? '',
        ];
    }, array_slice($data['results'], 0, 10));
}

/* =============================================
   AniList — Animes (GraphQL, pas de clé nécessaire)
   ============================================= */
function searchAniList(string $query): array {
    $gql = 'query($search: String) { Page(perPage: 10) { media(search: $search, type: ANIME, sort: POPULARITY_DESC) { id title { romaji english } startDate { year } coverImage { large } description(asHtml: false) } } }';

    $payload = json_encode(['query' => $gql, 'variables' => ['search' => $query]]);
    $data    = fetchJSON('https://graphql.anilist.co', $payload);

    if (!isset($data['data']['Page']['media'])) return [];

    return array_map(function($item) {
        $title = $item['title']['english'] ?? $item['title']['romaji'] ?? '';
        // AniList met des balises HTML dans la description même avec asHtml: false
        $synopsis = strip_tags($item['description'] ?? '');
        return [
            'external_id' => (string) $item['id'],
            'title'       => $title,
            'year'        => $item['startDate']['year'] ?? null,
            'cover_url'   => $item['coverImage']['large'] ?? null,
            'synopsis'    => mb_substr($synopsis, 0, 400),
        ];
    }, $data['data']['Page']['media']);
}

/* =============================================
   RAWG — Jeux vidéo
   ============================================= */
function searchRAWG(string $query): array {
    $key = RAWG_API_KEY;
    $url = "https://api.rawg.io/api/games?key={$key}&search=" . urlencode($query) . "&page_size=10";

    $data = fetchJSON($url);
    if (!isset($data['results'])) return [];

    return array_map(function($item) {
        $cover = $item['background_image'] ?? null;
        return [
            'external_id' => (string) $item['id'],
            'title'       => $item['name'] ?? '',
            'year'        => isset($item['released']) ? substr($item['released'], 0, 4) : null,
            'cover_url'   => $cover,
            'synopsis'    => '',
        ];
    }, $data['results']);
}

/* =============================================
   Utilitaire HTTP (cURL)
   ============================================= */
function fetchJSON(string $url, ?string $postBody = null): array {
    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: AntreDevoLib/1.0',
        ],
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    if ($postBody !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postBody);
    }

    $raw = curl_exec($ch);
    curl_close($ch);

    return $raw ? (json_decode($raw, true) ?? []) : [];
}
