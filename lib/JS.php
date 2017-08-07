<?php

class JS
{
    private static $loads   = array();
    private static $enabled = array();
    private static $scripts = array();

    private static $config;

    public static function init()
    {
        self::$config = array(
            'base_url'  => (getenv('HIVE_ENV') == 'DEVEL') ? '/js-dev/lib' : '/js/lib',
            'requirejs' => '/js/require.js',
            'prefix'    => '../app/'
        );
    }

    public static function config(array $config)
    {
        self::$config = array_merge(self::$config, $config);
    }

    public static function load()
    {
        $loads = func_get_args();
        self::$loads = array_unique(array_merge(self::$loads, $loads));
    }

    public static function enable()
    {
        $enabled = func_get_args();
        self::$enabled = array_unique(array_merge(self::$enabled, $enabled));
    }

    public static function begin()
    {
        ob_start();
    }

    public static function end()
    {
        self::$scripts[] = ob_get_contents();
        ob_end_clean();
    }

    public static function script($script)
    {
        self::$scripts[] = $script;
    }

    private static function mklist($items, $prefix = '', $quoted = true)
    {
        return implode(
            ', ',
            array_map(
                create_function(
                    '$x', 
                    $quoted ? 
                        'return "\''.$prefix.'$x\'";' : 
                        'return "'.$prefix.'$x";'),
                $items));
    }

    public static function dump()
    { 
?>
<script>
  var require = {
    baseUrl: '<?php print self::$config['base_url'] ?>'
  };
</script>
<script src="<?php print self::$config['requirejs'] ?>"></script>
<script>
require([<?php print self::mklist(self::$loads, self::$config['prefix']); ?>], function() {
require([<?php print self::mklist(self::$enabled) ?>], function() {
<?php foreach (self::$scripts as $s) { ?>
  <?php echo $s; ?>
<?php } ?>
});
});
</script>
<?php
    }
}
JS::init();
