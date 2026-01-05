<?php
// 1. INCLUDE DATABASE & SESSION
require 'db.php';

// 2. CHECK LOGIN (Security Gate)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// --- HANDLE LOGOUT ---
if ($page == 'logout') {
    session_destroy();
    header("Location: login.php");
    exit();
}

// --- HANDLE POST ACTIONS ---

// A. Handle Journal Entry
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['journal_entry'])) {
    $entry = trim($_POST['journal_entry']);
    if (!empty($entry)) {
        $entry = $conn->real_escape_string($entry);
        $conn->query("INSERT INTO journal (user_id, entry) VALUES ('$user_id', '$entry')");
        header("Location: index.php?page=journal");
        exit();
    }
}

// B. Reset Streak
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_streak'])) {
    $conn->query("INSERT INTO streak (user_id, start_date) VALUES ('$user_id', NOW())");
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
    $rank_color = "#888"; // Grey

    if ($days >= 3) { $rank = "Seeker"; $rank_color = "#fff"; }
    if ($days >= 7) { $rank = "Spartan"; $rank_color = "#ff9800"; }
    if ($days >= 21) { $rank = "Titan"; $rank_color = "#ff4444"; }
    if ($days >= 90) { $rank = "PHOENIX"; $rank_color = "#00e676"; }
    
} else {
    $conn->query("INSERT INTO streak (user_id, start_date) VALUES ('$user_id', NOW())");
    $days = 0; $hours = 0; $minutes = 0;
    $rank = "Initiate"; $rank_color = "#888";
}

