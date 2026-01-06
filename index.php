<?php
// 1. INCLUDE DATABASE & SESSION
require 'db.php';
session_start(); // Ensure session is started

// 2. CHECK LOGIN (Security Gate)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// --- HANDLE PROFILE PICTURE UPLOAD (GLOBAL) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_pic_upload'])) {
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); } 
    
    $file_name = time() . "_" . basename($_FILES["profile_pic_upload"]["name"]);
    $target_file = $target_dir . $file_name;
    
    $check = getimagesize($_FILES["profile_pic_upload"]["tmp_name"]);
    if($check !== false) {
        if (move_uploaded_file($_FILES["profile_pic_upload"]["tmp_name"], $target_file)) {
            $conn->query("UPDATE users SET profile_pic = '$file_name' WHERE id = '$user_id'");
            // Refresh the current page to show the new image
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit();
        }
    }
}

// --- GET USER INFO (PHOTO) ---
$user_query = $conn->query("SELECT * FROM users WHERE id = '$user_id'");
$user_data = $user_query->fetch_assoc();
$profile_pic = !empty($user_data['profile_pic']) && file_exists("uploads/" . $user_data['profile_pic']) 
    ? "uploads/" . $user_data['profile_pic'] 
    : "https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_1280.png"; 

// --- HANDLE LOGOUT ---
if ($page == 'logout') {
    session_destroy();
    header("Location: login.php");
    exit();
}

// --- HANDLE STREAK ACTION ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'streak') {
    // Check if already streaked today to prevent double counting
    $today = date('Y-m-d');
    // Logic: We check streak table. If user has a streak, we don't insert new, we just let it count. 
    // Simplified Logic: The calculation is based on START date. 
    // So 'Initiating' just ensures they acknowledge the day.
    // For this version, we will just reload to show the celebration or unlock the tile.
    header("Location: index.php?page=home");
    exit();
}

// --- HANDLE RESET STREAK ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_streak'])) {
    // Update start date to NOW
    $conn->query("UPDATE streak SET start_date = NOW() WHERE user_id = '$user_id'");
    // If no row existed, insert one
    if ($conn->affected_rows == 0) {
        $conn->query("INSERT INTO streak (user_id, start_date) VALUES ('$user_id', NOW())");
    }
    header("Location: index.php?page=home");
    exit();
}

// --- DATA FETCHING ---

// Streak Calculation
$streak_res = $conn->query("SELECT start_date FROM streak WHERE user_id='$user_id' ORDER BY id DESC LIMIT 1");
if ($streak_res->num_rows > 0) {
    $row = $streak_res->fetch_assoc();
    $diff = time() - strtotime($row['start_date']);
    if ($diff < 0) $diff = 0;
    
    $days = floor($diff / (60 * 60 * 24));
    $hours = floor(($diff % (60 * 60 * 24)) / (60 * 60));
    $minutes = floor(($diff % (60 * 60)) / 60);
    
    // --- IDENTITY ENGINE (RANK SYSTEM) ---
    $rank = "Initiate";
    $rank_color = "#888"; 

    if ($days >= 3) { $rank = "Seeker"; $rank_color = "#fff"; }
    if ($days >= 7) { $rank = "Spartan"; $rank_color = "#ff9800"; }
    if ($days >= 21) { $rank = "Titan"; $rank_color = "#ff4444"; }
    if ($days >= 90) { $rank = "PHOENIX"; $rank_color = "#00e676"; }
    
} else {
    $conn->query("INSERT INTO streak (user_id, start_date) VALUES ('$user_id', NOW())");
    $days = 0; $hours = 0; $minutes = 0;
    $rank = "Initiate"; $rank_color = "#888";
}

