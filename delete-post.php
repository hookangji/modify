<?php
// JSON 파일 경로 설정
$jsonFile = 'posts.json';

// GET 요청으로 전달된 ID 가져오기, 유효하지 않으면 기본값으로 -1 설정
$postId = isset($_GET['id']) ? (int)$_GET['id'] : -1;

// POST 요청일 경우 삭제 처리 시작
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // JSON 파일이 존재하는지 확인
    if (file_exists($jsonFile)) {
        $fileContents = file_get_contents($jsonFile); // 파일 내용 읽기
        $posts = json_decode($fileContents, true); // JSON 데이터를 배열로 변환
        // 전달받은 ID가 유효하고 해당 ID의 게시물이 존재하는지 확인
        if ($postId >= 0 && isset($posts[$postId])) {
            unset($posts[$postId]); // 해당 ID의 게시물을 삭제
            $posts = array_values($posts); // 배열의 인덱스를 재정렬
            file_put_contents($jsonFile, json_encode($posts, JSON_PRETTY_PRINT)); // 변경된 데이터를 JSON 파일에 저장
        }
    }

    // 삭제 처리 후 홈 화면으로 리디렉션
    header('Location: main.html');
    exit; // 추가 처리가 되지 않도록 스크립트 종료
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Confirmation</title>
    <style>
        /* 페이지 전체 스타일 설정 */
        body {
            font-family: Arial, sans-serif; /* 기본 글꼴 설정 */
            text-align: center; /* 텍스트 가운데 정렬 */
            margin: 0; /* 여백 제거 */
            padding: 0; /* 패딩 제거 */
            background-color: #fffafa; /* 배경색 설정 */
        }

        /* 컨테이너 스타일 */
        .container {
            margin-top: 20%; /* 상단 여백 설정 */
        }

        /* 제목 스타일 */
        h1 {
            color: #ff4b4b; /* 글씨 색상 설정 */
        }

        /* 버튼 영역 스타일 */
        .buttons {
            margin-top: 20px; /* 상단 여백 */
            display: flex; /* 플렉스 박스 사용 */
            justify-content: center; /* 버튼들 가운데 정렬 */
            gap: 10px; /* 버튼 간격 설정 */
        }

        /* 기본 버튼 스타일 */
        button {
            padding: 12px 24px; /* 버튼 안쪽 여백 설정 */
            font-size: 16px; /* 글씨 크기 설정 */
            font-weight: bold; /* 글씨 두께 설정 */
            color: white; /* 글씨 색상 */
            background: linear-gradient(135deg, #ff6f91, #ff9671); /* 버튼 배경 그라데이션 */
            border: none; /* 테두리 제거 */
            border-radius: 12px; /* 둥근 모서리 */
            cursor: pointer; /* 커서 모양 변경 */
            box-shadow: 0 6px 12px rgba(255, 111, 145, 0.4); /* 버튼 그림자 */
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease; /* 애니메이션 효과 */
        }

        /* 버튼에 마우스를 올렸을 때 */
        button:hover {
            transform: translateY(-3px); /* 살짝 올라감 */
            box-shadow: 0 8px 16px rgba(255, 111, 145, 0.6); /* 그림자 강화 */
            background: linear-gradient(135deg, #ff9671, #ff6f91); /* 배경색 전환 */
        }

        /* 버튼을 클릭했을 때 */
        button:active {
            transform: scale(0.98); /* 살짝 축소 */
            box-shadow: 0 4px 8px rgba(255, 111, 145, 0.3); /* 그림자 축소 */
        }

        /* 취소 버튼 추가 스타일 */
        button.cancel {
            color: #333; /* 글씨 색상 변경 */
            background: linear-gradient(135deg, #d1d8e0, #a5b1c2); /* 배경 그라데이션 */
            box-shadow: 0 4px 8px rgba(165, 177, 194, 0.4); /* 그림자 설정 */
        }

        /* 취소 버튼에 마우스를 올렸을 때 */
        button.cancel:hover {
            background: linear-gradient(135deg, #a5b1c2, #d1d8e0); /* 배경색 전환 */
            box-shadow: 0 6px 12px rgba(165, 177, 194, 0.6); /* 그림자 강화 */
        }

        /* 취소 버튼을 클릭했을 때 */
        button.cancel:active {
            transform: scale(0.98); /* 살짝 축소 */
            box-shadow: 0 2px 6px rgba(165, 177, 194, 0.3); /* 그림자 축소 */
        }
    </style>
</head>
<body>
    <!-- 삭제 확인 메시지 -->
    <div class="container">
        <h1>Are you sure you want to delete this post?</h1>
        <!-- POST 요청으로 삭제 요청을 보냄 -->
        <form method="POST" action="delete-post.php?id=<?php echo $postId; ?>">
            <div class="buttons">
                <button type="submit">Yes</button> <!-- 삭제 실행 버튼 -->
                <button type="button" class="cancel" onclick="location.href='main.html';">No</button> <!-- 취소 버튼 -->
            </div>
        </form>
    </div>
</body>
</html>