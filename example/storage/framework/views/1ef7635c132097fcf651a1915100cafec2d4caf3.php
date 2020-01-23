<?php $__env->startSection('content'); ?>
<?php $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <article class="pure-g">
    <div class="pure-u-1-6">
      <a href="<?php echo e($post['path']); ?>"><time><?php echo e(@$post['data']['date']); ?></time></a>
    </div>

    <div class="pure-u-5-6">
      <?php if(@$post['summary'] || @$post['contents']): ?>
        <div class="e-content">
          <?php echo @$post['summary'] ?: $post['contents']; ?>

          <?php if(@$post['summary']): ?><a href="<?php echo e($post['path']); ?>">more</a><?php endif; ?>
        </div>
      <?php endif; ?>
      <?php if(@$post['data']['photos']): ?>
        <?php echo $__env->make('partials.gallery', [
            'photos' => $post['data']['photos'],
            'height' => 120,
        ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      <?php endif; ?>
    </div>
  </article>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('default', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /mnt/files/Desktop/datashaman/tongs/resources/views/home.blade.php ENDPATH**/ ?>