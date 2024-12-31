<?php
session_start(); // 세션 시작
session_unset(); // 세션 변수 제거
session_destroy(); // 세션 삭제

// 로그인 페이지로 리디렉션
header('Location: main.html');
exit;
?>