<div id="index-loading-overlay">
    <div class="spinner"></div>
    <div id="loading-text">Analyse des fichiers...</div>
</div>

<?php
if (function_exists('apache_setenv')) { @apache_setenv('no-gzip', 1); }
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
while (ob_get_level() > 0) { ob_end_flush(); }
flush();