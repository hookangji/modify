<?php
session_start(); // 세션 시작

// JSON 파일 경로
$jsonFile = 'posts.json';

// GET 파라미터로 전달된 ID 가져오기
$postId = isset($_GET['id']) ? intval($_GET['id']) : null;

// postID 유효성 검사
if ($postId === null || $postId <= 0) {
    echo "Error: Invalid or missing postID in the URL.";
    exit;
}

// JSON 데이터 읽기
if (file_exists($jsonFile)) {
    $fileContents = file_get_contents($jsonFile);
    $posts = json_decode($fileContents, true);
} else {
    echo "Error: posts.json file not found.";
    exit;
}

// 게시글 인덱스 찾기
$postIndex = false;
foreach ($posts as $index => $post) {
    if (isset($post['postID']) && (int)$post['postID'] === $postId) {
        $postIndex = $index;
        break;
    }
}

if ($postIndex === false) {
    // 디버깅 정보 출력
    echo "Debug: Post not found. Provided postID: {$postId}<br>";
    echo "Debug: Available postIDs: " . implode(', ', array_column($posts, 'postID')) . "<br>";
    exit;
}

$post = $posts[$postIndex]; // 게시물 데이터 가져오기

// POST 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 수정된 데이터 가져오기
    $posts[$postIndex]['mood'] = $_POST['mood'] ?? $posts[$postIndex]['mood'];
    $posts[$postIndex]['date'] = $_POST['date'] ?? $posts[$postIndex]['date'];
    $posts[$postIndex]['time'] = $_POST['time'] ?? $posts[$postIndex]['time'];
    $posts[$postIndex]['artist'] = $_POST['artist'] ?? $posts[$postIndex]['artist'];
    $posts[$postIndex]['songTitle'] = $_POST['song-title'] ?? $posts[$postIndex]['songTitle'];
    $posts[$postIndex]['description'] = $_POST['description'] ?? $posts[$postIndex]['description'];

    // JSON 파일 저장
    if (file_put_contents($jsonFile, json_encode($posts, JSON_PRETTY_PRINT)) === false) {
        echo "Error: Could not save updated posts.json.";
        exit;
    }

    // 캐싱 비활성화를 위한 HTTP 헤더 설정
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: Sat, 1 Jan 2000 00:00:00 GMT");

    // 메인 페이지로 리디렉션
    header('Location: main.html');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post</title>
    <style>
    /* 공통 초기화 */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* 기본 폰트 설정 */
body {
    font-family: 'Arial', sans-serif;
    background-color: #fffafa; /* 부드러운 배경색 */
    color: #333;
    line-height: 1.6;
}

/* 헤더 스타일 */
header {
    display: flex;
    justify-content: flex-start;
    align-items: center;
    padding: 10px 20px;
    background: linear-gradient(90deg, #ff4b4b, #ff6f6f); /* 그라데이션 헤더 */
    border-bottom: 2px solid #ff8c8c;
    box-shadow: 0 4px 6px rgba(255, 75, 75, 0.4);
}

/* 홈 버튼 스타일 */
header .home-button {
    text-decoration: none;
    color: white;
    font-size: 24px;
    margin-left: auto;
    padding: 10px;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.2); /* 투명 배경 */
    border: 1px solid rgba(255, 255, 255, 0.6); /* 테두리 */
    text-align: center;
    cursor: pointer;
    transition: background-color 0.3s ease, color 0.3s ease, transform 0.3s ease;
}

header .home-button:hover {
    background-color: white;
    color: #ff4b4b; /* 호버 시 아이콘 색상 변경 */
    transform: scale(1.1);
}

/* 메인 컨텐츠 */
main {
    max-width: 800px;
    margin: 30px auto;
    background-color: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    border: 2px solid #ffdddd;
}

/* 제목 스타일 */
h1 {
    font-size: 28px;
    color: #ff4b4b;
    text-align: center;
    margin-bottom: 20px;
}

/* 폼 요소 */
form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

form div {
    display: flex;
    flex-direction: column;
}

label {
    font-size: 16px;
    color: #555;
    margin-bottom: 5px;
}

