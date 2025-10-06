<?php
require 'config.php';
require 'includes/auth_check.php';

// Input validation and security
$keyword = isset($_GET['search']) ? trim(htmlspecialchars($_GET['search'])) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// Fetch posts with prepared statements
if (!empty($keyword)) {
    $query = "SELECT p.*, u.username as author_name 
              FROM posts p 
              LEFT JOIN users u ON p.user_id = u.id 
              WHERE p.title LIKE :keyword OR p.content LIKE :keyword 
              ORDER BY p.created_at DESC 
              LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($query);
    $searchTerm = "%$keyword%";
    $stmt->bindParam(':keyword', $searchTerm, PDO::PARAM_STR);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $posts = $stmt->fetchAll();
} else {
    $query = "SELECT p.*, u.username as author_name 
              FROM posts p 
              LEFT JOIN users u ON p.user_id = u.id 
              ORDER BY p.created_at DESC 
              LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $posts = $stmt->fetchAll();
}

// Count total posts for pagination
if (!empty($keyword)) {
    $countQuery = "SELECT COUNT(*) as total FROM posts WHERE title LIKE :keyword OR content LIKE :keyword";
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->bindParam(':keyword', $searchTerm, PDO::PARAM_STR);
    $countStmt->execute();
} else {
    $countQuery = "SELECT COUNT(*) as total FROM posts";
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute();
}

$totalRows = $countStmt->fetch()['total'];
$totalPages = ceil($totalRows / $limit);

require 'includes/header.php';
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <!-- Search Form -->
        <div class="card card-custom mb-4">
            <div class="card-body">
                <form method="GET" class="d-flex">
                    <input type="text" class="form-control me-2" name="search" 
                           placeholder="🔍 Search posts by title or content..."
                           value="<?= htmlspecialchars($keyword) ?>">
                    <button class="btn btn-gradient" type="submit">Search</button>
                    <?php if(!empty($keyword)): ?>
                        <a href="index.php" class="btn btn-outline-secondary ms-2">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Posts List -->
        <?php if (count($posts) > 0): ?>
            <?php foreach ($posts as $post): ?>
                <div class="card card-custom mb-4">
                    <div class="card-body">
                        <h3 class="card-title text-primary"><?= htmlspecialchars($post['title']) ?></h3>
                        
                        <div class="post-content mb-3">
                            <?= nl2br(htmlspecialchars(substr($post['content'], 0, 300))) ?>
                            <?php if(strlen($post['content']) > 300): ?>...<?php endif; ?>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">
                                    📅 Posted on <?= date("F j, Y, g:i a", strtotime($post['created_at'])) ?>
                                    <?php if($post['author_name']): ?>
                                        by <span class="author-badge"><?= htmlspecialchars($post['author_name']) ?></span>
                                    <?php endif; ?>
                                </small>
                            </div>
                            <div>
                                <?php if ($_SESSION['user_id'] == $post['user_id'] || $_SESSION['user_role'] == 'admin'): ?>
                                    <a href="edit.php?id=<?= $post['id'] ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
                                    <a href="delete.php?id=<?= $post['id'] ?>" class="btn btn-danger btn-sm btn-delete">🗑️ Delete</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card card-custom">
                <div class="card-body text-center py-5">
                    <h4>📝 No posts found</h4>
                    <p class="text-muted">
                        <?php if(!empty($keyword)): ?>
                            No posts match your search criteria.
                        <?php else: ?>
                            No posts have been created yet. <a href="create.php">Create the first post!</a>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page-1 ?>&search=<?= urlencode($keyword) ?>">Previous</a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($i == $page ? 'active' : '') ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($keyword) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page+1 ?>&search=<?= urlencode($keyword) ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php require 'includes/footer.php'; ?>