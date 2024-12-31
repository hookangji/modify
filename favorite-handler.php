<?php
session_start(); // 세션 시작

// 요청 방식이 POST인지 확인
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 요청 본문에서 JSON 데이터를 읽어 디코드
    $data = json_decode(file_get_contents('php://input'), true);
    // postId를 정수형으로 변환 (문자열로 들어올 가능성 대비)
    $postId = (int) $data['postId'];
    // action (favorite/unfavorite) 값 가져오기
    $action = $data['action'];
    // 세션에서 userID 가져오기
    $userID = $_SESSION['userID'];

    // 즐겨찾기 데이터가 저장된 파일 경로
    $favoritesFile = 'favorites.json';
    // 파일이 존재하면 내용을 읽어서 배열로 변환, 없으면 빈 배열 초기화
    $favorites = file_exists($favoritesFile) ? json_decode(file_get_contents($favoritesFile), true) : [];

    if ($action === 'favorite') { 
        // 즐겨찾기 추가 요청일 경우
        // 중복 추가를 방지하기 위해 기존 데이터에서 동일한 userID와 postID가 있는지 확인
        foreach ($favorites as $favorite) {
            if ($favorite['userID'] === $userID && $favorite['postID'] == $postId) {
                // 이미 즐겨찾기에 존재하면 성공 메시지를 반환하고 종료
                echo json_encode(['success' => true]);
                exit;
            }
        }
        // 중복이 없으면 새 데이터를 추가
        $favorites[] = ['userID' => $userID, 'postID' => $postId];
    } elseif ($action === 'unfavorite') {
        // 즐겨찾기 삭제 요청일 경우
        // array_filter를 사용하여 조건에 맞는 데이터만 유지
        $favorites = array_filter($favorites, function ($favorite) use ($userID, $postId) {
            // userID와 postID가 일치하는 항목은 제외
            return !($favorite['userID'] === $userID && $favorite['postID'] == $postId);
        });
    }

    // 수정된 즐겨찾기 데이터를 JSON 파일에 저장
    file_put_contents($favoritesFile, json_encode($favorites, JSON_PRETTY_PRINT));
    // 성공 메시지를 반환
    echo json_encode(['success' => true]);
    exit; // 추가 처리가 실행되지 않도록 종료
}

// POST 요청이 아닐 경우 실패 메시지 반환
echo json_encode(['success' => false]);