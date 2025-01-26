<?php
// 予約データが格納されているJSONファイルのパス
define("RESERVATION_FILE", "reservations.json");

// 予約データを読み込む
function loadReservations() {
    if (file_exists(RESERVATION_FILE)) {
        return json_decode(file_get_contents(RESERVATION_FILE), true);
    }
    return [];
}

// 予約データを保存する
function saveReservations($data) {
    file_put_contents(RESERVATION_FILE, json_encode($data, JSON_PRETTY_PRINT));
}

// 予約フォームの処理
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 予約フォームが送信された場合
    if (isset($_POST["student_name"], $_POST["menu_item"])) {
        // 新しい予約を追加
        $student_name = $_POST["student_name"];
        $menu_item = $_POST["menu_item"];
        $reservation_time = date("Y-m-d H:i:s");

        $reservation_id = sprintf("%04d", rand(0, 9999));

        // 既存の予約データを読み込んで追加
        $reservations = loadReservations();
        $reservations[] = [
            "reservation_id" => $reservation_id,
            "student_name" => $student_name,
            "menu_item" => $menu_item,
            "reservation_time" => $reservation_time
        ];

        // 予約データを保存
        saveReservations($reservations);

        $message = "予約が完了しました。予約番号は " . $reservation_id . " です。";
    }

    // 予約確認フォームが送信された場合
    if (isset($_POST["reservation_id"])) {
        // 入力された予約番号を確認
        $reservation_id = $_POST["reservation_id"];
        $reservations = loadReservations();
        $found = false;
        foreach ($reservations as $reservation) {
            if ($reservation['reservation_id'] == $reservation_id) {
                $reservation_details = $reservation;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $message = "その予約番号は見つかりませんでした。";
        } else {
            $message = "予約確認: 名前: " . $reservation_details['student_name'] . "<br>" .
                       "予約品: " . $reservation_details['menu_item'] . "<br>" .
                       "予約時間: " . $reservation_details['reservation_time'] . "<br>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>食堂予約システム</title>
    <style>
body {
    font-family: 'Arial', sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f4f7fc;
    color: #333;
    line-height: 1.6;
}

h1, h2 {
    text-align: center;
    color: #222;
    margin: 0;
}

h1 {
    font-size: 2.5rem;
    margin-top: 60px;
    font-weight: 600;
    letter-spacing: 1px;
}

h2 {
    font-size: 1.8rem;
    margin-top: 30px;
    margin-bottom: 20px;
    font-weight: 500;
    color: #333;
}

form {
    background-color: #ffffff;
    border-radius: 12px;
    padding: 30px;
    margin: 20px auto;
    max-width: 85%;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border: 1px solid #ddd;
    transition: box-shadow 0.3s ease, transform 0.3s ease;
}

form:hover {
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    transform: translateY(-5px);
}

label {
    display: block;
    margin-bottom: 18px;
    font-weight: 500;
    font-size: 1.2rem;
    color: #555;
}

input[type="text"] {
    width: 100%;
    padding: 18px;
    margin-bottom: 22px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 1.1rem;
    background-color: #f8f9fa;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

input[type="text"]:focus {
    border-color: #007bff;
    outline: none;
    box-shadow: 0 0 6px rgba(0, 123, 255, 0.5);
}

button {
    width: 100%;
    padding: 18px;
    background-color: #007bff;
    border: none;
    color: #fff;
    font-size: 1.3rem;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.3s ease;
}

button:hover {
    background-color: #0056b3;
    transform: translateY(-2px);
}

button:active {
    transform: translateY(0);
}

.message {
    text-align: center;
    margin-top: 20px;
    font-size: 1.2rem;
    font-weight: 600;
    color: #28a745;
}

hr {
    margin: 40px auto;
    border: 0;
    height: 1px;
    background-color: #e0e0e0;
    width: 85%;
}

/* モバイルデバイス向けの最適化 */
@media (max-width: 600px) {
    h1 {
        font-size: 2.2rem;
        margin-top: 40px;
    }

    h2 {
        font-size: 1.6rem;
        margin-top: 20px;
    }

    form {
        padding: 25px;
        margin: 15px auto;
    }

    label {
        font-size: 1.1rem;
        margin-bottom: 15px;
    }

    input[type="text"] {
        font-size: 1.1rem;
        padding: 16px;
    }

    button {
        font-size: 1.2rem;
        padding: 16px;
    }

    .message {
        font-size: 1.1rem;
        margin-top: 18px;
    }

    hr {
        width: 90%;
    }
}

    </style>
</head>
<body>
    <h1>食堂予約システムBeta</h1>

    <!-- 予約フォーム -->
    <h2>予約する</h2><p>実在する予約品または生徒名を入力してください。存在しない場合は無効とします</p>
    <form method="POST">
        <label for="student_name">クラス番号名前:</label>
        <input type="text" id="student_name" name="student_name" required><br>
        <label for="menu_item">予約品:</label>
        <input type="text" id="menu_item" name="menu_item" required><br>
        <button type="submit">予約する</button>
    </form>

    <hr>

    <!-- 予約確認フォーム -->
    <h2>予約を確認する</h2>
    <form method="POST">
        <label for="reservation_id">予約番号:</label>
        <input type="text" id="reservation_id" name="reservation_id" required><br>
        <button type="submit">確認する</button>
    </form>

    <!-- メッセージ表示 -->
    <?php
    if (isset($message)) {
        echo "<p class='message'>$message</p>";
    }
    ?>
</body>
</html>
