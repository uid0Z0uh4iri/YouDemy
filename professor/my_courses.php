<?php
session_start();
require_once '../config/database.php';
require_once '../Class/User.php';
require_once '../Class/Teacher.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'teacher') {
    header('Location: ../auth.php');
    exit();
}

$db = new Database();
$pdo = $db->getConnection();

$teacher = new Teacher($pdo);
$teacher->setId($_SESSION['user']['id']);

// Gérer la suppression
if (isset($_POST['delete_course']) && isset($_POST['course_id'])) {
    if ($teacher->deleteCourse($_POST['course_id'])) {
        $success_message = "Cours supprimé avec succès.";
    } else {
        $error_message = "Erreur lors de la suppression du cours.";
    }
}

$courses = $teacher->getMyCourses();
$user = $_SESSION['user'];
$page = 'my_courses';

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<div class="main-content">
    <div class="top-bar">
        <div class="welcome">
            <h2>Mes Cours</h2>
            <p>Gérez vos cours et leur contenu</p>
        </div>
        <div class="actions">
            <a href="add_course.php" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Nouveau Cours
            </a>
        </div>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <div class="courses-container">
        <?php if ($courses && count($courses) > 0): ?>
            <?php foreach ($courses as $course): ?>
                <div class="course-card">
                    <div class="course-image">
                        <img src="<?php echo !empty($course['image_url']) ? '../' . $course['image_url'] : '../assets/images/course-placeholder.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($course['title']); ?>">
                        <span class="status-badge <?php echo $course['status']; ?>">
                            <?php echo ucfirst($course['status']); ?>
                        </span>
                    </div>
                    
                    <div class="course-info">
                        <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                        
                        <p><?php echo htmlspecialchars(substr($course['description'], 0, 100)) . '...'; ?></p>
                        
                        <!-- Tags -->
                        <div class="course-tags">
                            <?php if(!empty($course['tags'])): ?>
                                <?php foreach(explode(',', $course['tags']) as $tag): ?>
                                    <span class="tag"><?php echo htmlspecialchars($tag); ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Meta Information -->
                        <div class="course-meta">
                            <span class="course-date">
                                <i class="fas fa-calendar"></i>
                                <?php echo date('d M Y', strtotime($course['created_at'])); ?>
                            </span>
                            <span class="course-students">
                                <i class="fas fa-users"></i>
                                <?php echo $course['student_count'] ?? 0; ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="course-actions">
                        <a href="edit_course.php?id=<?php echo $course['id']; ?>" class="btn btn-edit">
                            <i class="fas fa-edit"></i>
                            Modifier
                        </a>
                        <form method="POST" class="delete-form" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce cours ?');">
                            <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                            <button type="submit" name="delete_course" class="btn btn-delete">
                                <i class="fas fa-trash"></i>
                                Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-book-open"></i>
                <h3>Aucun cours trouvé</h3>
                <p>Vous n'avez pas encore créé de cours.</p>
                <a href="add_course.php" class="btn btn-primary">
                    Créer mon premier cours
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 