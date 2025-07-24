<?php
session_start();
include 'includes/db_connection.php';
include 'includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_type = $_POST['user_type'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Check if username already exists
    $check_username_sql = "SELECT username FROM users WHERE username = ?";
    $check_username_stmt = $conn->prepare($check_username_sql);
    $check_username_stmt->bind_param("s", $username);
    $check_username_stmt->execute();
    $username_result = $check_username_stmt->get_result();
    
    // Check if email already exists
    $check_email_sql = "SELECT email FROM users WHERE email = ?";
    $check_email_stmt = $conn->prepare($check_email_sql);
    $check_email_stmt->bind_param("s", $email);
    $check_email_stmt->execute();
    $email_result = $check_email_stmt->get_result();
    
    if ($username_result->num_rows > 0) {
        $error = "Username already taken. Please choose a different username.";
    } elseif ($email_result->num_rows > 0) {
        $error = "Email already registered. Please use a different email address.";
    } else {
        // Insert into users table
        $sql = "INSERT INTO users (username, email, password, user_type) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $username, $email, $password, $user_type);
        
        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;
            
            if ($user_type == 'player') {
                // Insert into player_profiles
                $full_name = $_POST['full_name'];
                $age = $_POST['age'];
                $gender = $_POST['gender'];
                $college_name = $_POST['college_name'];
                $state = $_POST['state'];
                $city = $_POST['city'];
                $selected_game_domain = $_POST['selected_game_domain'];
                
                // First, check if college exists
                $sql = "SELECT college_id FROM colleges WHERE college_name = ? AND state = ? AND city = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sss", $college_name, $state, $city);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $college_id = $result->fetch_assoc()['college_id'];
                } else {
                    // Insert new college
                    $sql = "INSERT INTO colleges (college_name, state, city) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sss", $college_name, $state, $city);
                    $stmt->execute();
                    $college_id = $stmt->insert_id;
                }
                
                // Insert into player_profiles with college_id
                $sql = "INSERT INTO player_profiles (user_id, full_name, age, gender, college_id, selected_game_domain) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isssss", $user_id, $full_name, $age, $gender, $college_id, $selected_game_domain);
                
                if ($stmt->execute()) {
                    // Insert into players table
                    $sql = "INSERT INTO players (user_id, game_domain, in_game_name) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("iss", $user_id, $selected_game_domain, $username);
                    $stmt->execute();
                    
                    // Insert into player_rankings table using user_id
                    $sql = "INSERT INTO player_rankings (player_id, game_domain) VALUES (?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("is", $user_id, $selected_game_domain);
                    $stmt->execute();
                    
                    $_SESSION['success'] = "Registration successful! Please login.";
                    header("Location: login.php");
                    exit();
                }
            } else {
                // Insert into organizer_profiles
                $organization_name = $_POST['organization_name'];
                $contact_email = $_POST['contact_email'];
                $contact_phone = $_POST['contact_phone'];
                
                $sql = "INSERT INTO organizer_profiles (user_id, organization_name, contact_email, contact_phone) 
                        VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isss", $user_id, $organization_name, $contact_email, $contact_phone);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Registration successful! Please login.";
                    header("Location: login.php");
                    exit();
                }
            }
        }
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">Register</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <ul class="nav nav-tabs" id="registerTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="player-tab" data-bs-toggle="tab" href="#player" role="tab">Player</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="organizer-tab" data-bs-toggle="tab" href="#organizer" role="tab">Organizer</a>
                        </li>
                    </ul>
                    
                    <div class="tab-content mt-3" id="registerTabsContent">
                        <!-- Player Registration Form -->
                        <div class="tab-pane fade show active" id="player" role="tabpanel">
                            <form method="POST" action="">
                                <input type="hidden" name="user_type" value="player">
                                
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" required 
                                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" required
                                           value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="age" class="form-label">Age</label>
                                        <input type="number" class="form-control" id="age" name="age" required
                                               value="<?php echo isset($_POST['age']) ? htmlspecialchars($_POST['age']) : ''; ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="gender" class="form-label">Gender</label>
                                        <select class="form-select" id="gender" name="gender" required>
                                            <option value="Male" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                            <option value="Female" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                            <option value="Other" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="college_name" class="form-label">College Name</label>
                                    <input type="text" class="form-control" id="college_name" name="college_name" required
                                           value="<?php echo isset($_POST['college_name']) ? htmlspecialchars($_POST['college_name']) : ''; ?>">
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="state" class="form-label">State</label>
                                        <input type="text" class="form-control" id="state" name="state" required
                                               value="<?php echo isset($_POST['state']) ? htmlspecialchars($_POST['state']) : ''; ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="city" class="form-label">City</label>
                                        <input type="text" class="form-control" id="city" name="city" required
                                               value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="selected_game_domain" class="form-label">Game Domain</label>
                                    <select class="form-select" id="selected_game_domain" name="selected_game_domain" required>
                                        <option value="Valorant" <?php echo (isset($_POST['selected_game_domain']) && $_POST['selected_game_domain'] == 'Valorant') ? 'selected' : ''; ?>>Valorant</option>
                                        <option value="CS:GO" <?php echo (isset($_POST['selected_game_domain']) && $_POST['selected_game_domain'] == 'CS:GO') ? 'selected' : ''; ?>>CS:GO</option>
                                        <option value="Dota 2" <?php echo (isset($_POST['selected_game_domain']) && $_POST['selected_game_domain'] == 'Dota 2') ? 'selected' : ''; ?>>Dota 2</option>
                                        <option value="League of Legends" <?php echo (isset($_POST['selected_game_domain']) && $_POST['selected_game_domain'] == 'League of Legends') ? 'selected' : ''; ?>>League of Legends</option>
                                        <option value="PUBG" <?php echo (isset($_POST['selected_game_domain']) && $_POST['selected_game_domain'] == 'PUBG') ? 'selected' : ''; ?>>PUBG</option>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Register as Player</button>
                            </form>
                        </div>
                        
                        <!-- Organizer Registration Form -->
                        <div class="tab-pane fade" id="organizer" role="tabpanel">
                            <form method="POST" action="">
                                <input type="hidden" name="user_type" value="organizer">
                                
                                <div class="mb-3">
                                    <label for="org_username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="org_username" name="username" required
                                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="org_email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="org_email" name="email" required
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="org_password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="org_password" name="password" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="organization_name" class="form-label">Organization Name</label>
                                    <input type="text" class="form-control" id="organization_name" name="organization_name" required
                                           value="<?php echo isset($_POST['organization_name']) ? htmlspecialchars($_POST['organization_name']) : ''; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="contact_email" class="form-label">Contact Email</label>
                                    <input type="email" class="form-control" id="contact_email" name="contact_email" required
                                           value="<?php echo isset($_POST['contact_email']) ? htmlspecialchars($_POST['contact_email']) : ''; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="contact_phone" class="form-label">Contact Phone</label>
                                    <input type="tel" class="form-control" id="contact_phone" name="contact_phone" required
                                           value="<?php echo isset($_POST['contact_phone']) ? htmlspecialchars($_POST['contact_phone']) : ''; ?>">
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Register as Organizer</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 