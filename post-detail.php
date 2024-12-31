<?php
session_start(); // 세션 시작

// JSON 파일 경로
$jsonFile = 'posts.json';

// JSON 데이터 읽기
$posts = [];
if (file_exists($jsonFile)) {
    $fileContents = file_get_contents($jsonFile);
    if (!empty($fileContents)) {
        $posts = json_decode($fileContents, true);
    }
}

// GET 파라미터로 전달된 ID 가져오기
$postId = isset($_GET['id']) ? (int)$_GET['id'] : -1;

// 게시글 데이터 가져오기
$post = ($postId >= 0 && isset($posts[$postId])) ? $posts[$postId] : null;

// 감정 이모티콘 매핑
$moodIcons = [
    'love' => '🥰',
    'happy' => '😊',
    'sad' => '😢',
    'angry' => '😡',
    'neutral' => '😐'
];

// JSON 파일 경로
$favoritesFile = 'favorites.json';

// 별표 저장 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $favorites = file_exists($favoritesFile) ? json_decode(file_get_contents($favoritesFile), true) : [];
    $userID = $_SESSION['userID'];

    // 중복 체크
    foreach ($favorites as $favorite) {
        if ($favorite['userID'] === $userID && $favorite['postID'] == $postId) {
            header('Location: my-page.php?tab=favorites');
            exit;
        }
    }

    // 새로운 즐겨찾기 추가
    $favorites[] = ['userID' => $userID, 'postID' => $postId];
    file_put_contents($favoritesFile, json_encode($favorites, JSON_PRETTY_PRINT));
    header('Location: my-page.php?tab=favorites');
    exit;
}