// Daily Wisdom
$quote_data = $conn->query("SELECT * FROM wisdom ORDER BY RAND() LIMIT 1")->fetch_assoc();
$daily_quote = $quote_data ? $quote_data['quote'] : "The obstacle is the way.";
$daily_author = $quote_data ? "- " . $quote_data['author'] : "- Marcus Aurelius";
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="google-site-verification" content="O4M1iMTjRAm5R77OUH4O7R_0K-63ZSykwc63imjEQQc" />
    <link rel="manifest" href="manifest.php">
    <meta name="theme-color" content="#0f0f13">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
    <title>Phoenix Protocol</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        /* --- CSS VARIABLES & BASE --- */
        :root {
            --bg: #0f0f13;
            --card: #1d1d21;
            --green: #00e676;
            --orange: #ff9800;
            --red: #ff4444;
            --text: #fff;
        }

        body {
            margin: 0; padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background: var(--bg);
            color: var(--text);
            padding-bottom: 50px;
        }

        /* --- NAVIGATION --- */
        .navbar {
            background: #000;
            padding: 15px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #333;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .menu-btn {
            font-size: 24px;
            cursor: pointer;
            color: var(--text);
            background: none;
            border: none;
            margin-right: 15px;
        }

        .app-title {
            font-weight: bold;
            font-size: 1.2rem;
            color: var(--green);
            letter-spacing: 1px;
        }

        .sidebar {
            height: 100%;
            width: 0;
            position: fixed;
            z-index: 200;
            top: 0;
            left: 0;
            background-color: #111;
            overflow-x: hidden;
            transition: 0.3s;
            padding-top: 60px;
            border-right: 1px solid #333;
        }

        .sidebar a {
            padding: 15px 25px;
            text-decoration: none;
            font-size: 18px;
            color: #818181;
            display: block;
            transition: 0.3s;
            border-bottom: 1px solid #222;
        }

        .sidebar a:hover {
            color: #f1f1f1;
            background: #222;
        }

        .closebtn {
            position: absolute; top: 0; right: 25px;
            font-size: 36px; margin-left: 50px;
        }

        .panic-sidebar-btn {
            color: #ff5252 !important;
            border: 1px solid #ff5252;
            background: rgba(255, 82, 82, 0.05) !important;
            font-weight: bold;
            text-align: center;
            margin: 15px 15px;
            border-radius: 6px;
            padding: 10px 0 !important;
            transition: all 0.3s ease;
            display: block;
            letter-spacing: 1px;
            font-size: 0.9rem;
        }

        /* --- LAYOUT --- */
        .container {
            padding: 20px;
            max-width: 600px;
            margin: auto;
        }

        .card {
            background: var(--card);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border: 1px solid #333;
        }

        .big-text {
            font-size: 3rem;
            font-weight: bold;
            color: var(--green);
            margin: 10px 0;
        }

        .box {
            cursor: pointer;
            position: relative;
        }
        
        .box:hover {
            transform: scale(1.4);
            z-index: 10;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
            border: 1px solid #fff !important;
        }

        /* --- FORMS & BUTTONS --- */
        textarea {
            width: 100%;
            background: #000;
            border: 1px solid #444;
            color: #fff;
            padding: 15px;
            border-radius: 8px;
            box-sizing: border-box;
            min-height: 120px;
            font-family: inherit;
        }

        .btn {
            width: 100%;
            padding: 15px;
            background: var(--orange);
            border: none;
            color: #000;
            font-weight: bold;
            border-radius: 8px;
            margin-top: 10px;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-danger { background: var(--red); color: white; }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(255, 204, 0, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(255, 204, 0, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 204, 0, 0); }
        }
    </style>
</head>

