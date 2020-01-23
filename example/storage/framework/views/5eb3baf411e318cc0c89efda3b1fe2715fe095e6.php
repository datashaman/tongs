<?php $__env->startSection('content'); ?>
<section>
  <article class="pure-g">
    <div class="pure-u-1-6">
      <a href="<?php echo e($path); ?>"><time><?php echo e(@$data['date']); ?></time></a>
    </div>

    <div class="pure-u-5-6">
      <?php if($contents): ?>
        <div class="e-content">
          <?php echo $contents; ?>

        </div>
      <?php endif; ?>
      <?php if(@$data['photos']): ?>
        <?php echo $__env->make('partials.gallery', [
            'photos' => $data['photos'],
            'height' => 300,
        ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      <?php endif; ?>
    </div>
  </article>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('default', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /mnt/files/Desktop/datashaman/tongs/resources/views/post.blade.php ENDPATH**/ ?>