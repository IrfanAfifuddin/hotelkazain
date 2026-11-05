<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link rel="icon" href="data:,">
    <title><?php echo $__env->yieldContent('title', 'Pemesanan'); ?> — Kazain Hotel</title>

    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

    <style>
        body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
    </style>
    <?php echo $__env->yieldPushContent('styles'); ?>
    <?php echo $__env->make('customer.partials.theme', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</head>
<body class="bg-white text-gray-900 antialiased min-h-screen flex flex-col">

    <?php
        // Langkah aktif dikirim dari halaman: @section('step', '1'), '2', atau '3'
        $currentStep = (int) trim($__env->yieldContent('step', '1'));

        // Tombol kembali di header: @section('back_url', ...) dan @section('back_label', ...)
        $backUrl = trim($__env->yieldContent('back_url')) ?: route('customer.home');
        $backLabel = trim($__env->yieldContent('back_label')) ?: 'Beranda';
        $steps = [
            1 => 'Cek Ketersediaan & Kamar',
            2 => 'Isi Data Diri',
            3 => 'Pembayaran',
        ];
    ?>

    
    <header class="bg-ink text-white sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center gap-4">
            <a href="<?php echo e($backUrl); ?>" id="backLink" class="flex items-center gap-1.5 text-white/75 hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="m15 18-6-6 6-6" />
                </svg>
                <span><?php echo e($backLabel); ?></span>
            </a>

            <span class="h-6 w-px bg-white/20"></span>

            <a href="<?php echo e(route('customer.home')); ?>" class="font-display text-xl sm:text-2xl font-semibold tracking-wide">
                Kazain Hotel
            </a>
        </div>
    </header>

    
    <div class="bg-sand border-b border-gray-200">
        <ol class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-wrap items-center gap-x-3 gap-y-2 text-sm">
            <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $number => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $isActive = $number === $currentStep;
                    $isDone = $number < $currentStep;
                ?>
                <li class="flex items-center gap-2 <?php echo e($isActive ? 'text-gray-900 font-semibold' : ($isDone ? 'text-gray-700' : 'text-gray-400')); ?>" <?php if($isActive): ?> aria-current="step" <?php endif; ?>>
                    <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs
                        <?php echo e($isDone ? 'bg-gray-900 text-white' : ($isActive ? 'border-2 border-gray-900' : 'border border-gray-300')); ?>">
                        <?php if($isDone): ?>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5" /></svg>
                        <?php else: ?>
                            <?php echo e($number); ?>

                        <?php endif; ?>
                    </span>
                    <span class="<?php echo e($isActive ? '' : 'hidden sm:inline'); ?>"><?php echo e($label); ?></span>
                </li>

                <?php if(! $loop->last): ?>
                    <li class="text-gray-300" aria-hidden="true">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6" /></svg>
                    </li>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ol>
    </div>

    
    <main class="flex-1">
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <footer class="bg-ink py-6 text-center text-sm text-white/60">
        &copy; <?php echo e(date('Y')); ?> Kazain Hotel. Hak cipta dilindungi undang-undang.
    </footer>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH D:\KULIAH\hotelkazain\resources\views/layouts/booking.blade.php ENDPATH**/ ?>