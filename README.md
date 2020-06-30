# Ikarus SPS Multiprocessing
The multiprocessing package ships with example plugins to realize async sps action controlled by a single main sps engine.

### Installation
```bin
$ composer require ikarus/sps-multi-processing
```

### Usage
```php
<?php
use Ikarus\SPS\CyclicEngine;
use Ikarus\SPS\SpawnedFileSPSPlugin;
use Ikarus\SPS\SpawnedCallbackSPSPlugin;
use Ikarus\SPS\SpawnedPluginAdapterPlugin;


$sps = new CyclicEngine(2);

$sps->addPlugin(
    new SpawnedFileSPSPlugin('my-sub-sps.php', 'my-id')
);

$sps->addPlugin(
    new SpawnedCallbackSPSPlugin(function() {
        // Do stuff but remember that this callback is executed in a separated process!
        // To get a plugin management you need to setup it in main SPS and include it into the callback.
    })
);

$myPlugin = new ACustomDesignedPlugin();
$sps->addPlugin(
    new SpawnedPluginAdapterPlugin(
        // This will invoke the plugin's update method in a separate process.
        $myPlugin
    )
);
```

In the file ```my-sub-sps.php``` you can define a totally new sps instance with different properties.  
It will be invoked separated.

#### Notes
Please remember that the update methods or the update callbacks are performed in a copy of the main process.

#### Options
You can use the package ```ikarus/sps-common-management``` to solve the problem of different sps processes using a common management.