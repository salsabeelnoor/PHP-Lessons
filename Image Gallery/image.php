<?php
include './inc/functions.inc.php';
include './inc/images.inc.php';

?>
<?php include './views/header.php'; ?>
<?php if(!empty($_GET['image']) && !empty($imageTitles[$_GET['image']])): ?>
    <?php $image = $_GET['image']; ?>
    <h2><?php echo e($imageTitles[$image]); ?></h2>
    <img src="./images/<?php echo rawurldecode($image); ?>" >
    <p><?php echo str_replace("\n", "<br/>", e($imageDescriptions[$image])); ?></p>

    <?php else: ?>
    <div class="notice">
        <p>This image could not be found</p>
    </div>
<?php endif; ?>

<a href="gallery.php">Back to gallery</a>
<?php include './views/footer.php'; ?>