// Daily Wisdom (Shared)
$quote_data = $conn->query("SELECT * FROM wisdom ORDER BY RAND() LIMIT 1")->fetch_assoc();
$daily_quote = $quote_data ? $quote_data['quote'] : "No wisdom found.";
$daily_author = $quote_data ? "- " . $quote_data['author'] : "";
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

        /* CLASSY TACTICAL BUTTON */
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

        .panic-sidebar-btn:hover {
            background: rgba(255, 82, 82, 0.15) !important;
            box-shadow: 0 0 10px rgba(255, 82, 82, 0.2);
            transform: translateY(-2px);
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

        /* --- ANIMATED BOXES --- */
        .box {
            /* Basic styling is handled inline, this handles the POP */
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

        .history-item {
            border-left: 3px solid var(--orange);
            padding-left: 15px;
            margin-bottom: 25px;
        }

        .date { font-size: 0.8rem; color: #888; margin-bottom: 5px; }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>

<body>
	<div id="briefing-modal" style="
    display: none; 
    position: fixed; 
    top: 0; left: 0; 
    width: 100%; height: 100%; 
    background: rgba(0,0,0,0.9); 
    z-index: 9999; 
    align-items: center; 
    justify-content: center;
">
    <div class="card" style="max-width: 400px; border: 1px solid #00e676; box-shadow: 0 0 30px rgba(0, 230, 118, 0.2);">
        <h2 style="color: #00e676; margin-top: 0;">🚀 PROTOCOL BRIEFING</h2>
        <hr style="border-color: #333;">
        
        <div style="text-align: left; margin: 20px 0;">
            <p><strong>OBJECTIVE:</strong> Maintain a 90-Day Discipline Streak.</p>
            
            <p><strong>1. 📝 UPLOAD INTEL:</strong><br>
            <span style="color: #888; font-size: 0.9rem;">Write a short daily log. Focus on wins/losses. No personal secrets required.</span></p>
            
            <p><strong>2. 🔥 INITIATE STREAK:</strong><br>
            <span style="color: #888; font-size: 0.9rem;">Click the button to decrypt your daily Puzzle Piece.</span></p>
            
            <p><strong>3. ⚔️ EVOLVE:</strong><br>
            <span style="color: #888; font-size: 0.9rem;">Complete Tribe Missions to level up your real-world skills.</span></p>
        </div>

        <button onclick="closeBriefing()" class="btn" style="width: 100%; background: #00e676; color: #000; font-weight: bold;">
            I UNDERSTAND
        </button>
    </div>
</div>

<script>
    // Check if user has seen the briefing
    function closeBriefing() {
        document.getElementById('briefing-modal').style.display = 'none';
        localStorage.setItem('phoenix_briefing_seen', 'true');
    }

    // Auto-show only on first visit
    if (!localStorage.getItem('phoenix_briefing_seen')) {
        document.getElementById('briefing-modal').style.display = 'flex';
    }
    
    // Function to open it manually (for the menu)
    function openBriefing() {
        document.getElementById('briefing-modal').style.display = 'flex';
    }
</script>
    <div id="mySidebar" class="sidebar">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
        <a href="?page=home">🏠 Dashboard</a>
        <a href="?page=journal">🔒 Private Journal</a>
        <a href="?page=settings">⚙️ Settings</a>
        <a href="?page=wisdom">🧠 Inspiration Deck</a>
        <a href="?page=feedback">💬 Send Feedback</a>
          <a href="#" onclick="openBriefing()">❓ App Purpose</a>
        <a href="?page=report">📊 Performance Report</a>
        <a href="?page=panic" class="panic-sidebar-btn">🚨 EMERGENCY</a>
      
    </div>

    <div class="navbar">
        <button class="menu-btn" onclick="openNav()">☰</button>
        <div class="app-title">PHOENIX v1.3</div>
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
                
                <div style="
                    position: relative; 
                    width: 100%; 
                    aspect-ratio: 10 / 9; 
                    border-radius: 12px; 
                    overflow: hidden; 
                    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
                    border: 1px solid #444;
                ">
                    
                    <div style="
                      background: url('https://images.pexels.com/photos/672636/pexels-photo-672636.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1');
                        background-size: cover; 
                        background-position: center;
                        width: 100%; 
                        height: 100%; 
                        position: absolute; 
                        top: 0; left: 0; 
                        z-index: 0;
                        filter: brightness(0.8);">
                    </div>

                    <div style="
                        display: grid; 
                        grid-template-columns: repeat(10, 1fr); 
                        grid-template-rows: repeat(9, 1fr); 
                        width: 100%; 
                        height: 100%; 
                        position: absolute; 
                        top: 0; left: 0; 
                        z-index: 1;">

                        <?php
                        // THE MAGIC RANDOMIZER
                        $shuffled_map = [
                            45, 12, 88, 3, 76, 22, 5, 9, 33, 67, 1, 90, 55, 44, 23, 11, 78, 65, 34, 2, 
                            8, 19, 29, 39, 49, 59, 69, 79, 89, 6, 16, 26, 36, 46, 56, 66, 76, 86, 7, 
                            17, 27, 37, 47, 57, 67, 77, 87, 13, 23, 53, 63, 73, 83, 14, 24, 54, 64, 
                            74, 84, 15, 25, 35, 55, 85, 4, 21, 31, 41, 51, 61, 71, 81, 10, 20, 30, 
                            40, 50, 60, 70, 80, 18, 28, 38, 48, 58, 68, 78, 88, 42, 52, 62, 72, 82
                        ];
                        
                        $unlocked_tiles = array_slice($shuffled_map, 0, $days); 

                        for ($i = 1; $i <= 90; $i++) {
                            $is_unlocked = in_array($i, $unlocked_tiles);
                            
                            // MODIFIED STYLE: Looks like metal plates instead of flat grey
                            if ($is_unlocked) {
                                // Revealed (Transparent)
                                $style = "background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1);";
                            } else {
                                // Locked (Metal Plate Look)
                                $style = "background: #222; border: 1px solid #333; box-shadow: inset 0 0 5px #000;";
                            }
                            
                            // Added class='box' for the pop animation
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

        <?php elseif ($page == 'journal'): ?>

            <h2 style="color: var(--orange);">🔒 Private Vault</h2>
            <p style="color: #888; font-size: 0.9rem;">Entries are stored locally. Be brutally honest.</p>

            <div class="card">
                <form method="POST">
                    <textarea name="journal_entry" placeholder="Log urges, triggers, or victories..."></textarea>
                    <button class="btn" type="submit">SAVE TO VAULT</button>
                </form>
            </div>

            <h3 style="margin-top: 30px; border-bottom: 1px solid #333; padding-bottom: 10px;">History</h3>
            <?php
            $logs = $conn->query("SELECT * FROM journal WHERE user_id='$user_id' ORDER BY id DESC LIMIT 20");
            while ($row = $logs->fetch_assoc()) {
                echo "<div class='history-item'>";
                echo "<div class='date'>" . date("d M Y • h:i A", strtotime($row['created_at'])) . "</div>";
                echo "<div>" . htmlspecialchars($row['entry']) . "</div>";
                echo "</div>";
            }
            ?>
        
        <?php elseif ($page == 'feedback'): ?>
            
            <?php
            // HANDLE SUBMISSION
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['feedback_msg'])) {
                $msg = $conn->real_escape_string($_POST['feedback_msg']);
                $u_name = $_SESSION['username'];
                
                $conn->query("INSERT INTO feedback (user_id, username, message) VALUES ('$user_id', '$u_name', '$msg')");
                echo "<div class='card' style='background:rgba(0,255,0,0.1); color:#00e676; text-align:center;'>✅ Feedback Sent! Thank you.</div>";
            }
            ?>

            <div class="card">
                <h3>Help Devloper To Make App Better</h3>
                <p>Found a bug? Have an idea? Tell the developer.</p>
                
                <form method="POST">
                    <textarea name="feedback_msg" rows="5" placeholder="Type your message here..." style="width:100%; background:#000; color:white; border:1px solid #333; padding:10px; border-radius:5px;" required></textarea>
                    <br><br>
                    <button type="submit" class="btn" style="width:100%;">SEND MESSAGE</button>
                </form>
            </div>

        <?php elseif ($page == 'panic'): ?>

            <h2 style="color: #ff4444; text-align: center;">⚠️ TACTICAL INTERVENTION</h2>
            <p style="text-align: center; color: #888;">Identify the enemy. What are you feeling?</p>

            <style>
                .panic-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
                .panic-btn {
                    padding: 30px; border: 1px solid #333; border-radius: 10px;
                    background: #222; color: white; font-size: 1.2rem; cursor: pointer; transition: 0.2s;
                }
                .panic-btn:hover { background: #333; transform: scale(1.05); }
                .solution {
                    display: none; background: #1a1a1a; padding: 20px;
                    border-left: 4px solid #00e676; margin-top: 20px; animation: slideIn 0.3s;
                }
            </style>

            <div class="panic-grid">
                <button class="panic-btn" onclick="showSolution('bored')">🥱 Boredom</button>
                <button class="panic-btn" onclick="showSolution('stress')">⚡ Stress</button>
                <button class="panic-btn" onclick="showSolution('lonely')">🌑 Loneliness</button>
                <button class="panic-btn" onclick="showSolution('urge')">🔥 Horny</button>
            </div>

            <div id="bored" class="solution">
                <h3>The Cure for Boredom</h3>
                <p>Boredom is the brain seeking dopamine. Give it <strong>achievement</strong> instead.</p>
                <ul>
                    <li>🍳 <strong>Chef Mode:</strong> Go cook your favorite meal. (No phone allowed).</li>
                    <li>🗣️ <strong>Real Talk:</strong> Call your parents or an old friend. Just say hi.</li>
                    <li>🎸 <strong>Flow State:</strong> Do your favorite hobby for 5 minutes.</li>
                    <li>💪 <strong>The Tax:</strong> Drop and do 20 pushups immediately.</li>
                </ul>
                <a href="?page=home" class="btn">MISSION COMPLETE</a>
            </div>   
        
            <div id="stress" class="solution">
                <h3>The Cure for Stress</h3>
                <p>Relapsing will not fix the stress; it will add <strong>Guilt</strong> to the Stress.</p>
                <ul>
                    <li>Take 5 deep breaths (4-7-8 technique).</li>
                    <li>Walk outside without your phone.</li>
                    <li>Drink a full glass of water.</li>
                </ul>
                <a href="?page=home" class="btn">I AM CALM</a>
            </div>

            <div id="lonely" class="solution">
                <h3>The Cure for Loneliness</h3>
                <p>You need connection, not pixels.</p>
                <ul>
                    <li>Text a friend: "Hey, what are you up to?"</li>
                    <li>Go sit in a public place (coffee shop/park).</li>
                    <li>Do not stay in your bedroom.</li>
                </ul>
                <a href="?page=home" class="btn">I WILL CONNECT</a>
            </div>

            <div id="urge" class="solution">
                <h3>The Cure for The Urge</h3>
                <p>This is just energy. Transmute it.</p>
                <ul>
                    <li><strong>Cold Shower:</strong> 30 seconds. Shock the system.</li>
                    <li><strong>The Mirror:</strong> Look at yourself. Is this who you want to be?</li>
                    <li><strong>Wait 10 Minutes:</strong> The urge acts like a wave. It will pass.</li>
                </ul>
                <a href="?page=home" class="btn">I AM STRONG</a>
            </div>

            <script>
                function showSolution(id) {
                    document.querySelectorAll('.solution').forEach(el => el.style.display = 'none');
                    document.getElementById(id).style.display = 'block';
                }
            </script>

        <?php elseif ($page == 'settings'): ?>

            <h2 style="color: var(--red);">⚠️ Danger Zone</h2>

            <div class="card" style="border: 1px solid var(--red);">
                <h3 style="margin-top: 0; color: var(--red);">Reset Protocol</h3>
                <p>If you relapsed, click the button below. This will set your streak back to 0 Days.</p>
                <form method="POST" onsubmit="return confirm('ARE YOU SURE? This will reset your streak counter to zero.');">
                    <input type="hidden" name="reset_streak" value="true">
                    <button class="btn btn-danger" type="submit">🛑 I RELAPSED - RESET TIMER</button>
                </form>
            </div>

            <div class="card">
                <h3>App Info</h3>
                <p><strong>Version:</strong> Phoenix v1.3</p>
                <p><strong>Database:</strong> MySQL (phoenix_db)</p>
                <p><strong>Devloper:</strong> Sandip Rathod</p>
            </div>

       <?php elseif ($page == 'wisdom'): ?>
            
            <?php
            // THE RANDOMIZER
            // 0-60 = Wisdom Quote (60% Chance)
            // 60-100 = Social Mission (40% Chance)
            $roll = rand(0, 100);
            
            $content = "";
            $title = "";
            $color = "";
            $icon = "";

            if($roll < 60) {
                // --- TYPE 1: STOIC WISDOM ---
                $q = $conn->query("SELECT * FROM wisdom ORDER BY RAND() LIMIT 1")->fetch_assoc();
                // If database is empty, show a default
                if (!$q) { 
                    $content = "The obstacle is the way."; 
                    $author = "Marcus Aurelius"; 
                } else {
                    $content = $q['quote']; 
                    $author = $q['author'];
                }
                
                $title = "ANCIENT DATA";
                $color = "#00e676"; // Green
                $icon = "🧠";
                $body = "<em>\"$content\"</em><br><br><small>- $author</small>";

            } else {
                // --- TYPE 2: TRIBE MISSION (HUMANIZED) ---
                $missions = [
                    "📞 **The Cold Call:** Call a friend you haven't spoken to in 6 months. Do not text. Call them.",
                    "👀 **The Eye Contact Drill:** When you buy something today, look the cashier in the eye and ask 'How is your shift going?'",
                    "🤐 **The Listener:** In your next conversation, ask 3 questions before you share your own opinion.",
                    "🗣️ **Speak Up:** Speak 10% louder than you feel comfortable with today. Let them hear you.",
                    "🤝 **The Genuine Compliment:** Find one real thing to compliment someone on (Work, Style, Effort). Say it out loud.",
                    "📵 **The Disconnect:** Eat a meal with family or friends with your phone left in another room."
                ];
                
                $content = $missions[array_rand($missions)];
                
                $title = "TRIBE MISSION";   // Changed from 'Social Operation'
                $color = "#ff9800";         // Orange (Alert Color)
                $icon = "🤝";               // Changed from Satellite to Handshake
                
                // Changed 'Execution Required' to 'Action Required'
                $body = "<div style='font-size:1.2rem; font-weight:bold;'>$content</div><br><small>Action Required.</small>";
            }
            ?>

            <div style="height: 80vh; display: flex; align-items: center; justify-content: center; flex-direction: column;">
                
                <div class="swipe-card" style="
                    background: linear-gradient(135deg, #1e1e1e, #252525);
                    border: 1px solid <?php echo $color; ?>;
                    border-radius: 20px;
                    padding: 40px;
                    width: 80%;
                    max-width: 400px;
                    text-align: center;
                    box-shadow: 0 0 30px <?php echo $color; ?>44;
                    position: relative;
                ">
                    <div style="
                        font-size: 3rem; 
                        position: absolute; 
                        top: -30px; 
                        left: 50%; 
                        transform: translateX(-50%); 
                        background: #111; 
                        padding: 10px; 
                        border-radius: 50%; 
                        border: 1px solid <?php echo $color; ?>;">
                        <?php echo $icon; ?>
                    </div>

                    <h3 style="color: <?php echo $color; ?>; letter-spacing: 3px; margin-top: 20px;"><?php echo $title; ?></h3>
                    <hr style="border-color: #333; margin: 20px 0;">
                    
                    <p style="font-size: 1.3rem; line-height: 1.6; color: #ddd;">
                        <?php echo $body; ?>
                    </p>
                    
                    <br><br>
                    
                    <a href="?page=wisdom" style="
                        background: <?php echo $color; ?>; 
                        color: #000; 
                        padding: 15px 25px;
                        border-radius: 30px; 
                        text-decoration: none; 
                        font-weight: bold;
                        box-shadow: 0 5px 20px <?php echo $color; ?>66;
                        transition: 0.2s;
                        display: inline-block;
                        white-space: nowrap;
                        font-size: 0.9rem;
                    ">
                        NEW OBJECTIVE ➔
                    </a>
                </div>

                <p style="color: #666; margin-top: 20px; font-size: 0.8rem;">Tap above to locate new target.</p>
            </div>
        <?php elseif ($page == 'report'): ?>

            <?php
            // 1. FETCH DATA (Last 7 Entries)
            $sql = "SELECT * FROM journal WHERE user_id = '$user_id' ORDER BY created_at DESC LIMIT 7";
            $result = $conn->query($sql);

            // 2. DEFINE KEYWORDS (The "Brain")
            $positive_triggers = ['gym', 'code', 'coding', 'study', 'focus', 'win', 'good', 'happy', 'done', 'read', 'walk', 'early'];
            $negative_triggers = ['bored', 'tired', 'sad', 'relapse', 'phone', 'scroll', 'lazy', 'fail', 'angry', 'hard', 'sleepy'];

            $pos_score = 0;
            $neg_score = 0;
            $detected_issues = [];

            // 3. ANALYZE ENTRIES
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $text = strtolower($row['entry']); // Convert to lowercase for matching
                    
                    // Check for Positives
                    foreach ($positive_triggers as $word) {
                        if (strpos($text, $word) !== false) {
                            $pos_score++;
                        }
                    }

                    // Check for Negatives
                    foreach ($negative_triggers as $word) {
                        if (strpos($text, $word) !== false) {
                            $neg_score++;
                            // Track specifically WHAT is causing the issue
                            if (!isset($detected_issues[$word])) {
                                $detected_issues[$word] = 1;
                            } else {
                                $detected_issues[$word]++;
                            }
                        }
                    }
                }
            }

            // 4. CALCULATE STATUS
            $total = $pos_score + $neg_score;
            if ($total == 0) {
                $status = "NO DATA";
                $color = "#888"; 
                $message = "Insufficient intel. Upload more logs.";
            } elseif ($pos_score > $neg_score) {
                $status = "BATTLE READY";
                $color = "#00e676"; // Green
                $message = "Systems optimal. Momentum is building.";
            } elseif ($neg_score > $pos_score) {
                $status = "COMPROMISED";
                $color = "#ff3d00"; // Red
                $message = "Performance degrading. Immediate intervention required.";
            } else {
                $status = "STABLE";
                $color = "#ffcc00"; // Yellow
                $message = "Holding the line. Increase output.";
            }

            // Find Top Enemy (The most frequent negative word)
            $primary_threat = "None";
            if (!empty($detected_issues)) {
                $primary_threat = array_search(max($detected_issues), $detected_issues);
            }
            ?>

            <h2 style="color: <?php echo $color; ?>;">📑 TACTICAL ANALYSIS (7 DAYS)</h2>

            <div class="card" style="border-left: 5px solid <?php echo $color; ?>; text-align: center;">
                <small>CURRENT MENTAL STATE</small>
                <h1 style="margin: 10px 0; font-size: 2.5rem; color: <?php echo $color; ?>;">
                    <?php echo $status; ?>
                </h1>
                <p><?php echo $message; ?></p>
            </div>

            <div class="card">
                <h3>⚠️ Threat Detection</h3>
                <p>Based on your mission logs, your primary obstacle is:</p>
                
                <div style="background: #222; padding: 15px; border-radius: 8px; border: 1px solid #444; margin-top: 10px; display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 1.2rem; font-weight: bold; color: #ff3d00; text-transform: uppercase;">
                        <?php echo $primary_threat; ?>
                    </span>
                    <small style="color: #666;">DETECTED FREQUENCY: HIGH</small>
                </div>
                
                <?php if ($primary_threat == 'phone' || $primary_threat == 'scroll'): ?>
                    <p style="color: #ffcc00; margin-top: 10px;">🛑 <strong>Directive:</strong> Install an app blocker immediately.</p>
                <?php elseif ($primary_threat == 'tired' || $primary_threat == 'sleepy'): ?>
                    <p style="color: #ffcc00; margin-top: 10px;">🛑 <strong>Directive:</strong> Shutdown screens at 10 PM tonight.</p>
                <?php elseif ($primary_threat == 'bored'): ?>
                    <p style="color: #ffcc00; margin-top: 10px;">🛑 <strong>Directive:</strong> View 'Mission Deck' for a task.</p>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3>📉 Raw Telemetry</h3>
                <p>Positive Signals: <strong style="color: #00e676;"><?php echo $pos_score; ?></strong></p>
                <p>Negative Signals: <strong style="color: #ff3d00;"><?php echo $neg_score; ?></strong></p>
            </div>
        
        <?php elseif ($page == 'classified'): ?>
            
            <?php
            // SECURITY CHECK: Only allow YOUR username to see this
            // Replace 'Sandip' with your exact username if different
            if ($_SESSION['username'] != 'test') {
                echo "<h2 style='color:red;'>🚫 ACCESS DENIED</h2>";
                echo "<p>This channel is encrypted. Authorization missing.</p>";
            } else {
                echo "<h2>🕵️ CLASSIFIED INTEL</h2>";
                echo "<p style='color:#666;'>Incoming transmissions from users.</p>";

                // Fetch all feedback from database
                $sql = "SELECT * FROM feedback ORDER BY created_at DESC";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<div class='card' style='border-left: 4px solid #00e676;'>";
                        echo "<small style='color:#888;'>" . $row['created_at'] . " | <strong>" . $row['username'] . "</strong></small>";
                        echo "<p style='margin-top:10px; font-size:1.1rem;'>" . htmlspecialchars($row['message']) . "</p>";
                        echo "</div>";
                    }
                } else {
                    echo "<div class='card'>No messages intercepted yet.</div>";
                }
            }
            ?>
        <?php endif; ?>

    </div> <script>
        function openNav() { document.getElementById("mySidebar").style.width = "250px"; }
        function closeNav() { document.getElementById("mySidebar").style.width = "0"; }
    </script>
    
    <div id="install-container" style="display:none; text-align:center; margin: 20px;">
        <button id="install-btn" style="background: #00e676; color: black; border: none; padding: 15px 30px; border-radius: 8px; font-weight: bold; font-size: 1rem;">
            📲 INSTALL PHOENIX APP
        </button>
    </div>

    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('sw.js').then(function(registration) {
                console.log('Service Worker Registered');
            });
        }
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            document.getElementById('install-container').style.display = 'block';
        });
        document.getElementById('install-btn').addEventListener('click', (e) => {
            document.getElementById('install-container').style.display = 'none';
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') { console.log('User accepted'); }
                deferredPrompt = null;
            });
        });
    </script>

</body>
</html>