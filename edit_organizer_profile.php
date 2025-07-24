<?php
session_start();
include 'includes/db_connection.php';
include 'includes/header.php';

// Check if user is logged in and is an organizer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'organizer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get organizer profile
$sql = "SELECT * FROM organizer_profiles WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

if (!$profile) {
    header("Location: organizer_dashboard.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $organization_name = $_POST['organization_name'];
    $contact_email = $_POST['contact_email'];
    $contact_phone = $_POST['contact_phone'];
    
    $sql = "UPDATE organizer_profiles SET 
            organization_name = ?,
            contact_email = ?,
            contact_phone = ?
            WHERE user_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $organization_name, $contact_email, $contact_phone, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Profile updated successfully!";
        header("Location: organizer_dashboard.php");
        exit();
    } else {
        $error = "Error updating profile. Please try again.";
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Edit Organizer Profile</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="organization_name" class="form-label">Organization Name</label>
                            <input type="text" class="form-control" id="organization_name" name="organization_name" 
                                   value="<?php echo htmlspecialchars($profile['organization_name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="contact_email" class="form-label">Contact Email</label>
                            <input type="email" class="form-control" id="contact_email" name="contact_email" 
                                   value="<?php echo htmlspecialchars($profile['contact_email']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="contact_phone" class="form-label">Contact Phone</label>
                            <input type="tel" class="form-control" id="contact_phone" name="contact_phone" 
                                   value="<?php echo htmlspecialchars($profile['contact_phone']); ?>" required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                            <a href="organizer_dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 