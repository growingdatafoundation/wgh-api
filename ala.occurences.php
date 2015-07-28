<?php
require_once ('./bootstrap.php');

/**
 * /ala.occurences.php?include=ala.details&bname=Acacia&lat=-34.928726&lon=138.59994&radius=5&dump=1
 */

/**
 * Request > Validation, required params
 */

if (!isset($_GET['bname'])) {// botanical name
    \Api\View::out(400, 'Invalid parameters: `bname` required.');
}

if (!isset($_GET['lon'])) {// longitude
    \Api\View::out(400, 'Invalid parameters: `lon` required.');
}

if (!isset($_GET['lat'])) {// latitude
    \Api\View::out(400, 'Invalid parameters: `lat` required.');
}

if (!isset($_GET['radius'])) {// latitude
    \Api\View::out(400, 'Invalid parameters: `radius` required.');
}

$aggregator = new \Api\Aggregator();

/**
 * Base Module: Occurences
 */

$occurences = new \Api\Ala\Occurences($_GET);
$aggregator->set('ala.occurences', $occurences);

/**
 * Additional modules
 */
if (isset($_GET['include'])){
    // get species names for included modules
    $species = array_keys($occurences->taxon_name);
    $modules = $aggregator->parseModules($_GET['include']);
    
    // add species for modules who require this, keep location data for modules who require them
    $_GET['taxon_name'] = $species; 
    
    foreach((array)$modules as $module){
        $service = $aggregator->moduleToNamespacedClass($module);
        if(class_exists($service)){ 
            $data = new $service($_GET);
            $aggregator->set($module, $data);
        }
    }
}

/**
 * Debug: Dump
 */

if (isset($_GET['dump'])) {// botanical name
    \Api\View::serviceHeaders('html');
    dump(json_decode(json_encode($aggregator)));
    //print json_encode($aggregator, JSON_PRETTY_PRINT);
    exit(1);
}

/**
 * Default: Data
 */

\Api\View::serviceHeaders();

print json_encode($aggregator);
exit(1);
