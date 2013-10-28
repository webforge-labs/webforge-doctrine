<?php

$conf['db']['default']['host'] = '127.0.0.1';
$conf['db']['default']['user'] = $project->getLowerName();
$conf['db']['default']['password'] = '0ry8xd1fz9ubr5';
$conf['db']['default']['database'] = $project->isStaging() ? $project->getLowerName().'_staging' : $project->getLowerName();
$conf['db']['default']['port'] = NULL;
$conf['db']['default']['charset'] = 'utf8';

$conf['db']['tests'] = $conf['db']['default'];
$conf['db']['tests']['database'] = $project->getLowerName().'_tests';

// lowercasename => CamelCaseName
$conf['doctrine']['entities']['names'] = array(
  'contentstream'=>'ContentStream',
  'navigationnode'=>'NavigationNode',
  'newsentry'=>'NewsEntry',
  'calendarevent'=>'CalendarEvent'
);

// full FQN to custom Type
$conf['doctrine']['types'] = array();
