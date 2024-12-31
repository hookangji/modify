<?php
session_start(); // 세션 시작

// JSON 파일 경로
$jsonFile = 'users.json';

// POST 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 사용자 입력 데이터
    $ID = $_POST['ID'] ?? '';
    $password = $_POST['password'] ?? '';

    // 필드 검증
    if (!$ID || !$password) {
        echo 'Enter both ID and password.';
        exit;
    }

    // 사용자 데이터 읽기
    if (!file_exists($jsonFile)) {
        echo 'No user data found.';
        exit;
    }
    $users = json_decode(file_get_contents($jsonFile), true);

    // 사용자 인증
    foreach ($users as $user) {
        if ($user['ID'] === $ID) {
            // 디버깅 메시지를 로그로 변경하거나 제거
            if (password_verify($password, $user['password'])) {
                // 세션 저장
                $_SESSION['loggedin'] = true;
                $_SESSION['userID'] = $user['ID']; // 사용자 ID 저장

                // main.html로 리디렉션
                header('Location: main.html');
                exit;
            } else {
                echo 'Password mismatch.';
                exit;
            }
        }
    }

    // 인증 실패 시
    echo 'Invalid ID or password.';
    exit;
}
?>