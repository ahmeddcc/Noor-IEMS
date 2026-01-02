<?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($currentPage > 1): ?>
            <a href="#" class="page-link first" data-page="1" title="الأولى">
                <i class="fas fa-angle-double-right"></i>
            </a>
            <a href="#" class="page-link prev" data-page="<?php echo $currentPage - 1; ?>" title="السابقة">
                <i class="fas fa-angle-right"></i>
            </a>
        <?php endif; ?>

        <?php
        $start = max(1, $currentPage - 2);
        $end = min($totalPages, $currentPage + 2);
        
        for ($i = $start; $i <= $end; $i++):
            $activeClass = ($i == $currentPage) ? 'active' : '';
        ?>
            <a href="#" class="page-link <?php echo $activeClass; ?>" data-page="<?php echo $i; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <?php if ($currentPage < $totalPages): ?>
            <a href="#" class="page-link next" data-page="<?php echo $currentPage + 1; ?>" title="التالية">
                <i class="fas fa-angle-left"></i>
            </a>
            <a href="#" class="page-link last" data-page="<?php echo $totalPages; ?>" title="الأخيرة">
                <i class="fas fa-angle-double-left"></i>
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>
