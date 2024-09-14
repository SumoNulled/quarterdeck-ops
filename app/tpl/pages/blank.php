<!DOCTYPE html>
<html>

<?php \Loaders\Includes::includeFile('html_head'); ?>

<body class="theme-black">
  <?php
    \Loaders\Includes::includeFile('page_loader');
    \Loaders\Includes::includeFile('overlay_for_sidebars');
    \Loaders\Includes::includeFile('search_bar');
    \Loaders\Includes::includeFile('top_bar');
    \Loaders\Includes::includeFile('sidebars');
  ?>

    <section class="content">
        <div class="container-fluid">
            <div class="block-header">
                <h2>BLANK PAGE</h2>
            </div>
        </div>
    </section>

<?php \Loaders\Includes::includeFile('base_scripts'); ?>

</body>

</html>
