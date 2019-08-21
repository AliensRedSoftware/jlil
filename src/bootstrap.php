<?php
use app, gui, std, framework;
$packageLoader = new FrameworkPackageLoader();
$packageLoader->register();
$App = new Application();
$App->launch();
