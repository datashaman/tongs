<section class="gallery">
<?php $__currentLoopData = $photos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $photo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div id="<?php echo e($photo['id']); ?>" class="lightbox">
    <div class="lightbox__box">
    <a class="lightbox__close" href="#" tabindex="0">X</a>
    <div class="lightbox__content">
        <img src="<?php echo e($photo['link']); ?>"/>
    </div>
    </div>
</div>
<a class="lightbox__thumbnail" href="#<?php echo e($photo['id']); ?>"><img src="<?php echo e($photo['link']); ?>" height="<?php echo e($height); ?>"/></a>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php /**PATH /mnt/files/Desktop/datashaman/tongs/resources/views/partials/gallery.blade.php ENDPATH**/ ?>