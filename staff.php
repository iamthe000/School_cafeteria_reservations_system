<?php
define('ADMIN_PASSWORD_HASH', '$2y$10$G4fJemEI9H4TjlThKu.G7O405Pw1v/DG8dQPxaTGAwTHDw.d0fQDe'); // "admin123"のハッシュ

// セッション開始
session_start();

// ログインが成功した場合、管理ページを表示
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // 予約データの読み込み
    function loadReservations() {
        if (file_exists('reservations.json')) {
            return json_decode(file_get_contents('reservations.json'), true);
        }
        return [];
    }

    // 予約の削除
    if (isset($_POST['delete_reservation_id'])) {
        $reservations = loadReservations();
        $delete_id = $_POST['delete_reservation_id'];
        foreach ($reservations as $index => $reservation) {
            if ($reservation['reservation_id'] == $delete_id) {
                unset($reservations[$index]);
                break;
            }
        }
        // 更新された予約データを保存
        file_put_contents('reservations.json', json_encode(array_values($reservations), JSON_PRETTY_PRINT));
        $message = "予約が削除されました。";
    }

    // 予約の表示
    $reservations = loadReservations();
} else {
    // ログインフォームの処理
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['password'])) {
        $password = $_POST['password'];

        // パスワード確認
        if (password_verify($password, ADMIN_PASSWORD_HASH)) {
            $_SESSION['logged_in'] = true;
            header("Location: {$_SERVER['PHP_SELF']}"); // ログイン成功後、再読み込み
            exit;
        } else {
            $message = "パスワードが間違っています。";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>食堂予約システム - 管理者</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-top: 30px;
        }

        h2 {
            color: #333;
            margin-top: 20px;
        }

        form {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            margin: 20px auto;
            max-width: 400px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #5cb85c;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #4cae4c;
        }

        .message {
            text-align: center;
            margin-top: 20px;
            font-size: 18px;
            font-weight: bold;
            color: #5bc0de;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        table th, table td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }

        hr {
            margin: 40px 0;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>

    <?php if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] === false): ?>
        <!-- ログインフォーム -->
        <h1>ログイン - 管理者ページ</h1>
        <form method="POST">
            <label for="password">パスワード:</label>
            <input type="password" id="password" name="password" required><br>
            <button type="submit">ログイン</button>
        </form>

        <?php if (isset($message)) { echo "<p class='message'>$message</p>"; } ?>

    <?php else: ?>
        <!-- 管理者ページ -->
        <h1>食堂予約システム - 管理者ページ</h1>
        
        <?php if (isset($message)) { echo "<p class='message'>$message</p>"; } ?>

        <h2>予約一覧</h2>
        <table>
            <thead>
                <tr>
                    <th>予約番号</th>
                    <th>名前</th>
                    <th>予約品</th>
                    <th>予約時間</th>
                    <th>削除</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservations as $reservation): ?>
                    <tr>
                        <td><?php echo $reservation['reservation_id']; ?></td>
                        <td><?php echo $reservation['student_name']; ?></td>
                        <td><?php echo $reservation['menu_item']; ?></td>
                        <td><?php echo $reservation['reservation_time']; ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="delete_reservation_id" value="<?php echo $reservation['reservation_id']; ?>">
                                <button type="submit" onclick="return confirm('本当に削除しますか？');">削除</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <hr>
        <form method="POST" action="logout.php">
            <button type="submit">ログアウト</button>
        </form>

    <?php endif; ?>

</body>
</html>
