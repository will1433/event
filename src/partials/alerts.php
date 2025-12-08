<?php if (!empty($errorMsg)): ?>
    <div class="alert error"><?php echo htmlspecialchars($errorMsg); ?></div>
<?php endif; ?>

<?php if (!empty($successMsg)): ?>
    <div class="alert success"><?php echo htmlspecialchars($successMsg); ?></div>
<?php endif; ?>
