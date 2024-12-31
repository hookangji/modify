<?php
session_start(); // 세션 시작

// 로그인 여부 확인
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    header('Location: login.html'); // 로그인 페이지로 리디렉트
    exit;
}

// userID가 세션에 저장되어 있는지 확인
if (!isset($_SESSION['userID'])) {
    echo "Error: User ID not found in session.";
    exit;
}

// JSON 파일 경로
$postsFile = 'posts.json';

// POST 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 게시물 데이터 읽기
    $posts = file_exists($postsFile) ? json_decode(file_get_contents($postsFile), true) : [];

    // 최대 postID 찾기
    $maxPostID = 0;
    foreach ($posts as $post) {
        if (isset($post['postID']) && $post['postID'] > $maxPostID) {
            $maxPostID = $post['postID'];
        }
    }

    // 새로운 게시물 생성
    $newPost = [
        'postID' => $maxPostID + 1, // 새로운 postID 자동 생성
        'userID' => $_SESSION['userID'], // 로그인한 사용자의 ID 저장
        'date' => $_POST['date'] ?? date('Y-m-d'), // 날짜 (기본값: 오늘 날짜)
        'time' => $_POST['time'] ?? date('H:i'), // 시간 (기본값: 현재 시간)
        'artist' => $_POST['artist'] ?? 'Unknown Artist', // 가수 (기본값: Unknown Artist)
        'songTitle' => $_POST['song-title'] ?? 'Untitled', // 곡 제목 (기본값: Untitled)
        'description' => $_POST['description'] ?? '', // 상세 설명 (기본값: 빈 문자열)
        'mood' => $_POST['mood'] ?? 'neutral' // 선택된 기분 추가 (기본값: neutral)
    ];
    
    $posts[] = $newPost;
    file_put_contents('posts.json', json_encode($posts, JSON_PRETTY_PRINT));

    // 날짜와 시간 기준으로 정렬
    usort($posts, function ($a, $b) {
        $dateTimeA = strtotime($a['date'] . ' ' . $a['time']);
        $dateTimeB = strtotime($b['date'] . ' ' . $b['time']);
        return $dateTimeA <=> $dateTimeB; // PHP 7+에서 사용 가능한 비교 연산자
    });

    // JSON 파일 저장
    if (file_put_contents($postsFile, json_encode($posts, JSON_PRETTY_PRINT)) === false) {
        echo "Error: Could not save post data.";
        exit;
    }

    // 메인 페이지로 리디렉트
    header('Location: main.html');
    exit;
}
?>