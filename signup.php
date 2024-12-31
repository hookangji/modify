<?php
// JSON 파일 경로
$jsonFile = 'users.json';

// POST 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 사용자 입력 데이터
    $ID = $_POST['ID'] ?? '';
    $password = $_POST['password'] ?? '';
    $birthdate = $_POST['birthdate'] ?? '';

    // 필드 검증
    if (!$ID || !$password || !$birthdate) {
        echo 'Enter all information.';
        exit;
    }

    // JSON 데이터 읽기
    $users = [];
    if (file_exists($jsonFile)) {
        $fileContents = file_get_contents($jsonFile);

        // 파일이 비어 있지 않으면 JSON 디코드
        if (!empty($fileContents)) {
            $users = json_decode($fileContents, true);

            // 디코드 실패 시 빈 배열로 초기화
            if ($users === null) {
                $users = [];
            }
        }
    }

    // 중복 ID 확인
    foreach ($users as $user) {
        if ($user['ID'] === $ID) {
            echo 'This ID already exists.';
            exit;
        }
    }

    // 새 사용자 추가
    $users[] = [
        'ID' => $ID,
        'password' => password_hash($password, PASSWORD_DEFAULT), // 비밀번호 암호화
        'birthdate' => $birthdate,
    ];

    // JSON 파일에 저장
    file_put_contents($jsonFile, json_encode($users, JSON_PRETTY_PRINT));

    // 회원가입 성공 시 main.html로 리디렉션
    header('Location: main.html');
    exit;
}
?>