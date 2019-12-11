<?php
$sql[] = "CREATE TABLE `{$dbprefix}stated_week_plannings` (
    id INT(11) NOT NULL AUTO_INCREMENT,
    date DATE NOT NULL,
    locked TINYINT(1) NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$sql[] = "CREATE TABLE `{$dbprefix}stated_week_planning_columns` (
    id INT(11) NOT NULL AUTO_INCREMENT,
    planning_id INT(11),
    starttime TIME NOT NULL,
    endtime TIME NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (planning_id) REFERENCES stated_week_plannings(id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$sql[] = "CREATE TABLE `{$dbprefix}stated_week_planning_times` (
    id INT(11) NOT NULL AUTO_INCREMENT,
    agent_id int(11) NOT NULL DEFAULT '0',
    column_id INT(11),
    PRIMARY KEY (`id`),
    UNIQUE KEY `agent_column` (`agent_id`,`column_id`),
    FOREIGN KEY (column_id) REFERENCES stated_week_planning_columns(id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$sql[] = "CREATE TABLE `{$dbprefix}stated_week_planning_job` (
    id INT(11) NOT NULL AUTO_INCREMENT,
    planning_id INT(11),
    name VARCHAR(255) NOT NULL,
    description VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (planning_id) REFERENCES stated_week_plannings(id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$sql[] = "CREATE TABLE `{$dbprefix}stated_week_planning_job_times` (
    id INT(11) NOT NULL AUTO_INCREMENT,
    agent_id int(11) NOT NULL DEFAULT '0',
    job_id INT(11),
    times VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `agent_job` (`agent_id`,`job_id`),
    FOREIGN KEY (job_id) REFERENCES stated_week_planning_job(id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$sql[] = "CREATE TABLE `{$dbprefix}stated_week_planning_pauses` (
    id INT(11) NOT NULL AUTO_INCREMENT,
    agent_id int(11) NOT NULL DEFAULT '0',
    planning_id INT(11),
    PRIMARY KEY (`id`),
    UNIQUE KEY `agent_job` (`agent_id`,`planning_id`),
    FOREIGN KEY (planning_id) REFERENCES stated_week_plannings(id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
