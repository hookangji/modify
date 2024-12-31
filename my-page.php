<?php
session_start();

// 로그인 확인
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    header('Location: login.html'); // 로그인 페이지로 리디렉션
    exit;
}

// 사용자 정보
$currentUser = $_SESSION['userID'] ?? 'Guest';

$jsonFile = 'posts.json'; // 게시물 JSON 파일 경로

// JSON 데이터 읽기
$posts = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];

// 현재 사용자와 이번 달 데이터 필터링
$currentMonth = date('Y-m');
$filteredPosts = array_filter($posts, function ($post) use ($currentMonth, $currentUser) {
    // 'date' 필드와 'userID'가 존재하는지 확인
    if (empty($post['date']) || empty($post['userID'])) {
        return false;
    }

    // 현재 월과 사용자 ID로 필터링
    return strpos($post['date'], $currentMonth) === 0 && $post['userID'] === $currentUser;
});

// 기분별 데이터 계산
$moodCounts = [];
foreach ($filteredPosts as $post) {
    $mood = $post['mood'] ?? 'neutral'; // 기분이 없으면 기본값 neutral
    $moodCounts[$mood] = ($moodCounts[$mood] ?? 0) + 1;
}
// 기분 이름과 이모티콘 매핑
$moodEmojis = [
    'happy' => '😊',
    'sad' => '😢',
    'angry' => '😡',
    'love' => '🥰',
    'neutral' => '😐'
];

// 가장 많이 작성한 기분 찾기
if (!empty($moodCounts)) {
    $mostFrequentMood = array_keys($moodCounts, max($moodCounts))[0];
} else {
    $mostFrequentMood = 'neutral';
}
// 가장 많이 작성한 기분의 이모티콘 가져오기
$mostFrequentMoodEmoji = $moodEmojis[$mostFrequentMood] ?? '';

// 리마인드용 데이터 처리
$reminderPosts = [];
$today = date('Y-m-d'); // 오늘 날짜
$currentYear = date('Y'); // 현재 연도
$startDate = date('m-d', strtotime("$today -7 days")); // 월-일 형식의 시작 날짜
$endDate = date('m-d', strtotime("$today +7 days")); // 월-일 형식의 종료 날짜

// 게시물 필터링: 과거 연도의 동일 날짜 범위
$reminderPosts = array_filter($posts, function ($post) use ($startDate, $endDate) {
    if (empty($post['date'])) return false;

    $postYear = date('Y', strtotime($post['date'])); // 게시물 연도
    $postMonthDay = date('m-d', strtotime($post['date'])); // 게시물 월-일

    // 현재 연도는 제외하고, 동일 월-일 범위 내 포함 여부 확인
    return $postYear < date('Y') && $postMonthDay >= $startDate && $postMonthDay <= $endDate;
});

// 별표 데이터 읽기
$favoritesFile = 'favorites.json';
$favorites = file_exists($favoritesFile) ? json_decode(file_get_contents($favoritesFile), true) : [];

// 현재 사용자의 별표된 게시물 필터링
$userFavorites = array_filter($favorites, function ($fav) use ($currentUser) {
    return $fav['userID'] === $currentUser;
});

