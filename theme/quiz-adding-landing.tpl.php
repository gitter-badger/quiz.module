<ul class="admin-list quiz-type-list">
  <?php foreach ($quiz_types as $name => $quiz_type): ?>
    <li>
      <span class="label">
        <?php echo l($quiz_type->label, "quiz/add/{$name}"); ?>
      </span>

      <?php if ($quiz_type->description): ?>
        <div class="description">
          <?php echo $quiz_type->description; ?>
        </div>
      <?php endif; ?>
    </li>
  <?php endforeach; ?>
</ul>
