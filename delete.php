<?php
require 'config.php';
require 'includes/auth_check.php';

// Validate and sanitize ID
$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['error'] = "Invalid post ID.";
    header("Location: index.php");
    exit;
}

// Get post using prepared statement
$stmt = $pdo->prepare("SELECT p.*, u.username as author_name 
                      FROM posts p 
                      LEFT JOIN users u ON p.user_id = u.id 
                      WHERE p.id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch();

if(!$post){
    $_SESSION['error'] = "Post not found.";
    header("Location: index.php");
    exit;
}

// Authorization check
if($post['user_id'] != $_SESSION['user_id'] && $_SESSION['user_role'] != 'admin'){
    $_SESSION['error'] = "You don't have permission to delete this post.";
    header("Location: index.php");
    exit;
}

// If confirmed delete
if(isset($_POST['confirm_delete'])){
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    if($stmt->execute([$id])){
        $_SESSION['success'] = "Post deleted successfully!";
        header("Location: index.php");
        exit;
    } else {
        $error = "Failed to delete post. Please try again.";
    }
}

require 'includes/header.php';
?>

<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card card-custom">
            <div class="card-header bg-warning">
                <h3 class="card-title mb-0 text-dark">⚠️ Confirm Deletion</h3>
            </div>
            <div class="card-body text-center">
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <div class="alert alert-warning">
                    <h5>Are you sure you want to delete this post?</h5>
                    <p class="mb-2"><strong>"<?= htmlspecialchars($post['title']) ?>"</strong></p>
                    <p class="text-muted small">
                        Created by: <?= htmlspecialchars($post['author_name']) ?><br>
                        Date: <?= date("F j, Y", strtotime($post['created_at'])) ?>
                    </p>
                    <p class="mb-0"><strong>This action cannot be undone!</strong></p>
                </div>

                <form method="post" class="d-flex justify-content-center gap-3">
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" name="confirm_delete" class="btn btn-danger">
                        🗑️ Yes, Delete Permanently
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>