// 별표된 게시물 가져오기
$favoritePosts = [];
foreach ($userFavorites as $favorite) {
    $postId = $favorite['postID'];
    if (isset($posts[$postId])) {
        $favoritePosts[] = $posts[$postId];
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Page</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="my-page.css">
</head>
<body>
    <header>
        <h1>Hello, <?php echo htmlspecialchars($currentUser); ?>!</h1>
        <a href="main.html" class="home-button" title="Back to Home">
            &#x1F3E0; <!-- Home Icon -->
        </a>
    </header>

    <div class="tabs">
        <button onclick="showTab('statistics')" class="active">mood statistics</button>
        <button onclick="showTab('favorites')">unforgettable moment</button>
        <button onclick="showTab('reminder')">remind</button>
    </div>
    

    <div id="statistics" class="tab-content active">
    <h2>mood statistics</h2>
    <!-- 연도 선택 -->
    <label for="year-select">Year:</label>
    <select id="year-select" onchange="updateMoodChart()">
        <option value="2019">2019</option>
        <option value="2020">2020</option>
        <option value="2021">2021</option>
        <option value="2022">2022</option>    
        <option value="2023">2023</option>
        <option value="2024" selected>2024</option>
    </select>

    <!-- 월 선택 -->
    <label for="month-select">Month:</label>
    <select id="month-select" onchange="updateMoodChart()">
        <option value="01">January</option>
        <option value="02">February</option>
        <option value="03">March</option>
        <option value="04">April</option>
        <option value="05">May</option>
        <option value="06">June</option>
        <option value="07">July</option>
        <option value="08">August</option>
        <option value="09">September</option>
        <option value="10">October</option>
        <option value="11">November</option>
        <option value="12" selected>December</option>
    </select>
        <canvas id="moodChart"></canvas>
        <p class="chart-comment" id="chart-comment">
            This month <strong><?php echo $mostFrequentMoodEmoji; ?></strong>
            You feel the most!
        </p>
    </div>

    <div id="favorites" class="tab-content">
    <h2>Unforgettable Moment: You heard this song at a really special moment! ❤️</h2>
    <?php if (!empty($favoritePosts)): ?>
        <ul class="favorites-list">
            <?php foreach ($favoritePosts as $index => $post): ?>
                <li class="favorite-item">
                    <!-- 노래 제목과 아티스트 -->
                    <strong class="song-title">
                        <?php echo htmlspecialchars($post['songTitle'] . ' - ' . $post['artist']); ?>
                    </strong>

                    <!-- 날짜와 기분 이모티콘 -->
                    <p class="post-meta">
                        <span class="post-mood">
                            <?php echo $moodEmojis[$post['mood']] ?? '❓'; ?>
                        </span>
                        <span class="post-date">
                            <?php echo htmlspecialchars($post['date']); ?>
                        </span>
                    </p>

                    <!-- 설명 -->
                    <p class="post-description"><?php echo htmlspecialchars($post['description']); ?></p>

                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="no-moments">No unforgettable moments yet.</p>
    <?php endif; ?>
</div>

    <div id="reminder" class="tab-content">
        <h2>Remind : Past Memories!</h2>
        <?php if (!empty($reminderPosts)): ?>
        <h3>Your Memories Around This Time</h3>
        <ul>
            <?php foreach ($reminderPosts as $post): ?>
                <li>
                    <strong><?php echo htmlspecialchars($post['date']); ?></strong> - 
                    <em><?php echo htmlspecialchars($post['songTitle']); ?></em> by 
                    <span><?php echo htmlspecialchars($post['artist']); ?></span>
                    <p><?php echo nl2br(htmlspecialchars($post['description'])); ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php else: ?>
            <p>No memories found around this time in past years.</p>
        <?php endif; ?>
    </div>

    <script>

document.addEventListener("DOMContentLoaded", () => {
    // localStorage에서 마지막 활성화된 탭 가져오기
    const lastActiveTab = localStorage.getItem("activeTab") || "statistics"; // 기본 탭: 'statistics'
    showTab(lastActiveTab); // 저장된 탭 활성화

    // 모든 탭 버튼에 클릭 이벤트 추가
    const tabButtons = document.querySelectorAll(".tabs button");
    tabButtons.forEach(button => {
        button.addEventListener("click", () => {
            const tabId = button.getAttribute("onclick").match(/showTab\('(.+)'\)/)[1]; // 탭 ID 추출
            localStorage.setItem("activeTab", tabId); // 선택된 탭 ID 저장
        });
    });
});

// 탭 전환 함수
function showTab(tabId) {
    // 모든 탭 콘텐츠와 버튼 초기화
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.tabs button').forEach(btn => btn.classList.remove('active'));

    // 선택된 탭 활성화
    document.getElementById(tabId).classList.add('active');
    document.querySelector(`.tabs button[onclick="showTab('${tabId}')"]`).classList.add('active');
}

        // 탭 전환 함수
        function showTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tabs button').forEach(btn => btn.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            document.querySelector(`.tabs button[onclick="showTab('${tabId}')"]`).classList.add('active');
        }

       // 전체 게시물 데이터를 PHP에서 전달
const allPosts = <?php echo json_encode($posts); ?>;

// 차트 초기화
let moodChart;

function initializeMoodChart() {
    const ctx = document.getElementById('moodChart').getContext('2d');
    moodChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [], // 초기 레이블
            datasets: [{
                label: 'Mood Frequency',
                data: [], // 초기 데이터
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    ticks: {
                        font: { size: 24 } // 이모티콘 크기
                    }
                },
                y: {
                    beginAtZero: true, // y축 0부터 시작
                    ticks: {
                        font: { size: 16 } // y축 숫자 크기
                    }
                }
            }
        }
    });
}

// 기분 데이터 업데이트 함수
function updateMoodChart() {
    const year = document.getElementById('year-select').value; // 선택된 연도
    const month = document.getElementById('month-select').value; // 선택된 월
    const selectedMonth = `${year}-${month}`; // "YYYY-MM" 형식으로 조합

    // 선택된 연도와 월의 게시물 필터링
    const filteredPosts = allPosts.filter(post => post.date && post.date.startsWith(selectedMonth));

    // 기분별 데이터 계산
    const moodCounts = {};
    filteredPosts.forEach(post => {
        const mood = post.mood || 'neutral';
        moodCounts[mood] = (moodCounts[mood] || 0) + 1;
    });

    // 차트 레이블과 데이터 업데이트
    const moodEmojis = {
        happy: '😊',
        sad: '😢',
        angry: '😡',
        love: '🥰',
        neutral: '😐'
    };
    const moodLabels = Object.keys(moodCounts).map(mood => moodEmojis[mood] || mood);
    const moodData = Object.values(moodCounts);

    // 차트 데이터 업데이트
    moodChart.data.labels = moodLabels;
    moodChart.data.datasets[0].data = moodData;
    moodChart.update();

    // 코멘트 업데이트
    const mostFrequentMood = Object.keys(moodCounts).reduce((a, b) =>
        moodCounts[a] > moodCounts[b] ? a : b, 'neutral'
    );
    const mostFrequentMoodEmoji = moodEmojis[mostFrequentMood] || '😐';
    document.getElementById('chart-comment').innerHTML = `
        This month <strong>${mostFrequentMoodEmoji}</strong> feel the most!
    `;
}

// 초기 차트 설정
initializeMoodChart();
updateMoodChart();
    </script>
</body>
</html>