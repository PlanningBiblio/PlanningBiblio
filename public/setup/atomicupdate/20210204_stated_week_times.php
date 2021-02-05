<?php

$sql[] = "alter table stated_week_planning_times add start_time time null default null;";
$sql[] = "alter table stated_week_planning_times add end_time time null default null;";