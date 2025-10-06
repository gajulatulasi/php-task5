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
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch();

// Check if post exists and user has permission
if(!$post){
    $_SESSION['error'] = "Post not found.";
    header("Location: index.php");
    exit;
}

// Authorization check
if($post['user_id'] != $_SESSION['user_id'] && $_SESSION['user_role'] != 'admin'){
    $_SESSION['error'] = "You don't have permission to edit this post.";
    header("Location: index.php");
    exit;
}

$errors = [];

if($_POST){
    $title   = trim(htmlspecialchars($_POST['title']));
    $content = trim(htmlspecialchars($_POST['content']));
    
    // Validation
    if(empty($title)) {
        $errors[] = "Title is required";
    } elseif(strlen($title) < 3) {
        $errors[] = "Title must be at least 3 characters";
    } elseif(strlen($title) > 255) {
        $errors[] = "Title must be less than 255 characters";
    }
    
    if(empty($content)) {
        $errors[] = "Content is required";
    } elseif(strlen($content) < 10) {
        $errors[] = "Content must be at least 10 characters";
    }
    
    if(empty($errors)){
        $stmt = $pdo->prepare("UPDATE posts SET title=?, content=? WHERE id=?");
        if($stmt->execute([$title, $content, $id])){
            $_SESSION['success'] = "Post updated successfully!";
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Failed to update post. Please try again.";
        }
    }
}

require 'includes/header.php';
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card card-custom">
            <div class="card-header">
                <h3 class="card-title mb-0">✏️ Edit Post</h3>
            </div>
            <div class="card-body">
                <?php if(!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0">
                            <?php foreach($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="editForm">
                    <div class="mb-3">
                        <label for="title" class="form-label">Post Title</label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?= htmlspecialchars($post['title']) ?>" 
                               required minlength="3" maxlength="255">
                        <div class="form-text">Title must be between 3 and 255 characters.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">Post Content</label>
                        <textarea class="form-control" id="content" name="content" rows="8"
                                  required minlength="10"><?= htmlspecialchars($post['content']) ?></textarea>
                        <div class="form-text">Content must be at least 10 characters.</div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="index.php" class="btn btn-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-gradient">💾 Update Post</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>