$isFavorited = false;
if (file_exists('favorites.json')) {
    $favorites = json_decode(file_get_contents('favorites.json'), true);
    foreach ($favorites as $favorite) {
        if ($favorite['userID'] === $_SESSION['userID'] && (int)$favorite['postID'] === (int)$_GET['id']) {
            $isFavorited = true;
            break;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Details</title>
    <style>
        /* 전체 배경 색상 및 레이아웃 */
body {
    font-family: Arial, sans-serif;
    background-color: #fffafa; /* 부드러운 배경색 */
    margin: 0;
    padding: 0;
    color: #333; /* 기본 텍스트 색상 */
}

/* 헤더 스타일 */
header {
    display: flex;
    align-items: center;
    padding: 10px 20px;
    background: linear-gradient(90deg, #ff4b4b, #ff6f6f); /* 그라데이션 헤더 */
    border-bottom: 2px solid #ff8c8c;
    box-shadow: 0px 4px 6px rgba(255, 75, 75, 0.4);
}

/* 홈 버튼 스타일 */
header .home-button {
    text-decoration: none;
    color: #fff;
    font-size: 24px;
    margin-left: auto;
    padding: 10px;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.2); /* 투명 배경 */
    border: 1px solid rgba(255, 255, 255, 0.6); /* 테두리 */
    text-align: center;
    cursor: pointer;
    transition: background-color 0.3s ease, color 0.3s ease;
}

header .home-button:hover {
    background-color: #fff; /* 호버 시 배경 변경 */
    color: #ff4b4b; /* 아이콘 색상 변경 */
}

/* 게시글 상세 정보 */
.post-detail {
    max-width: 900px;
    margin: 30px auto;
    padding: 30px;
    background-color: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border: 2px solid #ffdddd;
    text-align: left;
}

/* 제목 스타일 */
.post-detail h3 {
    margin-bottom: 20px;
    font-size: 30px;
    color: #333;
    font-weight: bold;
}

/* 텍스트 스타일 */
.post-detail p {
    margin-bottom: 15px;
    font-size: 20px;
    line-height: 1.8;
    color: #555;
}

.post-detail p strong {
    color: #ff4b4b; /* 강조 텍스트 */
}

/* 버튼 컨테이너 */
.post-detail .buttons {
    display: flex;
    justify-content: flex-end;
    gap: 15px;
    margin-top: 20px;
}

/* 버튼 스타일 */
.post-detail .buttons button {
    padding: 12px 20px;
    font-size: 16px;
    font-weight: bold;
    color: white;
    background: linear-gradient(45deg, #ff4b4b, #ff6f6f);
    border: none;
    border-radius: 50px;
    cursor: pointer;
    box-shadow: 0px 4px 8px rgba(255, 75, 75, 0.4);
    transition: background 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
}

.post-detail .buttons button:hover {
    background: linear-gradient(45deg, #ff6f6f, #ff4b4b);
    transform: scale(1.05);
    box-shadow: 0px 8px 12px rgba(255, 75, 75, 0.6);
}
.mood-icon {
    font-size: 50px; /* 원하는 크기로 조정 */
    line-height: 1;
    vertical-align: middle; /* 텍스트와 정렬 */
}

/* 별표 버튼 기본 스타일 */
.favorite-btn {
    font-size: 23px;
    color: #ff4b4b;
    background: none;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px; /* 아이콘과 텍스트 간격 */
    transition: transform 0.2s ease, color 0.3s ease; /* 부드러운 전환 효과 */
    text-shadow: 0 0 8px rgba(255, 193, 7, 0.6); /* 빛나는 효과 */

}

/* 호버 효과 */
.favorite-btn:hover {
    transform: scale(1.2); /* 크기 살짝 확대 */
    color: #ffc107; /* 으로 변경 */
}

/* 활성화된 별표 */
.favorite-btn.active {
    color: #ffc107; /* 금색 강조 */
    text-shadow: 0 0 8px rgba(255, 193, 7, 0.6); /* 빛나는 효과 */
}

/* 활성화된 별표 아이콘 */
.favorite-btn.active .favorite-icon {
    font-size: 24px;
}

    </style>
</head>
<body>
<header>
    <a href="main.html" class="home-button" title="Back to Home">
        &#x1F3E0; <!-- Home Icon -->
    </a>
</header>
<main>
    <?php if ($post): ?>
        <h1 style="text-align: center; color: #ff4b4b;">Post Details</h1>
        <div class="post-detail">
            <p>
                <?php 
                if (!empty($post['mood']) && isset($moodIcons[$post['mood']])) {
                    echo '<span class="mood-icon">' . $moodIcons[$post['mood']] . '</span>'; // 이모티콘만 출력
                } else {
                    echo '<span class="mood-icon">No mood recorded.</span>';
                }
                ?>
            </p>
            <h3><?php echo htmlspecialchars($post['date'] . ' ' . $post['time']); ?></h3>
            <p><strong>Artist:</strong> <?php echo htmlspecialchars($post['artist']); ?></p>
            <p><strong>Song Title:</strong> <?php echo htmlspecialchars($post['songTitle']); ?></p>
            <p><strong>Description:</strong></p>
            <p><?php echo nl2br(htmlspecialchars($post['description'])); ?></p>
            <div class="buttons">
            <button onclick="location.href='edit-post.php?id=<?php echo $post['postID']; ?>'">Edit</button>
            <button onclick="location.href='delete-post.php?id=<?php echo $postId; ?>'">Delete</button>
            </div>

            <!-- 별표 버튼 -->
            <form id="favorite-form" method="POST" data-post-id="<?php echo $postId; ?>" style="text-align: right;">
                <button type="button" name="favorite" class="favorite-btn <?php echo $isFavorited ? 'active' : ''; ?>">
                    <span class="favorite-icon"><?php echo $isFavorited ? '❌' : '⭐'; ?></span>
                    <span class="favorite-text"><?php echo $isFavorited ? 'Unfavorite' : 'Favorite'; ?></span>
                </button>
            </form>

        </div>
    <?php else: ?>
        <p style="text-align: center; color: #ff4b4b;">Post not found. <a href="main.php" style="color: #007bff; text-decoration: underline;">Go back to Home</a></p>
    <?php endif; ?>
</main>
<script>
    document.addEventListener("DOMContentLoaded", () => {
    const favoriteForm = document.getElementById("favorite-form");
    const favoriteButton = favoriteForm.querySelector("button[name='favorite']");
    const postId = favoriteForm.getAttribute("data-post-id");

    favoriteButton.addEventListener("click", () => {
        const isFavorited = favoriteButton.classList.contains("active");

        // AJAX 요청으로 서버에 저장/삭제 요청
        fetch("favorite-handler.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ postId: postId, action: isFavorited ? "unfavorite" : "favorite" })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // 버튼 상태 토글
                favoriteButton.classList.toggle("active");
                favoriteButton.querySelector(".favorite-icon").textContent = isFavorited ? "⭐" : "❌";
                favoriteButton.querySelector(".favorite-text").textContent = isFavorited ? "Favorite" : "Unfavorite";
            } else {
                alert("Error updating favorite status.");
            }
        })
        .catch(error => console.error("Error:", error));
    });
});
</script>
</body>
</html>