<body>
    <div id="briefing-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 9999; align-items: center; justify-content: center;">
        <div class="card" style="max-width: 400px; border: 1px solid #00e676; box-shadow: 0 0 30px rgba(0, 230, 118, 0.2);">
            <h2 style="color: #00e676; margin-top: 0;">🚀 PROTOCOL BRIEFING</h2>
            <hr style="border-color: #333;">
            <div style="text-align: left; margin: 20px 0;">
                <p><strong>OBJECTIVE:</strong> Maintain a 90-Day Discipline Streak.</p>
                <p><strong>1. 📝 UPLOAD INTEL:</strong><br><span style="color: #888; font-size: 0.9rem;">Write a short daily log. Focus on wins/losses. No personal secrets required.</span></p>
                <p><strong>2. 🔥 INITIATE STREAK:</strong><br><span style="color: #888; font-size: 0.9rem;">Click the button to decrypt your daily Puzzle Piece.</span></p>
                <p><strong>3. ⚔️ EVOLVE:</strong><br><span style="color: #888; font-size: 0.9rem;">Complete Tribe Missions to level up your real-world skills.</span></p>
            </div>
            <button onclick="closeBriefing()" class="btn" style="width: 100%; background: #00e676; color: #000; font-weight: bold;">I UNDERSTAND</button>
        </div>
    </div>

    <script>
        function closeBriefing() {
            document.getElementById('briefing-modal').style.display = 'none';
            localStorage.setItem('phoenix_briefing_seen', 'true');
        }
        if (!localStorage.getItem('phoenix_briefing_seen')) {
            document.getElementById('briefing-modal').style.display = 'flex';
        }
        function openBriefing() {
            document.getElementById('briefing-modal').style.display = 'flex';
        }
    </script>

    <div id="mySidebar" class="sidebar">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
        <a href="?page=home">🏠 Dashboard</a>
        <a href="?page=journal">📓 Mission Log</a>
        <a href="?page=wisdom">🧠 Inspiration Deck</a>
        <a href="?page=report">📊 Performance Report</a>
        <a href="?page=feedback">💬 Send Feedback</a>
        <hr style="border-color: #333; width: 100%; margin: 10px 0;">
        <a href="#" onclick="openBriefing()">❓ Mission Briefing</a>
        <a href="?page=settings" style="color: #ff3d00;">⚙️ Settings</a>
        <a href="?page=logout" style="color: #666;">🚪 Logout</a>
        <a href="?page=panic" class="panic-sidebar-btn">🚨 EMERGENCY</a>
    </div>

    <div class="navbar">
        <button class="menu-btn" onclick="openNav()">☰</button>
        <div class="app-title">PHOENIX v1.3</div>

        <div style="position: absolute; top: 15px; right: 20px; text-align: center; z-index: 100;">
            <form action="" method="POST" enctype="multipart/form-data" id="navProfileForm">
                <label for="navPicUpload" style="cursor: pointer;">
                    <div style="width: 50px; height: 50px; border-radius: 50%; overflow: hidden; border: 2px solid #00e676; margin: 0 auto; background: #000;">
                        <img src="<?php echo $profile_pic; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                </label>
                <input type="file" id="navPicUpload" name="profile_pic_upload" accept="image/*" style="display:none;" onchange="document.getElementById('navProfileForm').submit()">
            </form>
            <div style="font-size: 0.7rem; color: #00e676; margin-top: 5px; font-weight: bold; text-transform: uppercase;">
                <?php echo $username; ?>
            </div>
        </div>
    </div>

    <div class="container">

        <?php if ($page == 'home'): ?>

            <div class="card" style="border-left: 4px solid var(--green);">
                <small style="color: #888; letter-spacing: 1px; font-weight: bold;">DAILY WISDOM</small>
                <p style="font-style: italic; font-size: 1.1rem; margin: 15px 0;">"<?php echo $daily_quote; ?>"</p>
                <div style="text-align: right; font-size: 0.9rem; color: #aaa;"><?php echo $daily_author; ?></div>
            </div>
            
            <div class="card" style="text-align: center; position: relative; overflow: hidden; border-left: 4px solid <?php echo $rank_color; ?>;">
                <div style="position: absolute; top: 10px; right: 10px; background: <?php echo $rank_color; ?>; color: #111; padding: 4px 8px; font-size: 0.7rem; font-weight: bold; border-radius: 4px; text-transform: uppercase;">
                    <?php echo $rank; ?>
                </div>
                <small style="color: #888; text-transform: uppercase; letter-spacing: 2px;">Protocol Streak</small>
                <div class="big-text" style="color: <?php echo $rank_color; ?>; text-shadow: 0 0 10px <?php echo $rank_color; ?>, 0 0 30px <?php echo $rank_color; ?>;">
                    <?php echo $days; ?>d <?php echo $hours; ?>h <?php echo $minutes; ?>m
                </div>
                <p style="margin: 0; font-size: 0.9rem; color: #666;">
                    Status: <strong style="color: <?php echo $rank_color; ?>"><?php echo $rank; ?></strong>
                </p>
            </div>
            
            <div class="card" style="padding: 0; background: transparent; border: none; overflow: visible;">
                <small style="color: #888; display: block; margin-bottom: 10px; font-weight: bold; letter-spacing: 1px;">90 DAY WAR MAP</small>
                <div style="position: relative; width: 100%; aspect-ratio: 10 / 9; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.5); border: 1px solid #444;">
                    
                    <div style="background: url('https://images.pexels.com/photos/672636/pexels-photo-672636.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1'); background-size: cover; background-position: center; width: 100%; height: 100%; position: absolute; top: 0; left: 0; z-index: 0; filter: brightness(0.8);"></div>

                    <div style="display: grid; grid-template-columns: repeat(10, 1fr); grid-template-rows: repeat(9, 1fr); width: 100%; height: 100%; position: absolute; top: 0; left: 0; z-index: 1;">
                        <?php
                        $shuffled_map = [45, 12, 88, 3, 76, 22, 5, 9, 33, 67, 1, 90, 55, 44, 23, 11, 78, 65, 34, 2, 8, 19, 29, 39, 49, 59, 69, 79, 89, 6, 16, 26, 36, 46, 56, 66, 76, 86, 7, 17, 27, 37, 47, 57, 67, 77, 87, 13, 23, 53, 63, 73, 83, 14, 24, 54, 64, 74, 84, 15, 25, 35, 55, 85, 4, 21, 31, 41, 51, 61, 71, 81, 10, 20, 30, 40, 50, 60, 70, 80, 18, 28, 38, 48, 58, 68, 78, 88, 42, 52, 62, 72, 82];
                        $unlocked_tiles = array_slice($shuffled_map, 0, $days); 
                        for ($i = 1; $i <= 90; $i++) {
                            $is_unlocked = in_array($i, $unlocked_tiles);
                            if ($is_unlocked) {
                                $style = "background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1);";
                            } else {
                                $style = "background: #222; border: 1px solid #333; box-shadow: inset 0 0 5px #000;";
                            }
                            echo "<div class='box' style='$style transition: 0.3s;'></div>";
                        }
                        ?>
                    </div>
                </div>
                <div style="text-align: center; margin-top: 15px;">
                    <p style="margin: 0; color: #666; font-size: 0.8rem; font-family: monospace; letter-spacing: 1px;">
                        PROTOCOL STATUS: <?php echo round(($days/90)*100); ?>% DECRYPTED
                    </p>
                </div>
            </div>

            <?php
            // Check if user wrote in the journal TODAY
            $today = date('Y-m-d');
            $sql_check = "SELECT * FROM journal WHERE user_id = '$user_id' AND DATE(created_at) = '$today'";
            $result_check = $conn->query($sql_check);
            $has_journaled = ($result_check->num_rows > 0);
            ?>

            <div style="text-align: center; margin-top: 20px;">
                <?php if (!$has_journaled): ?>
                    <a href="?page=journal" class="btn" style="background: #ffcc00; color: #000; display: block; width: 100%; font-weight: 800; animation: pulse 2s infinite; text-decoration: none; box-sizing: border-box;">
                        ⚠️ UPLOAD INTEL TO UNLOCK
                    </a>
                    <small style="display:block; margin-top:5px; color:#666;">*Daily report required to decrypt protocol.</small>
                <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="streak">
                        <button type="submit" class="btn" style="background: #00e676; color: #000; width: 100%; font-weight: 800; box-shadow: 0 0 20px #00e67666;">
                            🚀 INITIATE PROTOCOL
                        </button>
                    </form>
                <?php endif; ?>
            </div>

        <?php elseif ($page == 'journal'): ?>
            
            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['entry'])) {
                $entry = $conn->real_escape_string($_POST['entry']);
                $diet = isset($_POST['task_diet']) ? 1 : 0;
                $workout = isset($_POST['task_workout']) ? 1 : 0;
                $water = isset($_POST['task_water']) ? 1 : 0;
                $read = isset($_POST['task_read']) ? 1 : 0;
                $photo = isset($_POST['task_photo']) ? 1 : 0;

                $stmt = $conn->prepare("INSERT INTO journal (user_id, entry, task_diet, task_workout, task_water, task_read, task_photo) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isiiiii", $user_id, $entry, $diet, $workout, $water, $read, $photo);
                
                if ($stmt->execute()) {
                    echo "<div class='card' style='background:rgba(0,255,0,0.1); color:#00e676; text-align:center;'>✅ Intel Uploaded. Protocol Synced.</div>";
                    echo "<meta http-equiv='refresh' content='1;url=?page=home'>";
                }
            }
            ?>
            <div class="card">
                <h3>📓 Mission Log</h3>
                <p>Log your tactical status. What did you achieve?</p>
                <form method="POST">
                    <textarea name="entry" rows="5" placeholder="Day 4: Focus was good..." required></textarea>
                    <br><br>
                    <div style="background: #1a1a1a; padding: 15px; border-radius: 10px; border: 1px solid #333; margin-bottom: 20px;">
                        <h4 style="color: #00e676; margin-top: 0; margin-bottom: 10px; border-bottom: 1px solid #333; padding-bottom: 5px;">⚔️ PHOENIX CORE (Daily Ops)</h4>
                        <label style="display: flex; align-items: center; margin: 10px 0; cursor: pointer;"><input type="checkbox" name="task_diet" style="transform: scale(1.5); margin-right: 10px;"> <span>🚫 Strict Diet <small style="color:#666;">(No Cheat Meals)</small></span></label>
                        <label style="display: flex; align-items: center; margin: 10px 0; cursor: pointer;"><input type="checkbox" name="task_workout" style="transform: scale(1.5); margin-right: 10px;"> <span>🏋️ 2x Workouts <small style="color:#666;">(45m each)</small></span></label>
                        <label style="display: flex; align-items: center; margin: 10px 0; cursor: pointer;"><input type="checkbox" name="task_water" style="transform: scale(1.5); margin-right: 10px;"> <span>💧 Hydration <small style="color:#666;">(4 Liters)</small></span></label>
                        <label style="display: flex; align-items: center; margin: 10px 0; cursor: pointer;"><input type="checkbox" name="task_read" style="transform: scale(1.5); margin-right: 10px;"> <span>📚 Knowledge <small style="color:#666;">(10 Pages)</small></span></label>
                        <label style="display: flex; align-items: center; margin: 10px 0; cursor: pointer;"><input type="checkbox" name="task_photo" style="transform: scale(1.5); margin-right: 10px;"> <span>📸 Visual Check <small style="color:#666;">(Body/Face)</small></span></label>
                    </div>
                    <button type="submit" class="btn">UPLOAD INTEL</button>
                </form>
            </div>

        <?php elseif ($page == 'feedback'): ?>
            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['feedback_msg'])) {
                $msg = $conn->real_escape_string($_POST['feedback_msg']);
                $u_name = $_SESSION['username'];
                $conn->query("INSERT INTO feedback (user_id, username, message) VALUES ('$user_id', '$u_name', '$msg')");
                echo "<div class='card' style='background:rgba(0,255,0,0.1); color:#00e676; text-align:center;'>✅ Feedback Sent!</div>";
            }
            ?>
            <div class="card">
                <h3>Help Developer To Make App Better</h3>
                <form method="POST">
                    <textarea name="feedback_msg" rows="5" placeholder="Type your message here..." required></textarea>
                    <br><br>
                    <button type="submit" class="btn">SEND MESSAGE</button>
                </form>
            </div>

        <?php elseif ($page == 'panic'): ?>
            <h2 style="color: #ff4444; text-align: center;">⚠️ TACTICAL INTERVENTION</h2>
            <style>
                .panic-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
                .panic-btn { padding: 30px; border: 1px solid #333; border-radius: 10px; background: #222; color: white; font-size: 1.2rem; cursor: pointer; transition: 0.2s; }
                .panic-btn:hover { background: #333; transform: scale(1.05); }
                .solution { display: none; background: #1a1a1a; padding: 20px; border-left: 4px solid #00e676; margin-top: 20px; animation: slideIn 0.3s; }
            </style>
            <div class="panic-grid">
                <button class="panic-btn" onclick="showSolution('bored')">🥱 Boredom</button>
                <button class="panic-btn" onclick="showSolution('stress')">⚡ Stress</button>
                <button class="panic-btn" onclick="showSolution('lonely')">🌑 Loneliness</button>
                <button class="panic-btn" onclick="showSolution('urge')">🔥 Urge</button>
            </div>
            <div id="bored" class="solution"><h3>Cure for Boredom</h3><ul><li>Cook a meal.</li><li>Call a friend.</li><li>20 Pushups.</li></ul></div>    
            <div id="stress" class="solution"><h3>Cure for Stress</h3><ul><li>Deep breaths.</li><li>Walk outside.</li><li>Drink water.</li></ul></div>
            <div id="lonely" class="solution"><h3>Cure for Loneliness</h3><ul><li>Text a friend.</li><li>Go to a coffee shop.</li></ul></div>
            <div id="urge" class="solution"><h3>Cure for Urges</h3><ul><li>Cold Shower.</li><li>Look in the mirror.</li></ul></div>
            <script>function showSolution(id) { document.querySelectorAll('.solution').forEach(el => el.style.display = 'none'); document.getElementById(id).style.display = 'block'; }</script>

    <?php elseif ($page == 'settings'): ?>

            <h2 style="color: #fff; margin-bottom: 20px;">⚙️ SETTINGS & CONTROL</h2>

            <div class="card" style="margin-bottom: 20px;">
                <h3>🆔 Identity Card</h3>
                
                <div style="display: flex; align-items: center; gap: 15px;">
                    <img src="<?php echo $profile_pic; ?>" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 2px solid #00e676; flex-shrink: 0;">
                    
                    <form action="" method="POST" enctype="multipart/form-data" style="flex: 1; min-width: 0;">
                        <label style="color: #888; font-size: 0.9rem;">Update Photo:</label><br>
                        
                        <input type="file" name="profile_pic_upload" accept="image/*" required style="color: white; font-size: 0.8rem; max-width: 100%;">
                        
                        <button type="submit" class="btn" style="padding: 10px; font-size: 0.8rem; margin-top: 5px;">UPLOAD</button>
                    </form>
                </div>
            </div>

            <div class="card" style="border: 1px solid #00e676; box-shadow: 0 0 10px rgba(0, 230, 118, 0.1);">
                <h3 style="color: #00e676;">👨‍💻 The Architect</h3>
                
                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                    <div style="min-width: 50px; height: 50px; background: #222; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 1px solid #444; font-size: 1.5rem;">
                        💻
                    </div>
                    <div>
                        <div style="font-weight: bold; color: #fff;">Sandip Rathod</div>
                        <div style="font-size: 0.8rem; color: #888;">Full Stack Developer</div>
                    </div>
                </div>
                
                <p style="color: #bbb; font-size: 0.9rem; line-height: 1.5;">
                    Phoenix Protocol was built to fight the "Soft App" epidemic. 
                    No ads. No data selling. Just pure code and discipline.
                </p>

                <hr style="border-color: #333; margin: 20px 0;">

                <h4 style="margin-top:0;">☕ Fuel The Server</h4>
                <p style="color: #888; font-size: 0.9rem;">
                    If this protocol helped you stay disciplined, consider buying the developer a coffee to keep the system online.
                </p>

                <a href="upi://pay?pa=sandeeprathod67me@okicici&pn=Sandip%20Rathod&cu=INR" class="btn" style="
                    background: #25D366; 
                    color: #fff; 
                    text-decoration: none; 
                    display: flex; 
                    align-items: center; 
                    justify-content: center; 
                    gap: 10px;
                    font-weight: 800;
                    box-sizing: border-box; /* FIXED: Ensures padding doesn't make button wider than screen */
                ">
                    ⚡ SUPPORT VIA UPI
                </a>
                
                <p style="text-align: center; margin-top: 10px; font-size: 0.7rem; color: #666;">
                    Secure Direct Transfer (GPay/PhonePe/Paytm)
                </p>
            </div>

            <div class="card">
                <h3>ℹ️ System Info</h3>
                <p style="margin: 5px 0;"><strong>Version:</strong> Phoenix v1.3 (Stable)</p>
                <p style="margin: 5px 0;"><strong>Server:</strong> Linux / Apache</p>
                <p style="margin: 5px 0;"><strong>Database:</strong> MySQL (Encrypted)</p>
            </div>

            <h3 style="color: var(--red); margin-top: 40px; border-bottom: 1px solid var(--red); padding-bottom: 10px;">⚠️ ZONE ZERO</h3>
            
            <div class="card" style="border: 1px solid var(--red); background: rgba(255, 68, 68, 0.05);">
                <h4 style="margin-top: 0; color: var(--red);">Reset Protocol Timer</h4>
                <p style="font-size: 0.9rem; color: #aaa;">
                    This action only resets your <strong>Streak Counter</strong> to Day 0. 
                    <br>It does <strong>NOT</strong> delete your journal entries or charge you money.
                </p>
                <form method="POST" onsubmit="return confirm('ARE YOU SURE? This will reset your streak counter to zero.');">
                    <input type="hidden" name="reset_streak" value="true">
                    <button class="btn btn-danger" type="submit">🛑 I RELAPSED - RESET TIMER</button>
                </form>
            </div>

        <?php elseif ($page == 'wisdom'): ?>
            <?php
            $roll = rand(0, 100);
            if($roll < 60) {
                $q = $conn->query("SELECT * FROM wisdom ORDER BY RAND() LIMIT 1")->fetch_assoc();
                $content = $q ? $q['quote'] : "The obstacle is the way.";
                $title = "ANCIENT DATA"; $color = "#00e676"; $icon = "🧠";
                $body = "<em>\"$content\"</em><br><br><small>" . ($q ? $q['author'] : "Marcus Aurelius") . "</small>";
            } else {
                $missions = ["📞 Call a friend.", "👀 Eye Contact Drill.", "🤐 Listen more.", "🗣️ Speak Louder.", "🤝 Compliment someone.", "📵 No-Tech Dinner."];
                $content = $missions[array_rand($missions)];
                $title = "TRIBE MISSION"; $color = "#ff9800"; $icon = "🤝";
                $body = "<div style='font-size:1.2rem; font-weight:bold;'>$content</div><br><small>Action Required.</small>";
            }
            ?>
            <div style="height: 80vh; display: flex; align-items: center; justify-content: center; flex-direction: column;">
                <div class="swipe-card" style="background: linear-gradient(135deg, #1e1e1e, #252525); border: 1px solid <?php echo $color; ?>; border-radius: 20px; padding: 40px; width: 80%; max-width: 400px; text-align: center; box-shadow: 0 0 30px <?php echo $color; ?>44; position: relative;">
                    <div style="font-size: 3rem; position: absolute; top: -30px; left: 50%; transform: translateX(-50%); background: #111; padding: 10px; border-radius: 50%; border: 1px solid <?php echo $color; ?>;"><?php echo $icon; ?></div>
                    <h3 style="color: <?php echo $color; ?>; letter-spacing: 3px; margin-top: 20px;"><?php echo $title; ?></h3>
                    <hr style="border-color: #333; margin: 20px 0;">
                    <p style="font-size: 1.3rem; line-height: 1.6; color: #ddd;"><?php echo $body; ?></p>
                    <br><br>
                    <a href="?page=wisdom" style="background: <?php echo $color; ?>; color: #000; padding: 15px 25px; border-radius: 30px; text-decoration: none; font-weight: bold; box-shadow: 0 5px 20px <?php echo $color; ?>66; display: inline-block; white-space: nowrap; font-size: 0.9rem;">NEW OBJECTIVE ➔</a>
                </div>
            </div>

       <?php elseif ($page == 'report'): ?>

            <?php
            // ... (KEEP YOUR EXISTING PHP LOGIC HERE FOR FETCHING SCORES) ...
            // (I am not repeating the PHP logic to save space, just keep the top part of your code the same)
            // Just replace the HTML below the PHP closing tag ?>

            <div class="card" style="background: linear-gradient(45deg, #111, #1a1a1a); border: 1px solid #444;">
                <h3 style="margin-top: 0; color: #fff;">ℹ️ How This Works</h3>
                <p style="color: #bbb; font-size: 0.9rem; line-height: 1.5;">
                    This Tactical Analysis is generated by scanning your <strong>Mission Logs</strong> (Journal) for keywords like <em>"tired"</em>, <em>"gym"</em>, or <em>"focus"</em>.
                </p>
                <p style="color: #00e676; font-size: 0.9rem; font-weight: bold;">
                    💡 Tip: Write detailed entries every day to get accurate data.
                </p>
            </div>

            <h2 style="color: <?php echo $color; ?>;">📑 TACTICAL ANALYSIS (7 DAYS)</h2>

            <div class="card" style="border-left: 5px solid <?php echo $color; ?>; text-align: center;">
                <small>CURRENT MENTAL STATE</small>
                <h1 style="margin: 10px 0; font-size: 2.5rem; color: <?php echo $color; ?>;">
                    <?php echo $status; ?>
                </h1>
                <p><?php echo $message; ?></p>
            </div>


        <?php elseif ($page == 'classified'): ?>
            <?php if ($_SESSION['username'] != 'Sandip') { echo "<h2 style='color:red;'>🚫 ACCESS DENIED</h2>"; } 
            else {
                echo "<h2>🕵️ CLASSIFIED INTEL</h2>";
                $result = $conn->query("SELECT * FROM feedback ORDER BY created_at DESC");
                while($row = $result->fetch_assoc()) {
                    echo "<div class='card' style='border-left: 4px solid #00e676;'><small>".$row['username']."</small><p>".htmlspecialchars($row['message'])."</p></div>";
                }
            }
            ?>
        <?php endif; ?>

    </div>
    
    <script>
        function openNav() { document.getElementById("mySidebar").style.width = "250px"; }
        function closeNav() { document.getElementById("mySidebar").style.width = "0"; }
    </script>
    
    <div id="install-container" style="display:none; text-align:center; margin: 20px;">
        <button id="install-btn" style="background: #00e676; color: black; border: none; padding: 15px 30px; border-radius: 8px; font-weight: bold; font-size: 1rem;">📲 INSTALL PHOENIX APP</button>
    </div>
    <script>
        if ('serviceWorker' in navigator) { navigator.serviceWorker.register('sw.js'); }
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => { e.preventDefault(); deferredPrompt = e; document.getElementById('install-container').style.display = 'block'; });
        document.getElementById('install-btn').addEventListener('click', (e) => {
            document.getElementById('install-container').style.display = 'none'; deferredPrompt.prompt();
        });
    </script>
</body>
</html>
