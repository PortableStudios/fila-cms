<div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">
    <div class="flex">
        <div class="fi-ta-text-item inline-flex items-center gap-1.5 ">
            <span class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white">
                <?php
                    $diff = \Mistralys\Diff\Diff::compareStrings($getRecord()->old_value, $getRecord()->new_value)->toString('||||||||||');
                    $diff = explode('||||||||||', $diff);
                    echo substr($diff[0], 0, 30);
                ?>
            </span>
        </div>
    </div>
</div>