input, textarea {
    padding: 12px;
    font-size: 16px;
    border: 1px solid #ddd;
    border-radius: 10px;
    background: #f9f9f9;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

textarea {
    resize: vertical;
}

input:focus, textarea:focus {
    border-color: #ff4b4b;
    box-shadow: 0 4px 8px rgba(255, 75, 75, 0.2);
    outline: none;
}

/* 버튼 스타일 */
button {
    padding: 15px 20px;
    font-size: 18px;
    color: white;
    background: linear-gradient(45deg, #ff4b4b, #ff6f6f);
    border: none;
    border-radius: 50px;
    cursor: pointer;
    text-transform: uppercase;
    font-weight: bold;
    transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease;
}

button:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 12px rgba(255, 75, 75, 0.6);
    background: linear-gradient(45deg, #ff6f6f, #ff4b4b);
}

button:active {
    transform: scale(0.98);
    box-shadow: 0 4px 8px rgba(255, 75, 75, 0.4);
}

/* 링크 스타일 */
a {
    color: #007bff;
    text-decoration: none;
    font-weight: bold;
}

a:hover {
    text-decoration: underline;
}

/* 반응형 디자인 */
@media (max-width: 768px) {
    main {
        padding: 20px;
    }

    button {
        font-size: 16px;
    }

    input, textarea {
        font-size: 14px;
    }
}
#mood-selector {
    display: flex;
    flex-direction: row; /* 가로 정렬 */
    flex-wrap: nowrap;   /* 한 줄에 배치 */
    justify-content: center; /* 중앙 정렬 */
    align-items: center; /* 세로 정렬 */
    gap: 15px; /* 버튼 간격 */
    margin-bottom: 20px;
}
.mood-icon {
    font-size: 50px;
    background: none;
    border: none;
    cursor: pointer;
    opacity: 0.6;   
    transition: transform 0.2s, opacity 0.2s;
}

.mood-icon.selected {
    opacity: 1;
    transform: scale(1.2);
}
    </style>
</head>
<body>
    <header>
    <a href="main.html" class="home-button" title="Back to Home">
        <!-- 집 모양 아이콘 (Unicode 사용) -->
        &#x1F3E0;
    </a>
    </header>
    <main>
        <?php if ($post): ?>
            <h1>Edit Post</h1>
            <form method="POST">
                <div id="mood-selector">
                    <button class="mood-icon <?php echo ($post['mood'] === 'love') ? 'selected' : ''; ?>" data-mood="love">🥰</button>
                    <button class="mood-icon <?php echo ($post['mood'] === 'happy') ? 'selected' : ''; ?>" data-mood="happy">😊</button>
                    <button class="mood-icon <?php echo ($post['mood'] === 'sad') ? 'selected' : ''; ?>" data-mood="sad">😢</button>
                    <button class="mood-icon <?php echo ($post['mood'] === 'angry') ? 'selected' : ''; ?>" data-mood="angry">😡</button>
                    <button class="mood-icon <?php echo ($post['mood'] === 'neutral') ? 'selected' : ''; ?>" data-mood="neutral">😐</button>
                </div>
                <!-- 숨겨진 input 요소로 선택된 mood 값을 저장 -->
                <input type="hidden" id="mood" name="mood" value="<?php echo htmlspecialchars($post['mood']); ?>">
                <div>
                    <label for="date">Date:</label>
                    <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($post['date']); ?>" required>
                </div>
                <div>
                    <label for="time">Time:</label>
                    <input type="time" id="time" name="time" value="<?php echo htmlspecialchars($post['time']); ?>" required>
                </div>
                <div>
                    <label for="artist">Artist:</label>
                    <input type="text" id="artist" name="artist" value="<?php echo htmlspecialchars($post['artist']); ?>" required>
                </div>
                <div>
                    <label for="song-title">Song Title:</label>
                    <input type="text" id="song-title" name="song-title" value="<?php echo htmlspecialchars($post['songTitle']); ?>" required>
                </div>
                <div>
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="5" required><?php echo htmlspecialchars($post['description']); ?></textarea>
                </div>
                <button type="submit">Save Changes</button>
            </form>
        <?php else: ?>
            <p>Post not found. <a href="main.html">Go back to Home</a></p>
        <?php endif; ?>
    </main>

    <script>
    document.querySelectorAll('.mood-icon').forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault();

            // 모든 버튼에서 selected 클래스 제거
            document.querySelectorAll('.mood-icon').forEach(btn => btn.classList.remove('selected'));

            // 클릭된 버튼에 selected 클래스 추가
            this.classList.add('selected');

            // 숨겨진 input 필드에 선택된 mood 값 설정
            document.getElementById('mood').value = this.dataset.mood;
        });
    });
</script>

</body>
</html>